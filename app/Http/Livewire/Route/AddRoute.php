<?php

namespace App\Http\Livewire\Route;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\DistributionRoute;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddRoute extends Component
{
    use AuthorizesPermissions;

    public $editingRouteId = null;
    public $name;
    public $description;
    public $from_warehouse;
    public $to_warehouse;
    public $is_active = 1;
    // Warehouses are searched by organization name: there are tens of thousands, and listing them
    // all in both selects exhausted PHP's memory
    public $fromSearch = '';
    public $toSearch = '';

    private const OPTION_LIMIT = 50;

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
        $from = $this->warehouseOptions($this->fromSearch, $this->from_warehouse);
        $to = $this->warehouseOptions($this->toSearch, $this->to_warehouse);

        return view('livewire.route.add-route', [
            'fromWarehouses' => $from->take(self::OPTION_LIMIT),
            'toWarehouses' => $to->take(self::OPTION_LIMIT),
            'moreFrom' => $from->count() > self::OPTION_LIMIT,
            'moreTo' => $to->count() > self::OPTION_LIMIT,
        ])->extends('main.route.index');
    }

    // Warehouses whose organization's name matches the search, and the one already chosen
    private function warehouseOptions($search, $chosenId)
    {
        $actor = Auth::user();
        $search = trim((string) $search);

        return WareHouse::query()
            ->when($actor, fn ($query) => $query->accessibleBy($actor))
            ->where(fn ($query) => $query
                ->when($search !== '', fn ($match) => $match->whereHas('organization', fn ($organization) => $organization->where('name', 'like', "%{$search}%")), fn ($match) => $match->whereRaw('1 = 0'))
                ->when($chosenId, fn ($chosen) => $chosen->orWhere('ware_houses.id', $chosenId)))
            ->with('organization:id,name')
            ->orderBy('id')
            ->limit(self::OPTION_LIMIT + 1)
            ->get(['id', 'branch', 'organization_id']);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'routes.create|routes.edit',
            'actions' => [
                'addRoute' => fn () => $this->editingRouteId ? 'routes.edit' : 'routes.create',
                'loadRouteForEdit' => 'routes.edit',
            ],
        ];
    }
}
