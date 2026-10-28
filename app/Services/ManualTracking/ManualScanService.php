<?php

namespace App\Services\ManualTracking;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualScan;
use App\Models\Organization;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns scanned QR text into a title or a destination zone, and checks it against where
 * the package is. Book QRs carry region + grade + subject + edition; package QRs carry
 * only the zone name, so the title of a package comes from the operator.
 */
class ManualScanService
{
    private ?Collection $zones = null;

    public function __construct(private ManualStockService $stock)
    {
    }

    /**
     * Identify a scan and check it against the organization the package should belong to
     * (the destination when shipping or receiving, the warehouse itself when sorting).
     *
     * @return array{kind: string, payload: string, code: ?ManualBookCode, book: ?ManualBook, edition: ?string, zone: ?Zone, flag: ?string, message: string}
     */
    public function identify(string $payload, ?Organization $expectedAt = null, ?ManualDistribution $shipment = null): array
    {
        $payload = trim($payload);
        $result = ['kind' => ManualScan::KIND_UNKNOWN, 'payload' => $payload, 'code' => null, 'book' => null, 'edition' => null, 'zone' => null, 'flag' => null, 'message' => ''];

        if ($code = ManualBookCode::findByScan($payload)) {
            $code->loadMissing('book');

            return $this->checkBook(array_merge($result, [
                'kind' => ManualScan::KIND_BOOK,
                'code' => $code,
                'book' => $code->book,
                'edition' => $code->edition,
                'message' => $code->book->title . ($code->edition === ManualBook::EDITION_TEACHER ? ' (teacher guide)' : ''),
            ]), $expectedAt, $shipment);
        }

        $matches = $this->zonesMatching($payload);

        if ($matches->isEmpty()) {
            return array_merge($result, ['flag' => ManualScan::FLAG_UNKNOWN, 'message' => "\"{$payload}\" is not a known book or zone code."]);
        }

        // Zone names repeat across regions (e.g. North Shewa); prefer the one in this region
        if ($matches->count() > 1 && $expectedAt?->region_id) {
            $inRegion = $matches->where('region_id', $expectedAt->region_id);
            $matches = $inRegion->isNotEmpty() ? $inRegion : $matches;
        }

        if ($matches->count() > 1) {
            return array_merge($result, [
                'kind' => ManualScan::KIND_PACKAGE,
                'flag' => ManualScan::FLAG_AMBIGUOUS,
                'message' => "\"{$payload}\" matches zones in several regions. Check the package label.",
            ]);
        }

        return $this->checkPackage(array_merge($result, [
            'kind' => ManualScan::KIND_PACKAGE,
            'zone' => $matches->first(),
            'message' => 'Package for ' . $matches->first()->name,
        ]), $expectedAt);
    }

    /**
     * Record a scan made while sorting at a warehouse or receiving a shipment. Retrying with
     * the same client UUID returns the scan already recorded.
     */
    public function record(User $actor, Organization $at, string $payload, string $context, array $options = []): ManualScan
    {
        $shipment = $options['shipment'] ?? null;
        $title = $options['title'] ?? null;
        $clientUuid = $options['client_uuid'] ?? null;

        if ($clientUuid && ($existing = ManualScan::query()->where('client_uuid', $clientUuid)->first())) {
            return $existing;
        }

        if (trim($payload) === '') {
            throw new ManualTrackingException('Nothing was scanned.');
        }

        if (! $this->stock->canActFor($actor, $at)) {
            throw new ManualTrackingException('You can only scan for organizations in your area.');
        }

        if ($context === ManualScan::CONTEXT_RECEIVE) {
            $this->ensureCanReceive($actor, $at, $shipment);
            $shipment->loadMissing('lines');

            // A single-title shipment needs no title choice
            if (! $title && $shipment->lines->count() === 1) {
                $title = $shipment->lines->first()->book;
            }
        } elseif ($context !== ManualScan::CONTEXT_SORT) {
            throw new ManualTrackingException('Unknown scan context.');
        }

        $result = $this->identify($payload, $at, $shipment);
        $book = $result['book'] ?? ($result['kind'] === ManualScan::KIND_PACKAGE ? $title : null);

        // The operator's title is checked too: a region warehouse should not hold another region's books
        if ($result['kind'] === ManualScan::KIND_PACKAGE && ! $result['flag'] && $book) {
            $result = $this->checkBook(array_merge($result, ['book' => $book]), $at, $shipment);
        }

        return DB::transaction(fn () => ManualScan::query()->create([
            'organization_id' => $at->id,
            'manual_distribution_id' => $shipment?->id,
            'context' => $context,
            'payload' => Str::limit($result['payload'], 250, ''),
            'kind' => $result['kind'],
            'zone_id' => $result['zone']?->id,
            'manual_book_id' => $book?->id,
            'edition' => $result['edition'] ?? ($book ? ManualBook::EDITION_STUDENT : null),
            'flag' => $result['flag'],
            'flag_message' => $result['flag'] ? Str::limit($result['message'], 250, '') : null,
            'alert_status' => in_array($result['flag'], ManualScan::ALERT_FLAGS, true) ? ManualScan::ALERT_OPEN : null,
            'scanned_by' => $actor->id,
            'scanned_at' => now(),
            'client_uuid' => $clientUuid,
        ]));
    }

    // Undo a scan made by mistake. Receipt scans are locked once the shipment is received.
    public function void(User $actor, ManualScan $scan): void
    {
        if ((int) $scan->scanned_by !== (int) $actor->id) {
            throw new ManualTrackingException('Only the person who made a scan can undo it.');
        }

        if ($scan->voided_at || $scan->alert_status === ManualScan::ALERT_RESOLVED) {
            throw new ManualTrackingException('This scan can no longer be undone.');
        }

        if ($scan->manual_distribution_id && ! $scan->distribution?->isInTransit()) {
            throw new ManualTrackingException('The shipment has been received; its scans are final.');
        }

        $scan->update(['voided_at' => now(), 'alert_status' => null]);
    }

    // Close a misroute alert. Like discrepancies, the person who raised it cannot close it.
    public function resolve(User $actor, ManualScan $scan, string $note): ManualScan
    {
        return DB::transaction(function () use ($actor, $scan, $note): ManualScan {
            $scan = ManualScan::query()->with(['organization', 'distribution.organization'])->lockForUpdate()->findOrFail($scan->id);

            if ($scan->alert_status !== ManualScan::ALERT_OPEN) {
                throw new ManualTrackingException('This alert is not open.');
            }

            if ((int) $scan->scanned_by === (int) $actor->id) {
                throw new ManualTrackingException('An alert must be closed by someone other than the person who scanned it.');
            }

            $source = $scan->distribution?->organization;
            if (! $this->stock->canActFor($actor, $scan->organization) && ! ($source && $this->stock->canActFor($actor, $source))) {
                throw new ManualTrackingException('You can only close alerts in your area.');
            }

            if (trim($note) === '') {
                throw new ManualTrackingException('Say what was done about the misrouted package.');
            }

            $scan->update([
                'alert_status' => ManualScan::ALERT_RESOLVED,
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
                'resolution_note' => Str::limit(trim($note), 1000, ''),
            ]);

            return $scan;
        });
    }

    /**
     * Package scans counted per title for one shipment, ignoring undone scans.
     *
     * @return array<int|string, int> keyed by title id ('' for packages scanned without a title)
     */
    public function packageCounts(ManualDistribution $shipment): array
    {
        return ManualScan::query()->live()
            ->where('manual_distribution_id', $shipment->id)
            ->where('kind', ManualScan::KIND_PACKAGE)
            ->selectRaw('manual_book_id, COUNT(*) as packages')
            ->groupBy('manual_book_id')
            ->pluck('packages', 'manual_book_id')
            ->mapWithKeys(fn ($count, $bookId) => [$bookId ?: '' => (int) $count])
            ->all();
    }

    public function ensureCanReceive(User $actor, Organization $at, ?ManualDistribution $shipment): void
    {
        if (! $shipment || (int) $shipment->destination_organization_id !== (int) $at->id) {
            throw new ManualTrackingException('Receipt scans must be made at the shipment\'s destination.');
        }

        if (! $shipment->isInTransit()) {
            throw new ManualTrackingException('This shipment has already been received.');
        }

        if ((int) $shipment->distributed_by === (int) $actor->id) {
            throw new ManualTrackingException('The person who dispatched a shipment cannot receive it.');
        }
    }

    private function checkPackage(array $result, ?Organization $expectedAt): array
    {
        $zone = $result['zone'];

        if (! $expectedAt || ! $zone) {
            return $result;
        }

        // Zone, woreda and school offices take only their own zone's packages; region offices any zone in the region
        $misrouted = $expectedAt->zone_id
            ? (int) $zone->id !== (int) $expectedAt->zone_id
            : ($expectedAt->region_id && (int) $zone->region_id !== (int) $expectedAt->region_id);

        if ($misrouted) {
            $result['flag'] = ManualScan::FLAG_MISROUTE;
            $result['message'] = "Package for {$zone->name} is at {$expectedAt->name}.";
        }

        return $result;
    }

    private function checkBook(array $result, ?Organization $expectedAt, ?ManualDistribution $shipment): array
    {
        $book = $result['book'];

        if ($expectedAt?->region_id && $book->region_id && (int) $book->region_id !== (int) $expectedAt->region_id) {
            $result['flag'] = ManualScan::FLAG_WRONG_REGION;
            $result['message'] = "{$book->title} is printed for another region than {$expectedAt->name}.";

            return $result;
        }

        if ($shipment && ! $shipment->lines()->where('manual_book_id', $book->id)->exists()) {
            $result['flag'] = ManualScan::FLAG_NOT_ON_SHIPMENT;
            $result['message'] = "{$book->title} is not on shipment {$shipment->reference}.";
        }

        return $result;
    }

    private function zonesMatching(string $payload): Collection
    {
        $this->zones ??= Zone::query()->get(['id', 'name', 'region_id'])
            ->groupBy(fn (Zone $zone) => self::zoneKey($zone->name));

        return $this->zones->get(self::zoneKey($payload), collect());
    }

    // "EAST SHEWA", "East Shewa Zone" and "east-shewa" are the same label
    public static function zoneKey(?string $name): string
    {
        $key = Str::lower(preg_replace('/[^\pL\pN]+/u', ' ', (string) $name));

        return trim(preg_replace('/\s+(zone|sub ?city|subcity)$/', '', trim($key)));
    }
}
