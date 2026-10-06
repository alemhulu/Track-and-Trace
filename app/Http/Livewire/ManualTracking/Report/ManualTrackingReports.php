<?php

namespace App\Http\Livewire\ManualTracking\Report;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockLedger;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualTrackingReports extends Component
{
    public function render()
    {
        $locationSummary = ManualDistribution::query()
            ->selectRaw('COALESCE(regions.name, CONCAT("Region ", manual_distributions.region_id)) as region_name, COALESCE(zones.name, CONCAT("Zone ", manual_distributions.zone_id)) as zone_name, COALESCE(woredas.name, CONCAT("Woreda ", manual_distributions.woreda_id)) as woreda_name, SUM(manual_distribution_lines.quantity) as total_qty')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->leftJoin('regions', 'regions.id', '=', 'manual_distributions.region_id')
            ->leftJoin('zones', 'zones.id', '=', 'manual_distributions.zone_id')
            ->leftJoin('woredas', 'woredas.id', '=', 'manual_distributions.woreda_id')
            ->groupBy('manual_distributions.region_id', 'manual_distributions.zone_id', 'manual_distributions.woreda_id', 'regions.name', 'zones.name', 'woredas.name')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();

        $bookBalance = ManualStockLedger::query()
            ->selectRaw('manual_books.title, SUM(CASE WHEN movement_qty > 0 THEN movement_qty ELSE 0 END) as stock_in, SUM(CASE WHEN movement_qty < 0 THEN ABS(movement_qty) ELSE 0 END) as stock_out, MAX(balance_after) as latest_balance')
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_ledgers.manual_book_id')
            ->groupBy('manual_books.title')
            ->orderBy('manual_books.title')
            ->get();

        $userActivity = DB::table('manual_audits')
            ->selectRaw('manual_audits.user_id, users.name as user_name, manual_audits.action, COUNT(*) as total_actions')
            ->leftJoin('users', 'users.id', '=', 'manual_audits.user_id')
            ->groupBy('manual_audits.user_id', 'users.name', 'manual_audits.action')
            ->orderByDesc('total_actions')
            ->limit(30)
            ->get();

        $movementHistory = ManualDistribution::query()
            ->selectRaw('manual_distributions.reference, manual_distributions.distributed_at, manual_distribution_lines.quantity, manual_books.title as book_title, COALESCE(source_org.name, CONCAT("Org ", manual_distributions.organization_id)) as source_name, COALESCE(destination_org.name, CONCAT("Org ", manual_distributions.destination_organization_id)) as destination_name')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->leftJoin('manual_books', 'manual_books.id', '=', 'manual_distribution_lines.manual_book_id')
            ->leftJoin('organizations as source_org', 'source_org.id', '=', 'manual_distributions.organization_id')
            ->leftJoin('organizations as destination_org', 'destination_org.id', '=', 'manual_distributions.destination_organization_id')
            ->orderByDesc('manual_distributions.id')
            ->limit(30)
            ->get();

        $summary = [
            'total_books' => ManualBook::count(),
            'total_packages' => ManualBookPackage::count(),
            'total_distributions' => ManualDistribution::count(),
            'total_quantity_distributed' => (int) ManualDistributionLine::sum('quantity'),
            'stock_in_total' => (int) ManualStockLedger::where('movement_qty', '>', 0)->sum('movement_qty'),
            'stock_out_total' => (int) ManualStockLedger::where('movement_qty', '<', 0)->sum(DB::raw('ABS(movement_qty)')),
            'active_users' => (int) DB::table('manual_audits')->distinct('user_id')->count('user_id'),
        ];

        $monthlyTrend = ManualDistribution::query()
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->selectRaw("DATE_FORMAT(manual_distributions.distributed_at, '%Y-%m') as period, SUM(manual_distribution_lines.quantity) as total_qty")
            ->whereNotNull('manual_distributions.distributed_at')
            ->groupByRaw("DATE_FORMAT(manual_distributions.distributed_at, '%Y-%m')")
            ->orderBy('period')
            ->limit(12)
            ->get();

        $locationBreakdown = DB::table('manual_distributions')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->leftJoin('regions', 'regions.id', '=', 'manual_distributions.region_id')
            ->selectRaw("COALESCE(regions.name, CONCAT('Region ', manual_distributions.region_id)) as label, SUM(manual_distribution_lines.quantity) as total_qty")
            ->groupBy('manual_distributions.region_id', 'regions.name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        $distributionTrendLabels = $monthlyTrend->map(function ($item): string {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $item->period);

            return $date->format('M Y');
        })->values()->all();
        $distributionTrendSeries = $monthlyTrend->pluck('total_qty')->map(fn($value) => (int) $value)->values()->all();

        $locationChartLabels = $locationBreakdown->pluck('label')->all();
        $locationChartSeries = $locationBreakdown->pluck('total_qty')->map(fn($value) => (int) $value)->values()->all();

        return view('livewire.manual-tracking.report.manual-tracking-reports', [
            'summary' => $summary,
            'locationSummary' => $locationSummary,
            'bookBalance' => $bookBalance,
            'userActivity' => $userActivity,
            'movementHistory' => $movementHistory,
            'distributionTrendLabels' => $distributionTrendLabels,
            'distributionTrendSeries' => $distributionTrendSeries,
            'locationChartLabels' => $locationChartLabels,
            'locationChartSeries' => $locationChartSeries,
        ])->extends('main.manual-tracking.index');
    }

    public function exportLocationSummaryCsv(): StreamedResponse
    {
        $rows = ManualDistribution::query()
            ->selectRaw('COALESCE(regions.name, CONCAT("Region ", manual_distributions.region_id)) as region_name, COALESCE(zones.name, CONCAT("Zone ", manual_distributions.zone_id)) as zone_name, COALESCE(woredas.name, CONCAT("Woreda ", manual_distributions.woreda_id)) as woreda_name, SUM(manual_distribution_lines.quantity) as total_qty')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->leftJoin('regions', 'regions.id', '=', 'manual_distributions.region_id')
            ->leftJoin('zones', 'zones.id', '=', 'manual_distributions.zone_id')
            ->leftJoin('woredas', 'woredas.id', '=', 'manual_distributions.woreda_id')
            ->groupBy('manual_distributions.region_id', 'manual_distributions.zone_id', 'manual_distributions.woreda_id', 'regions.name', 'zones.name', 'woredas.name')
            ->orderByDesc('total_qty')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['region', 'zone', 'woreda', 'total_qty']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->region_name, $row->zone_name, $row->woreda_name, $row->total_qty]);
            }
            fclose($handle);
        }, 'manual-location-summary.csv');
    }

    public function exportBookBalanceCsv(): StreamedResponse
    {
        $rows = ManualStockLedger::query()
            ->selectRaw('manual_books.title, SUM(CASE WHEN movement_qty > 0 THEN movement_qty ELSE 0 END) as stock_in, SUM(CASE WHEN movement_qty < 0 THEN ABS(movement_qty) ELSE 0 END) as stock_out, MAX(balance_after) as latest_balance')
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_ledgers.manual_book_id')
            ->groupBy('manual_books.title')
            ->orderBy('manual_books.title')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['book', 'stock_in', 'stock_out', 'latest_balance']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->title, $row->stock_in, $row->stock_out, $row->latest_balance]);
            }
            fclose($handle);
        }, 'manual-book-balance.csv');
    }

    public function exportUserActivityCsv(): StreamedResponse
    {
        $rows = DB::table('manual_audits')
            ->selectRaw('manual_audits.user_id, users.name as user_name, manual_audits.action, COUNT(*) as total_actions')
            ->leftJoin('users', 'users.id', '=', 'manual_audits.user_id')
            ->groupBy('manual_audits.user_id', 'users.name', 'manual_audits.action')
            ->orderByDesc('total_actions')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['user', 'action', 'total_actions']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->user_name ?? 'Unknown', $row->action, $row->total_actions]);
            }
            fclose($handle);
        }, 'manual-user-activity.csv');
    }

    public function exportMovementHistoryCsv(): StreamedResponse
    {
        $rows = ManualDistribution::query()
            ->selectRaw('manual_distributions.reference, manual_distributions.distributed_at, manual_distribution_lines.quantity, manual_books.title as book_title, COALESCE(source_org.name, CONCAT("Org ", manual_distributions.organization_id)) as source_name, COALESCE(destination_org.name, CONCAT("Org ", manual_distributions.destination_organization_id)) as destination_name')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->leftJoin('manual_books', 'manual_books.id', '=', 'manual_distribution_lines.manual_book_id')
            ->leftJoin('organizations as source_org', 'source_org.id', '=', 'manual_distributions.organization_id')
            ->leftJoin('organizations as destination_org', 'destination_org.id', '=', 'manual_distributions.destination_organization_id')
            ->orderByDesc('manual_distributions.id')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['reference', 'book', 'source', 'destination', 'distributed_at', 'quantity']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->reference, $row->book_title, $row->source_name, $row->destination_name, $row->distributed_at, $row->quantity]);
            }
            fclose($handle);
        }, 'manual-package-movement-history.csv');
    }
}
