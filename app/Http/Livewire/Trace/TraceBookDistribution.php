<?php

namespace App\Http\Livewire\Trace;

use App\Models\Distribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class TraceBookDistribution extends Component
{
    use WithPagination;

    public $search = '';
    public $gradeId = null;
    public $subjectId = null;

    public function mount($gradeId = null, $subjectId = null)
    {
        $this->gradeId = $gradeId ?: null;
        $this->subjectId = $subjectId ?: null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedGradeId()
    {
        $this->resetPage();
    }

    public function updatedSubjectId()
    {
        $this->resetPage();
    }

    public function showDistribution($id)
    {
        $distributionQuery = Distribution::query();
        $actor = Auth::user();
        if ($actor) {
            $distributionQuery->accessibleBy($actor);
        }

        $distribution = $distributionQuery->find($id);
        if (! $distribution) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Distribution not found or outside your scope.'
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

        $actor = Auth::user();
        if ($actor) {
            $query->accessibleBy($actor);
        }

        $canFilterByPackage = Schema::hasTable('packages')
            && Schema::hasColumn('packages', 'grade_id')
            && Schema::hasColumn('packages', 'subject_id');

        if (($this->gradeId || $this->subjectId) && $canFilterByPackage) {
            $gradeId = $this->gradeId;
            $subjectId = $this->subjectId;

            $query->whereHas('steps.route', function ($routeQuery) use ($gradeId, $subjectId) {
                $routeQuery->where(function ($warehouseQuery) use ($gradeId, $subjectId) {
                    $warehouseQuery
                        ->whereHas('fromWarehouse.packages', function ($packageQuery) use ($gradeId, $subjectId) {
                            $packageQuery
                                ->when($gradeId, function ($gradeQuery) use ($gradeId) {
                                    $gradeQuery->where('grade_id', $gradeId);
                                })
                                ->when($subjectId, function ($subjectQuery) use ($subjectId) {
                                    $subjectQuery->where('subject_id', $subjectId);
                                });
                        })
                        ->orWhereHas('toWarehouse.packages', function ($packageQuery) use ($gradeId, $subjectId) {
                            $packageQuery
                                ->when($gradeId, function ($gradeQuery) use ($gradeId) {
                                    $gradeQuery->where('grade_id', $gradeId);
                                })
                                ->when($subjectId, function ($subjectQuery) use ($subjectId) {
                                    $subjectQuery->where('subject_id', $subjectId);
                                });
                        });
                });
            });
        }

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
