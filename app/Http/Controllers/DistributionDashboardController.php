<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\User;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualDistribution;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DistributionDashboardController extends Controller
{
    private const TREND_DAYS = 30;

    public function __invoke(): View
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        return view('dashboards.distribution', [
            'distributionData' => [
                'trendDays' => self::TREND_DAYS,
                'digital' => [
                    'kpiCards' => $this->buildDigitalKpiCards($actor),
                    'regionalChart' => $this->buildDigitalRegionalChart($actor),
                    'trendsChart' => $this->buildDigitalTrendsChart($actor),
                    'recent' => $this->buildRecentHubs($actor),
                ],
                'manual' => [
                    'kpiCards' => $this->buildManualKpiCards($actor),
                    'regionalChart' => $this->buildManualRegionalChart($actor),
                    'trendsChart' => $this->buildManualTrendsChart($actor),
                    'recent' => $this->buildRecentManualDistributions($actor),
                ],
            ],
        ]);
    }

    protected function buildDigitalKpiCards(?User $actor): array
    {
        $packages = fn () => Package::query()->when($actor, fn($query) => $query->accessibleBy($actor));

        return [
            [
                'label' => 'Distributed Books',
                'value' => (int) $packages()->sum('sent'),
                'hint' => 'Copies sent between warehouses',
                'style' => 'blue',
            ],
            [
                'label' => 'Shipments',
                'value' => (int) $packages()->where('sent', '>', 0)->count(),
                'hint' => 'Warehouse consignments dispatched',
                'style' => 'indigo',
            ],
            [
                'label' => 'In Transit',
                'value' => (int) $packages()->where('received', 0)->where('delivery_status', false)->sum('no_of_books'),
                'hint' => 'Copies dispatched but not yet received',
                'style' => 'amber',
            ],
        ];
    }

    protected function buildManualKpiCards(?User $actor): array
    {
        $manualDistributionIds = (clone $this->manualDistributionScope($actor))->select('id');

        return [
            [
                'label' => 'Distributed Books',
                'value' => (int) ManualDistributionLine::query()
                    ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIds)
                    ->sum('quantity'),
                'hint' => 'Copies handed out manually',
                'style' => 'teal',
            ],
            [
                'label' => 'Distribution Records',
                'value' => (int) $this->manualDistributionScope($actor)->count(),
                'hint' => 'Manual distributions logged',
                'style' => 'indigo',
            ],
            [
                'label' => 'Destinations Reached',
                'value' => (int) $this->manualDistributionScope($actor)
                    ->whereNotNull('destination_organization_id')
                    ->distinct()
                    ->count('destination_organization_id'),
                'hint' => 'Organizations that received manual stock',
                'style' => 'amber',
            ],
        ];
    }

    protected function buildDigitalRegionalChart(?User $actor): array
    {
        // Simplified regional breakdown based on Warehouse location
        $data = \App\Models\WareHouse::query()
            ->join('regions', 'regions.id', '=', 'ware_houses.region_id')
            ->selectRaw('regions.name, SUM((SELECT SUM(balance) FROM packages WHERE packages.ware_house_id = ware_houses.id)) as total_stock')
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->groupBy('regions.name')
            ->get();

        return [
            'categories' => $data->pluck('name')->all(),
            'series' => [
                ['name' => 'Regional Stock', 'data' => $data->pluck('total_stock')->map(fn($v) => (int)$v)->all()],
            ],
        ];
    }

    protected function buildManualRegionalChart(?User $actor): array
    {
        $data = ManualDistributionLine::query()
            ->join('manual_distributions', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->join('regions', 'regions.id', '=', 'manual_distributions.destination_region_id')
            ->whereIn('manual_distributions.id', (clone $this->manualDistributionScope($actor))->select('id'))
            ->selectRaw('regions.name, SUM(manual_distribution_lines.quantity) as total')
            ->groupBy('regions.name')
            ->get();

        return [
            'categories' => $data->pluck('name')->all(),
            'series' => [
                ['name' => 'Copies Distributed', 'data' => $data->pluck('total')->map(fn($v) => (int)$v)->all()],
            ],
        ];
    }

    protected function buildDigitalTrendsChart(?User $actor): array
    {
        $totals = Package::query()
            ->selectRaw('DATE(updated_at) as date, SUM(sent) as total')
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->where('sent', '>', 0)
            ->where('updated_at', '>=', $this->trendStart())
            ->groupBy('date')
            ->pluck('total', 'date');

        return $this->dailySeries('Books Sent', $totals);
    }

    protected function buildManualTrendsChart(?User $actor): array
    {
        $totals = ManualDistributionLine::query()
            ->join('manual_distributions', 'manual_distributions.id', '=', 'manual_distribution_lines.manual_distribution_id')
            ->whereIn('manual_distributions.id', (clone $this->manualDistributionScope($actor))->select('id'))
            ->selectRaw('DATE(COALESCE(manual_distributions.distributed_at, manual_distributions.created_at)) as date, SUM(manual_distribution_lines.quantity) as total')
            ->whereRaw('COALESCE(manual_distributions.distributed_at, manual_distributions.created_at) >= ?', [$this->trendStart()])
            ->groupBy('date')
            ->pluck('total', 'date');

        return $this->dailySeries('Books Distributed', $totals);
    }

    protected function buildRecentHubs(?User $actor): array
    {
        return \App\Models\WareHouse::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->with(['organization:id,name', 'region:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($w) => [
                'name' => $w->organization->name ?? 'Branch ' . $w->branch,
                'detail' => $w->region?->name ?? '—',
                'status' => 'Active',
            ])
            ->all();
    }

    protected function buildRecentManualDistributions(?User $actor): array
    {
        return $this->manualDistributionScope($actor)
            ->with(['lines.book:id,title', 'organization:id,name', 'destinationOrganization:id,name'])
            ->orderByDesc('distributed_at')
            ->limit(5)
            ->get()
            ->map(fn(ManualDistribution $distribution) => [
                'name' => $distribution->lines->pluck('book.title')->filter()->join(', ') ?: $distribution->reference,
                'detail' => ($distribution->organization->name ?? '—') . ' → ' . ($distribution->destinationOrganization->name ?? '—'),
                'status' => number_format((int) $distribution->lines->sum('quantity')) . ' copies · ' . optional($distribution->distributed_at)->format('M j'),
            ])
            ->all();
    }

    protected function manualDistributionScope(?User $actor): Builder
    {
        return ManualDistribution::query()->accessibleBy($actor);
    }

    private function trendStart()
    {
        return now()->subDays(self::TREND_DAYS - 1)->startOfDay();
    }

    /**
     * One point per day, including days with no activity, so both trend charts share an axis.
     */
    private function dailySeries(string $name, Collection $totalsByDate): array
    {
        $days = collect(range(0, self::TREND_DAYS - 1))
            ->map(fn ($offset) => $this->trendStart()->addDays($offset)->toDateString());

        return [
            'categories' => $days->all(),
            'series' => [
                ['name' => $name, 'data' => $days->map(fn ($day) => (int) ($totalsByDate[$day] ?? 0))->all()],
            ],
        ];
    }
}
