<?php

namespace App\Services\ManualTracking;

use App\Models\ManualTracking\ManualAudit;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Shipment hand-offs: the sender dispatches (stock leaves and is in transit), the receiver
 * counts what arrived (stock arrives, differences become discrepancies), and a third
 * person resolves each discrepancy.
 */
class ManualShipmentService
{
    public function __construct(private ManualStockService $stock)
    {
    }

    /**
     * @param  array{
     *     from_organization_id: int,
     *     to_organization_id: int,
     *     lines: array<int, array{manual_book_id: int, full_packages?: int, partial_package_books?: int, partial_teacher_guides?: int}>,
     *     carrier?: ?string, vehicle_no?: ?string, driver_name?: ?string, driver_phone?: ?string,
     *     waybill_no?: ?string, expected_at?: mixed, remarks?: ?string, client_uuid?: ?string
     * }  $data
     */
    public function dispatch(User $actor, array $data): ManualDistribution
    {
        // Offline clients retry; the same client_uuid must not create a second shipment
        if (! empty($data['client_uuid'])) {
            $existing = ManualDistribution::query()->where('client_uuid', $data['client_uuid'])->first();
            if ($existing) {
                return $existing;
            }
        }

        $from = Organization::query()->with('organizationType')->find($data['from_organization_id'] ?? null);
        $to = Organization::query()->find($data['to_organization_id'] ?? null);

        if (! $from || ! $to) {
            throw new ManualTrackingException('Choose both a source and a destination organization.');
        }

        if ($from->is($to)) {
            throw new ManualTrackingException('The destination must be different from the source.');
        }

        if (! $this->stock->canActFor($actor, $from)) {
            throw new ManualTrackingException('You can only dispatch from organizations in your area.');
        }

        $lines = $this->prepareLines($data['lines'] ?? []);
        $external = $this->isExternalSource($from);

        return DB::transaction(function () use ($actor, $data, $from, $to, $lines, $external): ManualDistribution {
            $shipment = ManualDistribution::query()->create([
                'reference' => $this->newReference(),
                'status' => ManualDistribution::STATUS_DISPATCHED,
                'distributed_by' => $actor->id,
                'distributed_at' => now(),
                'organization_id' => $from->id,
                'country_id' => $from->country_id,
                'region_id' => $from->region_id,
                'zone_id' => $from->zone_id,
                'woreda_id' => $from->woreda_id,
                'destination_organization_id' => $to->id,
                'destination_country_id' => $to->country_id,
                'destination_region_id' => $to->region_id,
                'destination_zone_id' => $to->zone_id,
                'destination_woreda_id' => $to->woreda_id,
                'carrier' => $data['carrier'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_phone' => $data['driver_phone'] ?? null,
                'waybill_no' => $data['waybill_no'] ?? null,
                'expected_at' => $data['expected_at'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'client_uuid' => $data['client_uuid'] ?? null,
            ]);

            foreach ($lines as $prepared) {
                /** @var ManualBook $book */
                $book = $prepared['book'];
                $line = $shipment->lines()->create([
                    'manual_book_id' => $book->id,
                    'full_packages' => $prepared['full_packages'],
                    'partial_package_books' => $prepared['partial_package_books'],
                    'partial_teacher_guides' => $prepared['partial_teacher_guides'],
                    'quantity' => $prepared['copies'][ManualBook::EDITION_STUDENT],
                    'teacher_guides' => $prepared['copies'][ManualBook::EDITION_TEACHER],
                ]);

                if ($external) {
                    continue;
                }

                foreach ($prepared['copies'] as $edition => $quantity) {
                    if ($quantity <= 0) {
                        continue;
                    }

                    $entry = $this->stock->move($from, $book, $edition, -$quantity, ManualStockService::MOVEMENT_DISPATCH, [
                        'actor' => $actor,
                        'shipment_id' => $shipment->id,
                        'line_id' => $line->id,
                        'notes' => 'Dispatched to ' . $to->name,
                    ]);
                    $this->stock->changeInTransit($from, $book, $edition, $quantity);

                    if ($edition === ManualBook::EDITION_STUDENT) {
                        $line->update([
                            'source_balance_before' => $entry->balance_after + $quantity,
                            'source_balance_after' => $entry->balance_after,
                        ]);
                    }
                }
            }

            $this->audit($actor, 'shipment_dispatched', $shipment, [
                'reference' => $shipment->reference,
                'to' => $to->name,
                'lines' => $lines->map(fn ($line) => ['manual_book_id' => $line['book']->id] + $line['copies'])->values()->all(),
            ]);

            return $shipment->fresh('lines');
        });
    }

    /**
     * @param  array<int, array{full_packages?: int, partial_package_books?: int, partial_teacher_guides?: int, damaged_quantity?: int, damaged_teacher_guides?: int}>  $counts  keyed by line id
     */
    public function receive(User $actor, ManualDistribution $shipment, array $counts, ?string $notes = null, ?Carbon $occurredAt = null): ManualDistribution
    {
        return DB::transaction(function () use ($actor, $shipment, $counts, $notes, $occurredAt): ManualDistribution {
            $shipment = ManualDistribution::query()
                ->with(['lines.book', 'organization.organizationType', 'destinationOrganization'])
                ->lockForUpdate()
                ->findOrFail($shipment->id);

            if (! $shipment->isInTransit()) {
                throw new ManualTrackingException('This shipment has already been received.');
            }

            $to = $shipment->destinationOrganization;
            if (! $to || ! $this->stock->canActFor($actor, $to)) {
                throw new ManualTrackingException('Only users at the destination can confirm receipt.');
            }

            if ((int) $shipment->distributed_by === (int) $actor->id) {
                throw new ManualTrackingException('The person who dispatched a shipment cannot also confirm its receipt.');
            }

            $external = $this->isExternalSource($shipment->organization);
            $hasDiscrepancy = false;

            foreach ($shipment->lines as $line) {
                if (! isset($counts[$line->id])) {
                    throw new ManualTrackingException("Enter the received count for {$line->book->title}.");
                }

                [$count, $received] = $this->countLine($line->book, $counts[$line->id]);
                $damaged = [
                    ManualBook::EDITION_STUDENT => $count['damaged_quantity'],
                    ManualBook::EDITION_TEACHER => $count['damaged_teacher_guides'],
                ];

                foreach ($damaged as $edition => $quantity) {
                    if ($quantity > $received[$edition]) {
                        throw new ManualTrackingException("Damaged copies of {$line->book->title} cannot exceed the copies received.");
                    }
                }

                $line->update([
                    'received_quantity' => $received[ManualBook::EDITION_STUDENT],
                    'received_teacher_guides' => $received[ManualBook::EDITION_TEACHER],
                    'damaged_quantity' => $damaged[ManualBook::EDITION_STUDENT],
                    'damaged_teacher_guides' => $damaged[ManualBook::EDITION_TEACHER],
                ]);

                foreach ($line->sentCopies() as $edition => $sent) {
                    $usable = $received[$edition] - $damaged[$edition];

                    if ($usable > 0) {
                        $this->stock->move($to, $line->book, $edition, $usable, ManualStockService::MOVEMENT_RECEIVE, [
                            'actor' => $actor,
                            'shipment_id' => $shipment->id,
                            'line_id' => $line->id,
                            'notes' => 'Received from ' . $shipment->organization->name,
                            'occurred_at' => $occurredAt,
                        ]);
                    }

                    if (! $external && $sent > 0) {
                        $this->stock->changeInTransit($shipment->organization, $line->book, $edition, -$sent);
                    }

                    $differences = [
                        ManualDiscrepancy::TYPE_MISSING => $sent - $received[$edition],
                        ManualDiscrepancy::TYPE_EXCESS => $received[$edition] - $sent,
                        ManualDiscrepancy::TYPE_DAMAGED => $damaged[$edition],
                    ];

                    foreach ($differences as $type => $quantity) {
                        if ($quantity > 0) {
                            $hasDiscrepancy = true;
                            $line->discrepancies()->create([
                                'edition' => $edition,
                                'type' => $type,
                                'quantity' => $quantity,
                                'status' => ManualDiscrepancy::STATUS_OPEN,
                                'raised_by' => $actor->id,
                            ]);
                        }
                    }
                }
            }

            $shipment->update([
                'status' => $hasDiscrepancy ? ManualDistribution::STATUS_RECEIVED_WITH_DISCREPANCY : ManualDistribution::STATUS_RECEIVED,
                'received_at' => $occurredAt ?? now(),
                'received_by' => $actor->id,
                'receipt_notes' => $notes,
            ]);

            $this->audit($actor, 'shipment_received', $shipment, [
                'reference' => $shipment->reference,
                'with_discrepancy' => $hasDiscrepancy,
            ]);

            return $shipment->fresh(['lines.discrepancies']);
        });
    }

    public function resolve(User $actor, ManualDiscrepancy $discrepancy, string $resolution, ?string $notes = null): ManualDiscrepancy
    {
        return DB::transaction(function () use ($actor, $discrepancy, $resolution, $notes): ManualDiscrepancy {
            $discrepancy = ManualDiscrepancy::query()
                ->with(['line.book', 'line.distribution.organization.organizationType', 'line.distribution.destinationOrganization'])
                ->lockForUpdate()
                ->findOrFail($discrepancy->id);

            if ($discrepancy->status !== ManualDiscrepancy::STATUS_OPEN) {
                throw new ManualTrackingException('This discrepancy is already resolved.');
            }

            if (! array_key_exists($resolution, ManualDiscrepancy::RESOLUTIONS[$discrepancy->type] ?? [])) {
                throw new ManualTrackingException('That resolution does not apply to this discrepancy.');
            }

            $shipment = $discrepancy->line->distribution;
            $from = $shipment->organization;
            $to = $shipment->destinationOrganization;

            // Neither side of the hand-off may close its own discrepancy
            if (in_array((int) $actor->id, [(int) $shipment->distributed_by, (int) $shipment->received_by], true)) {
                throw new ManualTrackingException('A discrepancy must be resolved by someone other than the sender and the receiver.');
            }

            if (! $this->stock->canActFor($actor, $to) && ! $this->stock->canActFor($actor, $from)) {
                throw new ManualTrackingException('You can only resolve discrepancies for shipments in your area.');
            }

            $context = [
                'actor' => $actor,
                'shipment_id' => $shipment->id,
                'line_id' => $discrepancy->line->id,
                'notes' => trim(ManualDiscrepancy::RESOLUTIONS[$discrepancy->type][$resolution] . '. ' . $notes),
            ];
            $book = $discrepancy->line->book;

            if (in_array($resolution, ['found', 'usable'], true)) {
                $this->stock->move($to, $book, $discrepancy->edition, $discrepancy->quantity, ManualStockService::MOVEMENT_FOUND, $context);
            }

            if ($resolution === 'corrected' && ! $this->isExternalSource($from)) {
                $this->stock->move($from, $book, $discrepancy->edition, -$discrepancy->quantity, ManualStockService::MOVEMENT_ADJUSTMENT, $context);
            }

            $discrepancy->update([
                'status' => ManualDiscrepancy::STATUS_RESOLVED,
                'resolution' => $resolution,
                'resolution_notes' => $notes,
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
            ]);

            $this->audit($actor, 'discrepancy_resolved', $discrepancy, [
                'reference' => $shipment->reference,
                'type' => $discrepancy->type,
                'quantity' => $discrepancy->quantity,
                'resolution' => $resolution,
            ]);

            return $discrepancy;
        });
    }

    // Printers are outside the tracked network: their own stock is not recorded
    public function isExternalSource(?Organization $organization): bool
    {
        return $organization?->organizationType?->name === 'Printer';
    }

    /**
     * @return Collection<int, array{book: ManualBook, full_packages: int, partial_package_books: int, partial_teacher_guides: int, copies: array{student: int, teacher: int}}>
     */
    private function prepareLines(array $lines): Collection
    {
        $prepared = collect($lines)->map(function ($line) {
            $book = ManualBook::query()->find($line['manual_book_id'] ?? null);
            if (! $book) {
                throw new ManualTrackingException('Choose a title for every line.');
            }

            [$count, $copies] = $this->countLine($book, $line);

            if (array_sum($copies) === 0) {
                throw new ManualTrackingException("Enter packages or copies for {$book->title}.");
            }

            return ['book' => $book, 'copies' => $copies] + $count;
        });

        if ($prepared->isEmpty()) {
            throw new ManualTrackingException('Add at least one title to the shipment.');
        }

        if ($prepared->pluck('book.id')->duplicates()->isNotEmpty()) {
            throw new ManualTrackingException('Each title can appear only once in a shipment.');
        }

        return $prepared;
    }

    /**
     * Read one line's count, given either as packages (full packages plus a partial one) or
     * as total copies for staff who count the books instead of the packages. Totals are kept
     * exactly; the package fields are filled with how those copies would normally be packed.
     *
     * @return array{0: array<string, int>, 1: array{student: int, teacher: int}}
     */
    private function countLine(ManualBook $book, array $input): array
    {
        $count = $this->nonNegativeCounts($input);

        if (array_key_exists('student_books', $input) || array_key_exists('teacher_guides', $input)) {
            $totals = $this->nonNegativeCounts($input, ['student_books', 'teacher_guides']);

            if (! $book->has_teacher_guide && $totals['teacher_guides'] > 0) {
                throw new ManualTrackingException("{$book->title} has no teacher guide.");
            }

            return [
                $book->packagesFor($totals['student_books'], $totals['teacher_guides']) + $count,
                [ManualBook::EDITION_STUDENT => $totals['student_books'], ManualBook::EDITION_TEACHER => $totals['teacher_guides']],
            ];
        }

        if ($count['partial_package_books'] >= $book->students_per_package) {
            throw new ManualTrackingException("A partial package of {$book->title} must hold fewer than {$book->students_per_package} student books.");
        }

        if (! $book->has_teacher_guide && $count['partial_teacher_guides'] > 0) {
            throw new ManualTrackingException("{$book->title} has no teacher guide.");
        }

        return [$count, $book->copiesFor($count['full_packages'], $count['partial_package_books'], $count['partial_teacher_guides'])];
    }

    private function nonNegativeCounts(array $input, array $fields = ['full_packages', 'partial_package_books', 'partial_teacher_guides', 'damaged_quantity', 'damaged_teacher_guides']): array
    {
        $count = [];
        foreach ($fields as $field) {
            $value = (int) ($input[$field] ?? 0);
            if ($value < 0) {
                throw new ManualTrackingException('Counts cannot be negative.');
            }
            $count[$field] = $value;
        }

        return $count;
    }

    private function newReference(): string
    {
        do {
            $reference = 'SH-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (ManualDistribution::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function audit(User $actor, string $action, $subject, array $meta): void
    {
        ManualAudit::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'auditable_type' => get_class($subject),
            'auditable_id' => $subject->id,
            'meta' => $meta,
        ]);
    }
}
