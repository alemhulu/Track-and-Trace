<?php

namespace App\Http\Livewire\Route;

use App\Models\DistributionRoute;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddRoute extends Component
{
    public $editingRouteId = null;
    public $name;
    public $description;
    public $from_warehouse;
    public $to_warehouse;
    public $is_active = 1;
    public $warehouses = [];

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('distribution_routes', 'name')->ignore($this->editingRouteId),
            ],
            'from_warehouse' => 'required|exists:ware_houses,id',
            'to_warehouse' => 'required|exists:ware_houses,id|different:from_warehouse',
            'description' => 'nullable|max:500',
            'is_active' => 'required|boolean',
        ];
    }

    public function mount()
    {
        $actor = Auth::user();

        $warehouseQuery = WareHouse::query()->with('organization')->orderBy('id');
        if ($actor) {
            $warehouseQuery->accessibleBy($actor);
        }

        $this->warehouses = $warehouseQuery->get();

        $editId = request()->query('edit');
        if ($editId) {
            $this->loadRouteForEdit($editId);
        }
    }

    public function loadRouteForEdit($id)
    {
        $actor = Auth::user();

        $routeQuery = DistributionRoute::query();
        if ($actor) {
            $routeQuery->accessibleBy($actor);
        }

        $route = $routeQuery->find($id);
        if (! $route) {
            return;
        }

        $this->editingRouteId = $route->id;
        $this->name = $route->name;
        $this->description = $route->description;
        $this->from_warehouse = $route->from_ware_house_id;
        $this->to_warehouse = $route->to_ware_house_id;
        $this->is_active = (int) $route->is_active;
    }

    public function addRoute()
    {
        $actor = Auth::user();

        $this->validate();

        $fromWarehouseQuery = WareHouse::query();
        $toWarehouseQuery = WareHouse::query();
        if ($actor) {
            $fromWarehouseQuery->accessibleBy($actor);
            $toWarehouseQuery->accessibleBy($actor);
        }

        $fromWarehouse = $fromWarehouseQuery->find($this->from_warehouse);
        $toWarehouse = $toWarehouseQuery->find($this->to_warehouse);

        if (! $fromWarehouse || ! $toWarehouse) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Selected warehouse is outside your scope.'
            ]);
        }

        $payload = [
            'name' => $this->name,
            'description' => $this->description,
            'from_ware_house_id' => $fromWarehouse->id,
            'to_ware_house_id' => $toWarehouse->id,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->editingRouteId) {
            $routeQuery = DistributionRoute::query();
            if ($actor) {
                $routeQuery->accessibleBy($actor);
            }

            $route = $routeQuery->find($this->editingRouteId);
            if (! $route) {
                return $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Route not found for update.'
                ]);
            }

            $route->update($payload);
            $this->alertSuccess('Route updated successfully!');
        } else {
            DistributionRoute::create($payload);
            $this->alertSuccess('Route created successfully!');
        }

        $this->resetFields();
        $this->emit('routeUpdated');
    }

    public function cancelEdit()
    {
        $this->resetFields();
        return redirect()->route('route.add');
    }

    protected function resetFields()
    {
        $this->editingRouteId = null;
        $this->name = '';
        $this->description = '';
        $this->from_warehouse = '';
        $this->to_warehouse = '';
        $this->is_active = 1;
    }

    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function alertSuccess($message = 'Route created successfully!')
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.route.add-route')->extends('main.route.index');
    }
}
