<?php

namespace App\Http\Livewire\Distribution;

use App\Models\Distribution;
use Livewire\Component;
use Livewire\WithPagination;

class ListDistribution extends Component
{
    use WithPagination;

    protected $listeners = ['distributionUpdated' => '$refresh'];

    public function viewDistribution($id)
    {
        $distribution = Distribution::find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found');
        }

        return redirect()->route('distribution-details.show', $distribution);
    }

    public function editDistribution($id)
    {
        $distribution = Distribution::find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found');
        }

        return redirect()->route('distribution.add', ['edit' => $distribution->id]);
    }

    public function deleteId($id)
    {
        $distribution = Distribution::find($id);
        if (! $distribution) {
            return $this->alertError('Distribution not found');
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
        $distributions = Distribution::withCount('steps')->latest()->paginate(10);

        return view('livewire.distribution.list-distribution', compact('distributions'))
            ->extends('main.distribution.index');
    }
}
