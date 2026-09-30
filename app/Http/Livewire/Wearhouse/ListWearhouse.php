<?php

namespace App\Http\Livewire\Wearhouse;

use App\Models\Package;
use App\Models\WareHouse;
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
        $this->totalWarehouses = WareHouse::count();
        $this->totalStores = WareHouse::distinct('branch')->count('branch');
        $this->totalBooksInStores = (int) Package::sum('balance');
    }

    public function render()
    {
        $this->refreshStats();

        $wearehouses = WareHouse::with(['organization.organizationType', 'user'])
            ->withCount('packages')
            ->paginate(10);

        return view('livewire.wearhouse.list-wearhouse', ['wearehouses' => $wearehouses]);
    }

    public function viewWarehouse($id)
    {
        $warehouse = WareHouse::find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found');
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Warehouse #' . $warehouse->id . ' selected.'
        ]);
    }

    public function editWarehouse($id)
    {
        $warehouse = WareHouse::find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found');
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Edit flow for warehouse #' . $warehouse->id . ' is ready for wiring.'
        ]);
    }

    public function deleteId($id)
    {
        $warehouse = WareHouse::find($id);
        if (! $warehouse) {
            return $this->alertError('Warehouse not found');
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
