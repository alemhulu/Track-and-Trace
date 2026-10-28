<?php

namespace App\Http\Livewire\ManualTracking\Oversight;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Organization;
use App\Models\School\Student;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\Oversight\VerificationService;
use App\Support\AcademicYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Visit form. Shelf counts are entered blind: the system figure is shown only afterwards.
 */
class RecordVisit extends Component
{
    use AuthorizesPermissions;

    public $schoolId;
    public $year;
    public $sample = [];
    public $shelf = [];
    public $seen = [];
    public $notes;

    public function mount($school, VerificationService $verification)
    {
        $model = $this->school($school);

        if ((int) Auth::user()->organization_id === (int) $model->id) {
            abort(403, 'A school cannot verify itself.');
        }

        $this->schoolId = $model->id;
        $this->year = AcademicYear::current();
        $this->sample = $verification->sampleStudents($model, $this->year)->all();
    }

    public function render(VerificationService $verification)
    {
        $school = $this->school($this->schoolId);

        return view('livewire.manual-tracking.oversight.record-visit', [
            'school' => $school,
            'titles' => $verification->titlesToCount($school),
            'students' => Student::query()
                ->whereKey($this->sample)
                ->with(['grade:id,name', 'heldBooks.book:id,title'])
                ->orderBy('grade_id')->orderBy('name')
                ->get(),
        ])->extends('main.manual-tracking.index');
    }

    public function save(VerificationService $verification)
    {
        $this->validate([
            'shelf.*' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:2000',
        ], [], ['shelf.*' => 'shelf count']);

        try {
            $visit = $verification->record(Auth::user(), $this->school($this->schoolId), (int) $this->year, $this->shelf, $this->checks(), $this->notes);
        } catch (ManualTrackingException $exception) {
            return $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        session()->flash('success', 'Visit recorded.');

        return redirect()->route('manual-tracking.visits.show', $visit->id);
    }

    // Every sampled student is checked; an unticked book means it could not be shown
    private function checks(): array
    {
        $checks = [];
        foreach ($this->sample as $studentId) {
            $checks[$studentId] = array_map(fn ($value) => (bool) $value, $this->seen[$studentId] ?? []);
        }

        return $checks;
    }

    private function school($id): Organization
    {
        return Organization::query()->accessibleBy(Auth::user())->findOrFail($id);
    }

    protected function permissionMap(): array
    {
        return ['view' => 'manual-tracking.visits.record'];
    }
}
