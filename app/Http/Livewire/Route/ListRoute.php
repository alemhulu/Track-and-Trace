<?php

namespace App\Http\Livewire\Route;

use App\Models\DistributionStep;
use App\Models\DistributionRoute;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListRoute extends Component
{
    use WithPagination;

    protected $listeners = ['routeUpdated' => '$refresh'];

    public function viewRoute($id)
    {
        $actor = Auth::user();

        $routeQuery = DistributionRoute::query();
        if ($actor) {
            $routeQuery->accessibleBy($actor);
        }

        $route = $routeQuery->find($id);
        if (! $route) {
            return $this->alertError('Route not found or outside your scope');
        }

        return redirect()->route('route.view', ['id' => $route->id]);
    }

    public function editRoute($id)
    {
        $actor = Auth::user();

        $routeQuery = DistributionRoute::query();
        if ($actor) {
            $routeQuery->accessibleBy($actor);
        }

        $route = $routeQuery->find($id);
        if (! $route) {
            return $this->alertError('Route not found or outside your scope');
        }

        return redirect()->route('route.add', ['edit' => $route->id]);
    }

    public function deleteId($id)
    {
        $actor = Auth::user();

        $routeQuery = DistributionRoute::query();
        if ($actor) {
            $routeQuery->accessibleBy($actor);
        }

        $route = $routeQuery->find($id);
        if (! $route) {
            return $this->alertError('Route not found or outside your scope');
        }

        if (DistributionStep::where('route_id', $route->id)->exists()) {
            return $this->alertError('Route cannot be deleted because it is used in a distribution step.');
        }

        $route->delete();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Route deleted successfully!'
        ]);
    }

    protected function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    public function render()
    {
        $actor = Auth::user();

        $routesQuery = DistributionRoute::query();
        if ($actor) {
            $routesQuery->accessibleBy($actor);
        }

        $routes = $routesQuery->with(['fromWarehouse.organization', 'toWarehouse.organization'])
            ->latest()
            ->paginate(10);

        return view('livewire.route.list-route', compact('routes'))->extends('main.route.index');
    }
}
