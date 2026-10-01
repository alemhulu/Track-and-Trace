<?php

namespace App\Http\Livewire\ManualTracking\Report;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockLedger;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Component;

class ManualTrackingReports extends Component
{
    public function render()
    {
        $locationSummary = ManualDistribution::query()
            ->selectRaw('COALESCE(region_id, 0) as region_id, COALESCE(zone_id, 0) as zone_id, COALESCE(woreda_id, 0) as woreda_id, SUM(quantity) as total_qty')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->groupBy('region_id', 'zone_id', 'woreda_id')
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
            ->selectRaw('user_id, action, COUNT(*) as total_actions')
            ->groupBy('user_id', 'action')
            ->orderByDesc('total_actions')
            ->limit(30)
            ->get();

        $movementHistory = ManualDistribution::query()
            ->selectRaw('manual_distributions.reference, manual_distributions.distributed_at, manual_distribution_lines.quantity')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->orderByDesc('manual_distributions.id')
            ->limit(30)
            ->get();

        return view('livewire.manual-tracking.report.manual-tracking-reports', [
            'locationSummary' => $locationSummary,
            'bookBalance' => $bookBalance,
            'userActivity' => $userActivity,
            'movementHistory' => $movementHistory,
        ])->extends('main.manual-tracking.index');
    }

    public function exportLocationSummaryCsv(): StreamedResponse
    {
        $rows = ManualDistribution::query()
            ->selectRaw('COALESCE(region_id, 0) as region_id, COALESCE(zone_id, 0) as zone_id, COALESCE(woreda_id, 0) as woreda_id, SUM(quantity) as total_qty')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->groupBy('region_id', 'zone_id', 'woreda_id')
            ->orderByDesc('total_qty')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['region_id', 'zone_id', 'woreda_id', 'total_qty']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->region_id, $row->zone_id, $row->woreda_id, $row->total_qty]);
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
            ->selectRaw('user_id, action, COUNT(*) as total_actions')
            ->groupBy('user_id', 'action')
            ->orderByDesc('total_actions')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['user_id', 'action', 'total_actions']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->user_id, $row->action, $row->total_actions]);
            }
            fclose($handle);
        }, 'manual-user-activity.csv');
    }

    public function exportMovementHistoryCsv(): StreamedResponse
    {
        $rows = ManualDistribution::query()
            ->selectRaw('manual_distributions.reference, manual_distributions.distributed_at, manual_distribution_lines.quantity')
            ->join('manual_distribution_lines', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->orderByDesc('manual_distributions.id')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['reference', 'distributed_at', 'quantity']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->reference, $row->distributed_at, $row->quantity]);
            }
            fclose($handle);
        }, 'manual-package-movement-history.csv');
    }
}
