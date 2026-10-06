<?php

namespace App\Http\Controllers;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        $kpiCards = $this->buildKpiCards($actor);
        $subjectChart = $this->buildSubjectChart($actor);
        $printOrderChart = $this->buildPrintOrderChart($actor);
        $bookSummaryChart = $this->buildBookSummaryChart($actor);
        $warehouseChart = $this->buildWarehouseChart($actor);

        return view('dashboard', [
            'dashboardData' => [
                'scopeLabel' => $this->buildScopeLabel($actor),
                'scopeLevel' => $this->buildScopeLevel($actor),
                'kpiCards' => $kpiCards,
                'subjectChart' => $subjectChart,
                'printOrderChart' => $printOrderChart,
                'bookSummaryChart' => $bookSummaryChart,
                'warehouseChart' => $warehouseChart,
            ],
        ]);
    }

    protected function buildKpiCards(?User $actor): array
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
            [
                'label' => 'Print Orders',
                'value' => $printOrders,
                'hint' => 'Digital print workflow orders',
                'style' => 'indigo',
                'delta' => $this->buildDelta($printOrdersCurrentWindow, $printOrdersPreviousWindow),
            ],
            [
                'label' => 'Distributed Books',
                'value' => $digitalDistributed + $manualDistributed,
                'hint' => 'Digital + manual distributed quantity',
                'style' => 'blue',
                'delta' => $this->buildDelta(
                    $digitalDistributedCurrentWindow + $manualDistributedCurrentWindow,
                    $digitalDistributedPreviousWindow + $manualDistributedPreviousWindow
                ),
            ],
            [
                'label' => 'Stock on Hand',
                'value' => $digitalStock + $manualStock,
                'hint' => 'Current digital + manual stock balance',
                'style' => 'emerald',
                'delta' => $this->buildDelta(
                    $digitalStockCurrentWindow + $manualStockCurrentWindow,
                    $digitalStockPreviousWindow + $manualStockPreviousWindow
                ),
            ],
            [
                'label' => 'Warehouses',
                'value' => $warehouses + $manualOrganizations,
                'hint' => 'Warehouses + manual-tracked organizations',
                'style' => 'amber',
                'delta' => $this->buildDelta(
                    $warehousesCurrentWindow + $manualOrganizationsCurrentWindow,
                    $warehousesPreviousWindow + $manualOrganizationsPreviousWindow
                ),
            ],
            [
                'label' => 'Manual Movements',
                'value' => $manualMovements,
                'hint' => 'Manual distribution records',
                'style' => 'teal',
                'delta' => $this->buildDelta($manualMovementsCurrentWindow, $manualMovementsPreviousWindow),
            ],
        ];
    }

    protected function buildSubjectChart(?User $actor): array
    {
        $digitalPrinted = PrintOrder::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(print_orders.no_of_books, 0)) as total')
            ->join('books', 'books.id', '=', 'print_orders.book_id')
            ->join('subjects', 'subjects.id', '=', 'books.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $digitalDistributed = Package::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(packages.sent, 0)) as total')
            ->join('subjects', 'subjects.id', '=', 'packages.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $digitalStored = Package::query()
            ->selectRaw('LOWER(TRIM(subjects.name)) as subject_key, SUM(COALESCE(packages.balance, 0)) as total')
            ->join('subjects', 'subjects.id', '=', 'packages.subject_id')
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $manualPrinted = $this->manualStockLedgerScope($actor)
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_ledgers.manual_book_id')
            ->selectRaw('LOWER(TRIM(manual_books.subject_name)) as subject_key, SUM(COALESCE(manual_stock_ledgers.movement_qty, 0)) as total')
            ->where('manual_stock_ledgers.movement_qty', '>', 0)
            ->whereNotNull('manual_books.subject_name')
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $manualDistributionIds = (clone $this->manualDistributionScope($actor))->select('id');

        $manualDistributed = ManualDistributionLine::query()
            ->selectRaw('LOWER(TRIM(manual_books.subject_name)) as subject_key, SUM(COALESCE(manual_distribution_lines.quantity, 0)) as total')
            ->join('manual_books', 'manual_books.id', '=', 'manual_distribution_lines.manual_book_id')
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIds)
            ->whereNotNull('manual_books.subject_name')
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $manualStored = $this->manualStockLedgerScope($actor)
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_ledgers.manual_book_id')
            ->selectRaw('LOWER(TRIM(manual_books.subject_name)) as subject_key, SUM(COALESCE(manual_stock_ledgers.movement_qty, 0)) as total')
            ->whereNotNull('manual_books.subject_name')
            ->groupBy('subject_key')
            ->pluck('total', 'subject_key');

        $subjectKeys = collect([
            ...$digitalPrinted->keys()->all(),
            ...$digitalDistributed->keys()->all(),
            ...$digitalStored->keys()->all(),
            ...$manualPrinted->keys()->all(),
            ...$manualDistributed->keys()->all(),
            ...$manualStored->keys()->all(),
        ])->filter()->unique()->sort()->values();

        if ($subjectKeys->isEmpty()) {
            $subjectKeys = collect(['no data']);
        }

        $categories = [];
        $printedSeries = [];
        $distributedSeries = [];
        $storedSeries = [];

        foreach ($subjectKeys as $subjectKey) {
            $displayName = $this->toDisplaySubjectName($subjectKey);

            $printedTotal = (int) ($digitalPrinted[$subjectKey] ?? 0) + (int) ($manualPrinted[$subjectKey] ?? 0);
            $distributedTotal = (int) ($digitalDistributed[$subjectKey] ?? 0) + (int) ($manualDistributed[$subjectKey] ?? 0);
            $storedTotal = (int) ($digitalStored[$subjectKey] ?? 0) + (int) ($manualStored[$subjectKey] ?? 0);

            $categories[] = $displayName;
            $printedSeries[] = $printedTotal;
            $distributedSeries[] = $distributedTotal;
            $storedSeries[] = $storedTotal;
        }

        return [
            'categories' => $categories,
            'series' => [
                ['name' => 'Printed', 'data' => $printedSeries],
                ['name' => 'Distributed', 'data' => $distributedSeries],
                ['name' => 'Store', 'data' => $storedSeries],
            ],
        ];
    }

    protected function buildPrintOrderChart(?User $actor): array
    {
        $printOrderQuery = PrintOrder::query()->when($actor, fn ($query) => $query->accessibleBy($actor));
        $manualDistributionQuery = $this->manualDistributionScope($actor);

        return [
            'series' => [
                (int) (clone $printOrderQuery)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 1)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 4)->count(),
                (int) (clone $printOrderQuery)->where('request_status', 2)->count(),
                (int) (clone $manualDistributionQuery)->count(),
            ],
            'labels' => ['Orders', 'Accepted', 'Rejected', 'Printed', 'Manual Movements'],
        ];
    }

    protected function buildBookSummaryChart(?User $actor): array
    {
        $digitalStock = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->sum('balance');

        $digitalDistributed = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->sum('sent');

        $digitalOnStudentHand = (int) Package::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->sum('received');

        $manualStock = (int) $this->manualStockLedgerScope($actor)->sum('movement_qty');
        $manualDistributionIds = (clone $this->manualDistributionScope($actor))->select('id');
        $manualDistributed = (int) ManualDistributionLine::query()
            ->whereIn('manual_distribution_lines.manual_distribution_id', $manualDistributionIds)
            ->sum('quantity');
        $manualOnHand = max(0, $manualStock);

        return [
            'series' => [
                $digitalStock + $manualOnHand,
                $digitalDistributed + $manualDistributed,
                $digitalOnStudentHand + $manualOnHand,
            ],
            'labels' => ['Stock', 'Distributed', 'On Students Hand'],
        ];
    }

    protected function buildWarehouseChart(?User $actor): array
    {
        $warehouseQuery = WareHouse::query()->when($actor, fn ($query) => $query->accessibleBy($actor));

        $manualOrgCount = $this->manualOrganizationCount($actor);

        return [
            'series' => [
                (int) (clone $warehouseQuery)->count(),
                (int) (clone $warehouseQuery)->distinct('branch')->count('branch'),
                (int) ($manualOrgCount ?? 0),
            ],
            'labels' => ['Warehouses', 'Stores', 'Manual Tracking Organizations'],
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
            return $query->where('region_id', $actor->region_id);
        }

        if ($level === User::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
            return $query->where('region_id', $actor->region_id)->where('zone_id', $actor->zone_id);
        }

        if ($level === User::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
            return $query
                ->where('region_id', $actor->region_id)
                ->where('zone_id', $actor->zone_id)
                ->where('woreda_id', $actor->woreda_id);
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
            return $query->where(function (Builder $scope) use ($actor): void {
                $scope->where('organization_id', $actor->organization_id)
                    ->orWhere('acted_by', $actor->id);
            });
        }

        return $query->where('acted_by', $actor->id);
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
