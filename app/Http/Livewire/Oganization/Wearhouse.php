<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Livewire\Component;

class Wearhouse extends Component
{
    // Form fields
    public $organizaton_id = '', $warehouse_id = '', $name = '', $description = '';
    public $user_id = '', $wearhouses = [];
    public $organizations = [], $users = [];

    protected $rules = [
        'organizaton_id' => 'required|exists:organizations,id',
        'warehouse_id' => 'required|integer|min:1',
        'user_id' => 'required|exists:users,id',
    ];

    public function mount()
    {
        $this->organizations = Organization::query()->orderBy('name')->get();
        $this->users = User::query()->orderBy('name')->get();
        $this->loadWarehouses();
    }

    protected function loadWarehouses()
    {
        $this->wearhouses = WareHouse::with(['organization', 'user'])->orderByDesc('id')->get();
    }

    public function addWarehouse()
    {
        $this->validate();

        WareHouse::create([
            'branch' => $this->warehouse_id,
            'organization_id' => $this->organizaton_id,
            'assigned_user_id' => $this->user_id,
        ]);

        $this->loadWarehouses();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Warehouse Added Successfully!'
        ]);
        $this->resetFields();
    }

    public function editWearhouse($id)
    {
        $warehouse = WareHouse::find($id);
        if (! $warehouse) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Warehouse not found.'
            ]);
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
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Warehouse not found.'
            ]);
        }

        if ($warehouse->packages()->exists()) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Warehouse cannot be deleted, it has related packages.'
            ]);
        }

        $warehouse->delete();
        $this->loadWarehouses();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Warehouse deleted successfully!'
        ]);
    }

    protected function resetFields()
    {
        $this->organizaton_id = '';
        $this->warehouse_id = '';
        $this->name = '';
        $this->description = '';
        $this->user_id = '';
    }

    public function render()
    {
        $this->loadWarehouses();
        return view('livewire.oganization.wearhouse')->extends('main.organization.index');
    }
}
