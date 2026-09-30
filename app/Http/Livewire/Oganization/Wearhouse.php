<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
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
        $actor = Auth::user();
        if (! $actor) {
            $this->organizations = collect();
            $this->users = collect();
            $this->wearhouses = [];
            return;
        }

        $this->organizations = Organization::query()->accessibleBy($actor)->orderBy('name')->get();
        $this->users = User::query()->accessibleBy($actor)->orderBy('name')->get();
        $this->loadWarehouses();
    }

    protected function loadWarehouses()
    {
        $actor = Auth::user();
        if (! $actor) {
            $this->wearhouses = [];
            return;
        }

        $this->wearhouses = WareHouse::query()
            ->accessibleBy($actor)
            ->with(['organization', 'user'])
            ->orderByDesc('id')
            ->get();
    }

    public function addWarehouse()
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Authentication required.'
            ]);
        }

        $this->validate();

        $organization = Organization::query()->accessibleBy($actor)->find($this->organizaton_id);
        if (! $organization) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Selected organization is outside your scope.'
            ]);
        }

        $assignedUser = User::query()->accessibleBy($actor)->find($this->user_id);
        if (! $assignedUser) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Selected user is outside your scope.'
            ]);
        }

        WareHouse::create([
            'branch' => $this->warehouse_id,
            'organization_id' => $organization->id,
            'assigned_user_id' => $assignedUser->id,
            'country_id' => $organization->country_id,
            'region_id' => $organization->region_id,
            'zone_id' => $organization->zone_id,
            'woreda_id' => $organization->woreda_id,
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
        $actor = Auth::user();
        if (! $actor) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Authentication required.'
            ]);
        }

        $warehouse = WareHouse::query()->accessibleBy($actor)->find($id);
        if (! $warehouse) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Warehouse not found or outside your scope.'
            ]);
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
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Authentication required.'
            ]);
        }

        $warehouse = WareHouse::query()->accessibleBy($actor)->find($id);
        if (! $warehouse) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Warehouse not found or outside your scope.'
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
