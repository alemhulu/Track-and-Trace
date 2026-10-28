<?php

namespace App\Http\Controllers\ManualTracking;

use App\Http\Controllers\Controller;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Oversight\VerificationVisit;
use App\Services\Oversight\OversightService;
use App\Support\AcademicYear;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV exports for the Ministry and regional bureaus, limited to the user's area. Files open
 * directly in Excel (UTF-8 with a byte order mark, so Amharic and Afaan Oromo names survive).
 */
class ManualExportController extends Controller
{
    public const TYPES = [
        'coverage' => 'School coverage',
        'stock' => 'Stock by holder',
        'shipments' => 'Shipments and receipts',
        'visits' => 'Verification visits',
    ];

    public function __invoke(Request $request, string $type, OversightService $oversight): StreamedResponse
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $actor = $request->user();
        $year = (int) $request->query('year', AcademicYear::current());
        $filename = sprintf('%s-%s.csv', $type, $type === 'coverage' || $type === 'visits' ? $year . '-ec' : now()->format('Y-m-d'));

        return response()->streamDownload(function () use ($type, $actor, $year, $oversight): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = fn (array $row) => fputcsv($out, $row);

            match ($type) {
                'coverage' => $this->coverage($write, $oversight, $actor, $year),
                'stock' => $this->stock($write, $actor),
                'shipments' => $this->shipments($write, $actor),
                'visits' => $this->visits($write, $actor, $year),
            };

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function coverage(callable $write, OversightService $oversight, $actor, int $year): void
    {
        $write(['School', 'Region', 'Zone', 'Woreda', 'Academic year', 'Student books required', 'With students', 'On hand at school', 'Short', 'Coverage %', 'Last visit', 'Shelf counts matching %', 'Books shown by students %']);

        foreach ($oversight->schoolRows($actor, $year) as $row) {
            $school = $row['school'];
            $totals = $row['totals'];
            $visit = $row['visit'];

            $write([
                $school->name,
                $school->region->name ?? '',
                $school->zone->name ?? '',
                $school->woreda->name ?? '',
                AcademicYear::label($year),
                $totals['required_student'],
                $totals['issued_student'],
                $totals['on_hand_student'],
                $totals['shortfall_student'],
                $this->percent($totals['coverage']),
                $visit?->visited_at?->toDateString() ?? '',
                $visit ? $this->percent($visit->stockAccuracy()) : '',
                $visit ? $this->percent($visit->holdingRate()) : '',
            ]);
        }
    }

    private function stock(callable $write, $actor): void
    {
        $write(['Holder', 'Region', 'Zone', 'Woreda', 'Title', 'Edition', 'On hand', 'Sent, not yet received']);

        ManualStockBalance::query()
            ->accessibleBy($actor)
            ->where(fn ($query) => $query->where('on_hand', '!=', 0)->orWhere('in_transit_out', '!=', 0))
            ->with(['organization:id,name,region_id,zone_id,woreda_id', 'organization.region:id,name', 'organization.zone:id,name', 'organization.woreda:id,name', 'book:id,title'])
            ->orderBy('organization_id')->orderBy('manual_book_id')->orderBy('edition')
            ->chunk(1000, function ($balances) use ($write): void {
                foreach ($balances as $balance) {
                    $write([
                        $balance->organization->name ?? '',
                        $balance->organization->region->name ?? '',
                        $balance->organization->zone->name ?? '',
                        $balance->organization->woreda->name ?? '',
                        $balance->book->title ?? '',
                        ManualBook::EDITIONS[$balance->edition] ?? $balance->edition,
                        $balance->on_hand,
                        $balance->in_transit_out,
                    ]);
                }
            });
    }

    private function shipments(callable $write, $actor): void
    {
        $write(['Reference', 'Status', 'From', 'To', 'Dispatched', 'Received', 'Waybill', 'Title', 'Sent SB', 'Sent TG', 'Received SB', 'Received TG', 'Damaged SB', 'Damaged TG', 'Open discrepancies']);

        ManualDistribution::query()
            ->accessibleBy($actor)
            ->with(['organization:id,name', 'destinationOrganization:id,name', 'lines.book:id,title', 'lines.discrepancies'])
            ->orderBy('id')
            ->chunk(500, function ($shipments) use ($write): void {
                foreach ($shipments as $shipment) {
                    foreach ($shipment->lines as $line) {
                        $received = ! $shipment->isInTransit();
                        $write([
                            $shipment->reference,
                            ManualDistribution::STATUSES[$shipment->status] ?? $shipment->status,
                            $shipment->organization->name ?? '',
                            $shipment->destinationOrganization->name ?? '',
                            optional($shipment->distributed_at)->format('Y-m-d H:i'),
                            optional($shipment->received_at)->format('Y-m-d H:i'),
                            $shipment->waybill_no,
                            $line->book->title ?? '',
                            $line->quantity,
                            $line->teacher_guides,
                            $received ? (int) $line->received_quantity : '',
                            $received ? (int) $line->received_teacher_guides : '',
                            $received ? (int) $line->damaged_quantity : '',
                            $received ? (int) $line->damaged_teacher_guides : '',
                            $line->discrepancies->where('status', 'open')->count(),
                        ]);
                    }
                }
            });
    }

    private function visits(callable $write, $actor, int $year): void
    {
        $write(['Date', 'School', 'Zone', 'Woreda', 'Visited by', 'Titles counted', 'Titles matching', 'Students checked', 'Books recorded', 'Books shown', 'Notes']);

        VerificationVisit::query()
            ->accessibleBy($actor)
            ->where('academic_year', $year)
            ->with(['school:id,name,zone_id,woreda_id', 'school.zone:id,name', 'school.woreda:id,name', 'visitor:id,name'])
            ->orderBy('visited_at')
            ->chunk(500, function ($visits) use ($write): void {
                foreach ($visits as $visit) {
                    $write([
                        $visit->visited_at->format('Y-m-d'),
                        $visit->school->name ?? '',
                        $visit->school->zone->name ?? '',
                        $visit->school->woreda->name ?? '',
                        $visit->visitor->name ?? '',
                        $visit->stock_lines,
                        $visit->stock_lines_matching,
                        $visit->students_checked,
                        $visit->books_expected,
                        $visit->books_seen,
                        $visit->notes,
                    ]);
                }
            });
    }

    private function percent(?float $share): string
    {
        return $share === null ? '' : (string) round($share * 100, 1);
    }
}
