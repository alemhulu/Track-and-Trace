<?php

namespace App\Http\Livewire\Distribution;

use App\Models\Distribution;
use App\Models\DistributionRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddDistribution extends Component
{
    public $editingDistributionId = null;
    public $steps = [];
    public $routes = [];
    public $name = '';
    public $description = '';
    public $is_active = 1;

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:180',
                Rule::unique('distributions', 'name')->ignore($this->editingDistributionId),
            ],
            'description' => 'nullable|max:500',
            'is_active' => 'required|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.route_id' => 'required|exists:distribution_routes,id|distinct',
        ];
    }

    public function mount()
    {
        $actor = Auth::user();
        $routesQuery = DistributionRoute::query()
            ->with(['fromWarehouse.organization', 'toWarehouse.organization'])
            ->where('is_active', true)
            ->orderBy('name');

        if ($actor) {
            $routesQuery->accessibleBy($actor);
        }

        $this->routes = $routesQuery->get();

        $editId = request()->query('edit');
        if ($editId) {
            $this->loadDistributionForEdit($editId);
        } else {
            $this->addStep();
        }
    }

    public function loadDistributionForEdit($id)
    {
        $actor = Auth::user();

        $distributionQuery = Distribution::with(['steps' => function ($query) {
            $query->orderBy('step_order');
        }]);

        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distribution = $distributionQuery->find($id);

        if (! $distribution) {
            $this->addStep();
            return;
        }

        $this->editingDistributionId = $distribution->id;
        $this->name = $distribution->name;
        $this->description = $distribution->description;
        $this->is_active = (int) $distribution->is_active;
        $this->steps = $distribution->steps
            ->map(function ($step) {
                return ['route_id' => $step->route_id];
            })
            ->toArray();

        if (count($this->steps) === 0) {
            $this->addStep();
        }
    }

    public function addStep()
    {
        $this->steps[] = [
            'route_id' => '',
        ];
    }

    public function deleteStep($index)
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);

        if (count($this->steps) === 0) {
            $this->addStep();
        }
    }

    public function save()
    {
        $actor = Auth::user();
        $validated = $this->validate();

        $routeIds = collect($validated['steps'])->pluck('route_id')->filter()->unique()->values();
        $routeScopeQuery = DistributionRoute::query()->whereIn('id', $routeIds);
        if ($actor) {
            $routeScopeQuery->accessibleBy($actor);
        }

        $allowedRouteIds = $routeScopeQuery->pluck('id');

        if ($allowedRouteIds->count() !== $routeIds->count()) {
            return $this->alertError('One or more selected routes are outside your scope.');
        }

        $editableDistribution = null;
        if ($this->editingDistributionId) {
            $distributionQuery = Distribution::query();
            if ($actor) {
                $distributionQuery->accessibleBy($actor);
            }

            $editableDistribution = $distributionQuery->find($this->editingDistributionId);
            if (! $editableDistribution) {
                return $this->alertError('Distribution not found for update.');
            }
        }

        DB::transaction(function () use ($validated, $editableDistribution) {
            if ($editableDistribution) {
                $distribution = $editableDistribution;
                $distribution->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => (bool) $validated['is_active'],
                ]);

                $distribution->steps()->delete();
            } else {
                $distribution = Distribution::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'is_active' => (bool) $validated['is_active'],
                ]);
            }

            foreach ($validated['steps'] as $index => $step) {
                $distribution->steps()->create([
                    'route_id' => $step['route_id'],
                    'step_order' => $index + 1,
                ]);
            }
        });

        if ($this->editingDistributionId) {
            $this->alertSuccess('Distribution updated successfully!');
        } else {
            $this->alertSuccess('Distribution created successfully!');
        }

        $this->resetFields();
        $this->emit('distributionUpdated');
    }

    public function cancelEdit()
    {
        $this->resetFields();
        return redirect()->route('distribution.add');
    }

    protected function resetFields()
    {
        $this->editingDistributionId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = 1;
        $this->steps = [];
        $this->addStep();
    }

    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.distribution.add-distribution')->extends('main.distribution.index');
    }

    public function alertSuccess($message = 'Distribution Created Successfully!')
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => $message]
        );
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name]
        );
    }
}
