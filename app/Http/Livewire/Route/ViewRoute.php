<?php

namespace App\Http\Livewire\Route;

use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewRoute extends Component
{
    public $routeId;

    public function mount($id)
    {
        $this->routeId = (int) $id;
    }

    public function noop()
    {
        // Display-only form wrapper action.
    }

    public function render()
    {
        $routeQuery = DistributionRoute::query()->with(['fromWarehouse.organization', 'toWarehouse.organization']);
        $actor = Auth::user();
        if ($actor) {
            $routeQuery->accessibleBy($actor);
        }

        $routeRecord = $routeQuery->findOrFail($this->routeId);

        $stepUsages = DistributionStep::with('distribution')
            ->where('route_id', $this->routeId)
            ->orderBy('distribution_id')
            ->orderBy('step_order')
            ->get();

        return view('livewire.route.view-route', compact('routeRecord', 'stepUsages'))
            ->extends('main.route.index');
    }
}
