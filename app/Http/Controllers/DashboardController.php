<?php

namespace App\Http\Controllers;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\User;
use App\Models\WareHouse;
use App\Services\Oversight\OversightService;
use App\Support\AcademicYear;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        return view('dashboard', [
            'dashboardData' => [
                'scopeLabel' => $this->buildScopeLabel($actor),
                'scopeLevel' => $this->buildScopeLevel($actor),
                'kpiGroups' => $this->buildKpiGroups($actor),
                'oversight' => $actor ? app(OversightService::class)->overview($actor, AcademicYear::current()) : null,
                'academicYear' => AcademicYear::label(AcademicYear::current()),
                'charts' => [
                    'digital' => [
                        'subject' => $this->buildDigitalSubjectChart($actor),
                        'status' => $this->buildPrintOrderChart($actor),
                        'summary' => $this->buildDigitalBookSummaryChart($actor),
                        'network' => $this->buildWarehouseChart($actor),
                    ],
                    'manual' => [
                        'subject' => $this->buildManualSubjectChart($actor),
                        'status' => $this->buildManualShipmentStatusChart($actor),
                        'summary' => $this->buildManualBookSummaryChart($actor),
                        'network' => $this->buildManualNetworkChart($actor),
                    ],
                ],
            ],
        ]);
    }

    protected function buildKpiGroups(?User $actor): array
    {
        $currentPeriodStart = now()->subDays(30);
        $previousPeriodStart = now()->subDays(60);
        $previousPeriodEnd = now()->subDays(30);

        $printOrders = (int) PrintOrder::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->count();
        $printOrdersCurrentWindow = (int) PrintOrder::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('created_at', '>=', $currentPeriodStart)
            ->count();
        $printOrdersPreviousWindow = (int) PrintOrder::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('created_at', '>=', $previousPeriodStart)
            ->where('created_at', '<', $previousPeriodEnd)
            ->count();

        $manualDistributionIds = (clone $this->manualDistributionScope($actor))->select('id');
        $manualDistributionIdsCurrentWindow = (clone $this->manualDistributionScope($actor))
            ->where(function (Builder $windowQuery) use ($currentPeriodStart): void {
                $windowQuery->where('distributed_at', '>=', $currentPeriodStart)
                    ->orWhere(function (Builder $fallbackQuery) use ($currentPeriodStart): void {
                        $fallbackQuery->whereNull('distributed_at')
                            ->where('created_at', '>=', $currentPeriodStart);
                    });
            })
            ->select('id');
        $manualDistributionIdsPreviousWindow = (clone $this->manualDistributionScope($actor))
            ->where(function (Builder $windowQuery) use ($previousPeriodStart, $previousPeriodEnd): void {
                $windowQuery->where(function (Builder $datedDistribution) use ($previousPeriodStart, $previousPeriodEnd): void {
                    $datedDistribution->whereNotNull('distributed_at')
                        ->where('distributed_at', '>=', $previousPeriodStart)
                        ->where('distributed_at', '<', $previousPeriodEnd);
                })->orWhere(function (Builder $fallbackQuery) use ($previousPeriodStart, $previousPeriodEnd): void {
                    $fallbackQuery->whereNull('distributed_at')
                        ->where('created_at', '>=', $previousPeriodStart)
                        ->where('created_at', '<', $previousPeriodEnd);
                });
            })
            ->select('id');

        $digitalDistributed = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->sum('sent');
        $manualDistributed = (int) ManualDistributionLine::query()
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIds)
            ->sum('quantity');
        $digitalDistributedCurrentWindow = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('updated_at', '>=', $currentPeriodStart)
            ->sum('sent');
        $manualDistributedCurrentWindow = (int) ManualDistributionLine::query()
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIdsCurrentWindow)
            ->sum('quantity');
        $digitalDistributedPreviousWindow = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('updated_at', '>=', $previousPeriodStart)
            ->where('updated_at', '<', $previousPeriodEnd)
            ->sum('sent');
        $manualDistributedPreviousWindow = (int) ManualDistributionLine::query()
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIdsPreviousWindow)
            ->sum('quantity');

        $digitalStock = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->sum('balance');
        $manualStock = max(0, (int) $this->manualStockLedgerScope($actor)->sum('movement_qty'));
        $digitalStockCurrentWindow = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('updated_at', '>=', $currentPeriodStart)
            ->sum('balance');
        $manualStockCurrentWindow = max(
            0,
            (int) $this->manualStockLedgerScope($actor)
                ->where('created_at', '>=', $currentPeriodStart)
                ->sum('movement_qty')
        );
        $digitalStockPreviousWindow = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('updated_at', '>=', $previousPeriodStart)
            ->where('updated_at', '<', $previousPeriodEnd)
            ->sum('balance');
        $manualStockPreviousWindow = max(
            0,
            (int) $this->manualStockLedgerScope($actor)
                ->where('created_at', '>=', $previousPeriodStart)
                ->where('created_at', '<', $previousPeriodEnd)
                ->sum('movement_qty')
        );

        $warehouses = (int) WareHouse::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->count();
        $manualOrganizations = $this->manualOrganizationCount($actor);
        $warehousesCurrentWindow = (int) WareHouse::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('created_at', '>=', $currentPeriodStart)
            ->count();
        $manualOrganizationsCurrentWindow = $this->manualOrganizationCountByWindow($actor, $currentPeriodStart, null);
        $warehousesPreviousWindow = (int) WareHouse::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where('created_at', '>=', $previousPeriodStart)
            ->where('created_at', '<', $previousPeriodEnd)
            ->count();
        $manualOrganizationsPreviousWindow = $this->manualOrganizationCountByWindow($actor, $previousPeriodStart, $previousPeriodEnd);

        $manualMovements = (int) (clone $this->manualDistributionScope($actor))->count();
        $manualMovementsCurrentWindow = (int) (clone $this->manualDistributionScope($actor))
            ->where(function (Builder $windowQuery) use ($currentPeriodStart): void {
                $windowQuery->where('distributed_at', '>=', $currentPeriodStart)
                    ->orWhere(function (Builder $fallbackQuery) use ($currentPeriodStart): void {
                        $fallbackQuery->whereNull('distributed_at')
                            ->where('created_at', '>=', $currentPeriodStart);
                    });
            })
            ->count();
        $manualMovementsPreviousWindow = (int) (clone $this->manualDistributionScope($actor))
            ->where(function (Builder $windowQuery) use ($previousPeriodStart, $previousPeriodEnd): void {
                $windowQuery->where(function (Builder $datedDistribution) use ($previousPeriodStart, $previousPeriodEnd): void {
                    $datedDistribution->whereNotNull('distributed_at')
                        ->where('distributed_at', '>=', $previousPeriodStart)
                        ->where('distributed_at', '<', $previousPeriodEnd);
                })->orWhere(function (Builder $fallbackQuery) use ($previousPeriodStart, $previousPeriodEnd): void {
                    $fallbackQuery->whereNull('distributed_at')
                        ->where('created_at', '>=', $previousPeriodStart)
                        ->where('created_at', '<', $previousPeriodEnd);
                });
            })
            ->count();

        return [
            'digital' => [
                'title' => 'Digital Tracking',
                'subtitle' => 'Barcoded packages moving through the warehouse chain',
                'cards' => [
                    [
                        'label' => 'Print Orders',
                        'value' => $printOrders,
                        'hint' => 'Orders placed with printers',
                        'style' => 'indigo',
                        'delta' => $this->buildDelta($printOrdersCurrentWindow, $printOrdersPreviousWindow),
                    ],
                    [
                        'label' => 'Distributed Books',
                        'value' => $digitalDistributed,
                        'hint' => 'Copies sent between warehouses',
                        'style' => 'blue',
                        'delta' => $this->buildDelta($digitalDistributedCurrentWindow, $digitalDistributedPreviousWindow),
                    ],
                    [
                        'label' => 'Stock on Hand',
                        'value' => $digitalStock,
                        'hint' => 'Copies held in warehouses',
                        'style' => 'emerald',
                        'delta' => $this->buildDelta($digitalStockCurrentWindow, $digitalStockPreviousWindow),
                    ],
                    [
                        'label' => 'Warehouses',
                        'value' => $warehouses,
                        'hint' => 'Registered storage locations',
                        'style' => 'amber',
                        'delta' => $this->buildDelta($warehousesCurrentWindow, $warehousesPreviousWindow),
                    ],
                ],
            ],
            'manual' => [
                'title' => 'Manual Tracking',
                'subtitle' => 'Supplementary books recorded by hand',
                'cards' => [
                    [
                        'label' => 'Distributed Books',
                        'value' => $manualDistributed,
                        'hint' => 'Copies handed out manually',
                        'style' => 'teal',
                        'delta' => $this->buildDelta($manualDistributedCurrentWindow, $manualDistributedPreviousWindow),
                    ],
                    [
                        'label' => 'Stock on Hand',
                        'value' => $manualStock,
                        'hint' => 'Copies left in manual packages',
                        'style' => 'emerald',
                        'delta' => $this->buildDelta($manualStockCurrentWindow, $manualStockPreviousWindow),
                    ],
                    [
                        'label' => 'Organizations',
                        'value' => $manualOrganizations,
                        'hint' => 'Senders and receivers of manual stock',
                        'style' => 'amber',
                        'delta' => $this->buildDelta($manualOrganizationsCurrentWindow, $manualOrganizationsPreviousWindow),
                    ],
                    [
                        'label' => 'Distribution Records',
                        'value' => $manualMovements,
                        'hint' => 'Manual distributions logged',
                        'style' => 'slate',
                        'delta' => $this->buildDelta($manualMovementsCurrentWindow, $manualMovementsPreviousWindow),
                    ],
                ],
            ],
        ];
    }

    protected function buildDigitalSubjectChart(?User $actor): array
    {
        $printed = PrintOrder::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(print_orders.no_of_books, 0)) as total')
            ->join('books', 'books.id', '=', 'print_orders.book_id')
            ->join('subjects', 'subjects.id', '=', 'books.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $distributed = Package::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(packages.sent, 0)) as total')
            ->join('subjects', 'subjects.id', '=', 'packages.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $stored = Package::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(packages.balance, 0)) as total')
            ->join('subjects', 'subjects.id', '=', 'packages.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        return $this->subjectChart(['Printed' => $printed, 'Distributed' => $distributed, 'Store' => $stored]);
    }

    protected function buildManualSubjectChart(?User $actor): array
    {
        $manualDistributionIds = (clone $this->manualDistributionScope($actor))->select('id');
        $lineTotals = fn (string $column) => ManualDistributionLine::query()
            ->selectRaw("LOWER(TRIM(manual_books.subject_name)) as subject_key, SUM(COALESCE(manual_distribution_lines.{$column}, 0)) as total")
            ->join('manual_books', 'manual_books.id', '=', 'manual_distribution_lines.manual_book_id')
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIds)
            ->whereNotNull('manual_books.subject_name')
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        // Ledger entries are recorded against the holder, so a scoped sum is stock held in the area
        $stored = $this->manualStockLedgerScope($actor)
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_ledgers.manual_book_id')
            ->selectRaw('LOWER(TRIM(manual_books.subject_name)) as subject_key, SUM(COALESCE(manual_stock_ledgers.movement_qty, 0)) as total')
            ->whereNotNull('manual_books.subject_name')
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key')
            ->map(fn ($total) => max(0, (int) $total));

        return $this->subjectChart([
            'Dispatched' => $lineTotals('quantity'),
            'Received' => $lineTotals('received_quantity'),
            'In Stock' => $stored,
        ]);
    }

    /**
     * @param  array<string, \Illuminate\Support\Collection>  $seriesTotals  series name => totals keyed by subject
     */
    protected function subjectChart(array $seriesTotals): array
    {
        $subjectKeys = collect($seriesTotals)
            ->flatMap(fn ($totals) => $totals->keys()->all())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($subjectKeys->isEmpty()) {
            $subjectKeys = collect(['no data']);
        }

        return [
            'categories' => $subjectKeys->map(fn ($subjectKey) => $this->toDisplaySubjectName($subjectKey))->all(),
            'series' => collect($seriesTotals)
                ->map(fn ($totals, $name) => [
                    'name' => $name,
                    'data' => $subjectKeys->map(fn ($subjectKey) => (int) ($totals[$subjectKey] ?? 0))->all(),
                ])
                ->values()
                ->all(),
        ];
    }

    protected function buildPrintOrderChart(?User $actor): array
    {
        $printOrderQuery = PrintOrder::query()->when($actor, fn ($query) => $query->accessibleBy($actor));

        return [
            'title' => 'Print Orders',
            'series' => [
                (int) (clone $printOrderQuery)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 1)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 4)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 2)->count(),
            ],
            'labels' => ['Orders', 'Accepted', 'Rejected', 'Printed'],
        ];
    }

    protected function buildManualShipmentStatusChart(?User $actor): array
    {
        $counts = $this->manualDistributionScope($actor)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'title' => 'Shipments',
            'series' => collect(ManualDistribution::STATUSES)->keys()->map(fn ($status) => (int) ($counts[$status] ?? 0))->all(),
            'labels' => array_values(ManualDistribution::STATUSES),
        ];
    }

    protected function buildDigitalBookSummaryChart(?User $actor): array
    {
        $packages = fn () => Package::query()->when($actor, fn ($query) => $query->accessibleBy($actor));

        return [
            'title' => 'Books',
            'subtitle' => 'Digital book information',
            'series' => [
                (int) $packages()->sum('balance'),
                (int) $packages()->sum('sent'),
                (int) $packages()->sum('received'),
            ],
            'labels' => ['Stock', 'Distributed', 'On Students Hand'],
        ];
    }

    protected function buildManualBookSummaryChart(?User $actor): array
    {
        $lines = fn () => ManualDistributionLine::query()
            ->whereIn('manual_distribution_lines.manual_distribution_id', (clone $this->manualDistributionScope($actor))->select('id'));

        return [
            'title' => 'Books',
            'subtitle' => 'Manual book information',
            'series' => [
                max(0, (int) $this->manualStockLedgerScope($actor)->sum('movement_qty')),
                (int) $lines()->whereHas('distribution', fn ($shipment) => $shipment->where('status', ManualDistribution::STATUS_DISPATCHED))->sum('quantity'),
                (int) $lines()->sum('received_quantity'),
            ],
            'labels' => ['In Stock', 'In Transit', 'Received'],
        ];
    }

    protected function buildWarehouseChart(?User $actor): array
    {
        $warehouseQuery = WareHouse::query()->when($actor, fn ($query) => $query->accessibleBy($actor));

        return [
            'title' => 'Warehouses',
            'subtitle' => 'Warehouses information',
            'series' => [
                (int) (clone $warehouseQuery)->count(),
                (int) (clone $warehouseQuery)->distinct('branch')->count('branch'),
            ],
            'labels' => ['Warehouses', 'Stores'],
        ];
    }

    protected function buildManualNetworkChart(?User $actor): array
    {
        $distributions = $this->manualDistributionScope($actor)->get(['organization_id', 'destination_organization_id']);

        return [
            'title' => 'Organizations',
            'subtitle' => 'Manual tracking organizations',
            'series' => [
                $distributions->pluck('organization_id')->filter()->unique()->count(),
                $distributions->pluck('destination_organization_id')->filter()->unique()->count(),
            ],
            'labels' => ['Sending', 'Receiving'],
        ];
    }

    protected function toDisplaySubjectName(string $subjectKey): string
    {
        if ($subjectKey === 'no data') {
            return 'No Data';
        }

        return collect(explode(' ', $subjectKey))
            ->filter()
            ->map(fn ($segment) => ucfirst($segment))
            ->implode(' ');
    }

    protected function manualStockLedgerScope(?User $actor): Builder
    {
        $query = ManualStockLedger::query();

        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        $level = $actor->effectiveAccessLevel();

        if ($level === User::ACCESS_LEVEL_REGION && ! empty($actor->region_id)) {
            return $query->where('manual_stock_ledgers.region_id', $actor->region_id);
        }

        if ($level === User::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
            return $query->where('manual_stock_ledgers.region_id', $actor->region_id)->where('manual_stock_ledgers.zone_id', $actor->zone_id);
        }

        if ($level === User::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
            return $query
                ->where('manual_stock_ledgers.region_id', $actor->region_id)
                ->where('manual_stock_ledgers.zone_id', $actor->zone_id)
                ->where('manual_stock_ledgers.woreda_id', $actor->woreda_id);
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
            return $query->where(function (Builder $scope) use ($actor): void {
                $scope->where('manual_stock_ledgers.organization_id', $actor->organization_id)
                    ->orWhere('manual_stock_ledgers.acted_by', $actor->id);
            });
        }

        return $query->where('manual_stock_ledgers.acted_by', $actor->id);
    }

    protected function manualDistributionScope(?User $actor): Builder
    {
        return ManualDistribution::query()->accessibleBy($actor);
    }

    protected function manualOrganizationCount(?User $actor): int
    {
        return (int) $this->manualDistributionScope($actor)
            ->get(['organization_id', 'destination_organization_id'])
            ->flatMap(function (ManualDistribution $distribution): array {
                return [$distribution->organization_id, $distribution->destination_organization_id];
            })
            ->filter()
            ->unique()
            ->count();
    }

    protected function manualOrganizationCountByWindow(?User $actor, $startAt, $endAt): int
    {
        $distributionQuery = clone $this->manualDistributionScope($actor);

        $distributionQuery->where(function (Builder $windowQuery) use ($startAt, $endAt): void {
            $windowQuery->where(function (Builder $datedDistribution) use ($startAt, $endAt): void {
                $datedDistribution->whereNotNull('distributed_at')
                    ->where('distributed_at', '>=', $startAt);

                if ($endAt) {
                    $datedDistribution->where('distributed_at', '<', $endAt);
                }
            })->orWhere(function (Builder $fallbackQuery) use ($startAt, $endAt): void {
                $fallbackQuery->whereNull('distributed_at')
                    ->where('created_at', '>=', $startAt);

                if ($endAt) {
                    $fallbackQuery->where('created_at', '<', $endAt);
                }
            });
        });

        return (int) $distributionQuery
            ->get(['organization_id', 'destination_organization_id'])
            ->flatMap(function (ManualDistribution $distribution): array {
                return [$distribution->organization_id, $distribution->destination_organization_id];
            })
            ->filter()
            ->unique()
            ->count();
    }

    protected function buildDelta(int $current, int $previous): array
    {
        if ($current === 0 && $previous === 0) {
            return [
                'label' => '0.0% vs previous 30 days',
                'direction' => 'flat',
            ];
        }

        if ($previous === 0) {
            return [
                'label' => '+100.0% vs previous 30 days',
                'direction' => 'up',
            ];
        }

        $changePercent = (($current - $previous) / $previous) * 100;
        $direction = $changePercent > 0 ? 'up' : ($changePercent < 0 ? 'down' : 'flat');

        return [
            'label' => sprintf('%+.1f%% vs previous 30 days', $changePercent),
            'direction' => $direction,
        ];
    }

    protected function buildScopeLabel(?User $actor): string
    {
        if (! $actor) {
            return 'No Access Scope';
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return 'National Scope';
        }

        return match ($actor->effectiveAccessLevel()) {
            User::ACCESS_LEVEL_REGION => 'Region Scope',
            User::ACCESS_LEVEL_ZONE => 'Zone Scope',
            User::ACCESS_LEVEL_WOREDA => 'Woreda Scope',
            User::ACCESS_LEVEL_ORGANIZATION => 'Organization Scope',
            default => 'User Scope',
        };
    }

    protected function buildScopeLevel(?User $actor): string
    {
        if (! $actor) {
            return 'none';
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return User::ACCESS_LEVEL_NATIONAL;
        }

        return $actor->effectiveAccessLevel() ?: 'user';
    }
}
