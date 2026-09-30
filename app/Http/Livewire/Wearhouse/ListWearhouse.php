<?php

namespace App\Http\Livewire\Wearhouse;

use App\Models\Package;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListWearhouse extends Component
{
    use WithPagination;

    public $totalWarehouses = 0;
    public $totalStores = 0;
    public $totalBooksInStores = 0;

    public function mount()
    {
        $this->refreshStats();
    }

    protected function refreshStats()
    {
        $actor = Auth::user();
        if (! $actor) {
            $this->totalWarehouses = 0;
            $this->totalStores = 0;
            $this->totalBooksInStores = 0;
            return;
        }

        $warehouseQuery = WareHouse::query()->accessibleBy($actor);

        $this->totalWarehouses = (clone $warehouseQuery)->count();
        $this->totalStores = (clone $warehouseQuery)->distinct('branch')->count('branch');

        $this->totalBooksInStores = (int) Package::query()
            ->whereHas('warehouse', function ($query) use ($actor) {
                $query->accessibleBy($actor);
            })
            ->sum('balance');
    }

    public function render()
    {
        $this->refreshStats();

        $actor = Auth::user();
        if (! $actor) {
            return view('livewire.wearhouse.list-wearhouse', ['wearehouses' => WareHouse::query()->whereRaw('1 = 0')->paginate(10)]);
        }

        $wearehouses = WareHouse::query()
            ->accessibleBy($actor)
            ->with(['organization.organizationType', 'user'])
            ->withCount('packages')
            ->paginate(10);

        return view('livewire.wearhouse.list-wearhouse', ['wearehouses' => $wearehouses]);
    }

    public function viewWarehouse($id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->alertError('Authentication required');
        }

        $warehouse = WareHouse::query()->accessibleBy($actor)->find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found or outside your scope');
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Warehouse #' . $warehouse->id . ' selected.'
        ]);
    }

    public function editWarehouse($id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->alertError('Authentication required');
        }

        $warehouse = WareHouse::query()->accessibleBy($actor)->find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found or outside your scope');
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Edit flow for warehouse #' . $warehouse->id . ' is ready for wiring.'
        ]);
    }

    public function deleteId($id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->alertError('Authentication required');
        }

        $warehouse = WareHouse::query()->accessibleBy($actor)->find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found or outside your scope');
        }

        if ($warehouse->packages()->exists()) {
            return $this->alertError('Warehouse cannot be deleted, it has related packages.');
        }

        $warehouse->delete();
        $this->refreshStats();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Warehouse deleted successfully!'
        ]);
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $name
        ]);
    }
}
