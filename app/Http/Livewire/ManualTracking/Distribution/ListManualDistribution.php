<?php

namespace App\Http\Livewire\ManualTracking\Distribution;

use App\Models\ManualTracking\ManualDistribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class ListManualDistribution extends Component
{
    use WithPagination;

    public $recordes = 10;

    public function render()
    {
        $actor = Auth::user();
        if ($actor && Gate::denies('viewAny', ManualDistribution::class)) {
            abort(403);
        }

        $distributions = ManualDistribution::query()
            ->withCount('lines')
            ->withSum('lines', 'quantity')
            ->accessibleBy($actor)
            ->orderByDesc('id')
            ->paginate($this->recordes);

        return view('livewire.manual-tracking.distribution.list-manual-distribution', [
            'distributions' => $distributions,
        ])->extends('main.manual-tracking.index');
    }

    public function deleteDistribution($id)
    {
        $distribution = ManualDistribution::query()->withCount('lines')->find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found.');
        }

        if (Gate::denies('delete', $distribution)) {
            return $this->alertError('You are not authorized to delete this distribution.');
        }

        if ($distribution->lines_count > 0) {
            return $this->alertError('Distribution has line items and cannot be deleted.');
        }

        $distribution->delete();
        $this->alertSuccess('Distribution deleted successfully.');
    }

    public function deleteId($id)
    {
        return $this->deleteDistribution($id);
    }

    private function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    private function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
