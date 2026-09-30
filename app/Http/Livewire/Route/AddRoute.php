<?php

namespace App\Http\Livewire\Route;

use App\Models\DistributionRoute;
use App\Models\WareHouse;
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
        $this->warehouses = WareHouse::with('organization')->orderBy('id')->get();

        $editId = request()->query('edit');
        if ($editId) {
            $this->loadRouteForEdit($editId);
        }
    }

    public function loadRouteForEdit($id)
    {
        $route = DistributionRoute::find($id);
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
        $this->validate();

        $payload = [
            'name' => $this->name,
            'description' => $this->description,
            'from_ware_house_id' => $this->from_warehouse,
            'to_ware_house_id' => $this->to_warehouse,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->editingRouteId) {
            $route = DistributionRoute::find($this->editingRouteId);
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
