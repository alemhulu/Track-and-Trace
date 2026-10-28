<?php

namespace App\Http\Controllers;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Package;
use App\Models\WareHouse;
use App\Models\User;
use Illuminate\Contracts\View\View;

class InventoryDashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 50;

    public function __invoke(): View
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        return view('dashboards.inventory', [
            'inventoryData' => [
                'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
                'digital' => [
                    'kpiCards' => $this->buildDigitalKpiCards($actor),
                    'stockChart' => $this->buildStockByWarehouseChart($actor),
                    'compositionChart' => $this->buildDigitalCompositionChart($actor),
                    'lowStockAlerts' => $this->buildDigitalLowStockAlerts($actor),
                ],
                'manual' => [
                    'kpiCards' => $this->buildManualKpiCards($actor),
                    'stockChart' => $this->buildStockByManualBookChart($actor),
                    'compositionChart' => $this->buildManualCompositionChart($actor),
                    'lowStockAlerts' => $this->buildManualLowStockAlerts($actor),
                ],
            ],
        ]);
    }

    protected function buildDigitalKpiCards(?User $actor): array
    {
        $totalDigitalStock = (int) Package::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->sum('balance');

        $warehouseCount = (int) WareHouse::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->count();

        // Books are identified by grade + subject (there is no title column)
        $totalBooks = Package::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->distinct()
            ->get(['grade_id', 'subject_id'])
            ->count();

        return [
            [
                'label' => 'Stock on Hand',
                'value' => $totalDigitalStock,
                'hint' => 'Copies held in warehouses',
                'style' => 'emerald',
            ],
            [
                'label' => 'Warehouses',
                'value' => $warehouseCount,
                'hint' => 'Registered storage locations',
                'style' => 'amber',
            ],
            [
                'label' => 'Titles',
                'value' => $totalBooks,
                'hint' => 'Grade and subject combinations tracked',
                'style' => 'indigo',
            ],
        ];
    }

    protected function buildManualKpiCards(?User $actor): array
    {
        $balances = fn () => ManualStockBalance::query()->accessibleBy($actor);

        return [
            [
                'label' => 'Student Books on Hand',
                'value' => (int) $balances()->where('edition', ManualBook::EDITION_STUDENT)->sum('on_hand'),
                'hint' => 'Held by locations in your area',
                'style' => 'emerald',
            ],
            [
                'label' => 'Teacher Guides on Hand',
                'value' => (int) $balances()->where('edition', ManualBook::EDITION_TEACHER)->sum('on_hand'),
                'hint' => 'Held by locations in your area',
                'style' => 'amber',
            ],
            [
                'label' => 'Locations Holding Stock',
                'value' => (int) $balances()->where('on_hand', '>', 0)->distinct()->count('organization_id'),
                'hint' => 'Warehouses, offices and schools',
                'style' => 'teal',
            ],
        ];
    }

    protected function buildStockByWarehouseChart(?User $actor): array
    {
        $data = WareHouse::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->with('organization:id,name')
            ->selectRaw('ware_houses.*, (SELECT SUM(balance) FROM packages WHERE packages.ware_house_id = ware_houses.id) as total_stock')
            ->orderByDesc('total_stock')
            ->limit(10)
            ->get();

        return [
            'categories' => $data->map(fn($warehouse) => $warehouse->organization->name ?? 'Branch ' . $warehouse->branch)->all(),
            'series' => [
                ['name' => 'Stock Level', 'data' => $data->pluck('total_stock')->map(fn($v) => (int)$v)->all()],
            ],
        ];
    }

    protected function buildStockByManualBookChart(?User $actor): array
    {
        $data = ManualStockBalance::query()
            ->accessibleBy($actor)
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_balances.manual_book_id')
            ->where('manual_stock_balances.edition', ManualBook::EDITION_STUDENT)
            ->selectRaw('manual_books.title, SUM(manual_stock_balances.on_hand) as stock')
            ->groupBy('manual_books.title')
            ->orderByDesc('stock')
            ->limit(10)
            ->get();

        return [
            'categories' => $data->pluck('title')->all(),
            'series' => [
                ['name' => 'Student Books', 'data' => $data->pluck('stock')->map(fn($v) => (int)$v)->all()],
            ],
        ];
    }

    protected function buildDigitalLowStockAlerts(?User $actor): array
    {
        // Rows at zero have forwarded everything they received; that is not low stock
        return Package::query()
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->with(['grade:id,name', 'subject', 'wareHouse.organization:id,name'])
            ->where('balance', '>', 0)
            ->where('balance', '<', self::LOW_STOCK_THRESHOLD)
            ->orderBy('balance', 'asc')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'name' => trim(implode(' · ', array_filter([
                    $p->grade ? 'Grade ' . $p->grade->name : null,
                    $p->subject?->name,
                ]))),
                'location' => $p->wareHouse?->organization?->name ?? ($p->wareHouse ? 'Branch ' . $p->wareHouse->branch : '—'),
                'balance' => $p->balance,
            ])
            ->all();
    }

    protected function buildManualLowStockAlerts(?User $actor): array
    {
        return ManualStockBalance::query()
            ->accessibleBy($actor)
            ->with(['book:id,title', 'organization:id,name'])
            ->where('edition', ManualBook::EDITION_STUDENT)
            ->where('on_hand', '>', 0)
            ->where('on_hand', '<', self::LOW_STOCK_THRESHOLD)
            ->orderBy('on_hand', 'asc')
            ->limit(5)
            ->get()
            ->map(fn($balance) => [
                'name' => $balance->book?->title ?? '—',
                'location' => $balance->organization?->name ?? '—',
                'balance' => $balance->on_hand,
            ])
            ->all();
    }

    protected function buildDigitalCompositionChart(?User $actor): array
    {
        $composition = Package::query()
            ->join('subjects', 'subjects.id', '=', 'packages.subject_id')
            ->selectRaw('subjects.name, SUM(packages.balance) as total')
            ->when($actor, fn($query) => $query->accessibleBy($actor))
            ->groupBy('subjects.name')
            ->get();

        return [
            'labels' => $composition->pluck('name')->all(),
            'series' => $composition->pluck('total')->map(fn($v) => (int)$v)->all(),
        ];
    }

    protected function buildManualCompositionChart(?User $actor): array
    {
        $composition = ManualStockBalance::query()
            ->accessibleBy($actor)
            ->join('manual_books', 'manual_books.id', '=', 'manual_stock_balances.manual_book_id')
            ->selectRaw("COALESCE(manual_books.subject_name, 'Unspecified') as name, SUM(manual_stock_balances.on_hand) as total")
            ->groupBy('name')
            ->get();

        return [
            'labels' => $composition->pluck('name')->all(),
            'series' => $composition->pluck('total')->map(fn($v) => (int)$v)->all(),
        ];
    }
}
