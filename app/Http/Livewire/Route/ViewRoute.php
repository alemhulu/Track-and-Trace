<?php

namespace App\Http\Livewire\Route;

use App\Models\DistributionRoute;
use App\Models\DistributionStep;
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
        $routeRecord = DistributionRoute::with(['fromWarehouse.organization', 'toWarehouse.organization'])
            ->findOrFail($this->routeId);

        $stepUsages = DistributionStep::with('distribution')
            ->where('route_id', $this->routeId)
            ->orderBy('distribution_id')
            ->orderBy('step_order')
            ->get();

        return view('livewire.route.view-route', compact('routeRecord', 'stepUsages'))
            ->extends('main.route.index');
    }
}
