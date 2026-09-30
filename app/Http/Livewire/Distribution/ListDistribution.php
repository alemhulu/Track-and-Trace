<?php

namespace App\Http\Livewire\Distribution;

use App\Models\Distribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ListDistribution extends Component
{
    use WithPagination;

    protected $listeners = ['distributionUpdated' => '$refresh'];

    public function viewDistribution($id)
    {
        $distributionQuery = Distribution::query();
        $actor = Auth::user();
        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distribution = $distributionQuery->find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found or outside your scope');
        }
        if ($actor && Gate::denies('view', $distribution)) {
            return $this->alertError('You are not authorized to view this distribution');
        }

        return redirect()->route('distribution-details.show', $distribution);
    }

    public function editDistribution($id)
    {
        $distributionQuery = Distribution::query();
        $actor = Auth::user();
        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distribution = $distributionQuery->find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found or outside your scope');
        }
        if ($actor && Gate::denies('update', $distribution)) {
            return $this->alertError('You are not authorized to edit this distribution');
        }

        return redirect()->route('distribution.add', ['edit' => $distribution->id]);
    }

    public function deleteId($id)
    {
        $distributionQuery = Distribution::query();
        $actor = Auth::user();
        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distribution = $distributionQuery->find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found or outside your scope');
        }
        if ($actor && Gate::denies('delete', $distribution)) {
            return $this->alertError('You are not authorized to delete this distribution');
        }

        if ($distribution->tracks()->exists()) {
            return $this->alertError('Distribution cannot be deleted, it has related tracks.');
        }

        $distribution->delete();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Distribution deleted successfully!'
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
        $distributionQuery = Distribution::query();
        $actor = Auth::user();
        if ($actor && Gate::denies('viewAny', Distribution::class)) {
            $distributionQuery->whereRaw('1 = 0');
        }
        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distributions = $distributionQuery
            ->withCount('steps')
            ->latest()
            ->paginate(10);

        return view('livewire.distribution.list-distribution', compact('distributions'))
            ->extends('main.distribution.index');
    }
}
