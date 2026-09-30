<?php

namespace App\Http\Livewire\Trace;

use App\Models\Distribution;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class TraceBookDistribution extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showDistribution($id)
    {
        $distribution = Distribution::find($id);
        if (! $distribution) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Distribution not found.'
            ]);
        }

        return redirect()->route('distribution-details.show', $distribution);
    }

    public function render()
    {
        $query = Distribution::query()
            ->withCount('steps')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest();

        // Some environments keep these counters in tracks, others do not.
        if (Schema::hasTable('tracks')) {
            if (Schema::hasColumn('tracks', 'no_of_packages')) {
                $query->withSum('tracks as tracked_packages_total', 'no_of_packages');
            }

            if (Schema::hasColumn('tracks', 'no_of_books')) {
                $query->withSum('tracks as tracked_books_total', 'no_of_books');
            }
        }

        $distributions = $query->paginate(10);

        return view('livewire.trace.trace-book-distribution', compact('distributions'));
    }
}
