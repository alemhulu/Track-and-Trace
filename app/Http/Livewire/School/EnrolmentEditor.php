<?php

namespace App\Http\Livewire\School;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Http\Livewire\School\Concerns\LoadsSchool;
use App\Models\Grade;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Services\ManualTracking\ManualTrackingException;
use App\Services\School\SchoolBookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EnrolmentEditor extends Component
{
    use LoadsSchool;
    use AuthorizesPermissions;

    // grade id => [male, female, sections]
    public $rows = [];

    public function mount()
    {
        $school = $this->school();
        $saved = SchoolEnrolment::query()
            ->where('organization_id', $school->id)
            ->where('academic_year', $this->year)
            ->get()
            ->keyBy('grade_id');

        foreach ($this->grades() as $grade) {
            $enrolment = $saved[$grade->id] ?? null;
            $this->rows[$grade->id] = [
                'male' => $enrolment?->male ?? '',
                'female' => $enrolment?->female ?? '',
                'sections' => $enrolment?->sections ?? '',
            ];
        }
    }

    public function render()
    {
        return view('livewire.school.enrolment-editor', [
            'grades' => $this->grades(),
            'registered' => Student::query()
                ->active()
                ->where('organization_id', $this->schoolId)
                ->where('academic_year', $this->year)
                ->selectRaw('grade_id, COUNT(*) as total')
                ->groupBy('grade_id')
                ->pluck('total', 'grade_id'),
        ]);
    }

    public function save(SchoolBookService $books)
    {
        $this->validate([
            'rows.*.male' => 'nullable|integer|min:0|max:5000',
            'rows.*.female' => 'nullable|integer|min:0|max:5000',
            'rows.*.sections' => 'nullable|integer|min:1|max:100',
        ], [], ['rows.*.male' => 'male', 'rows.*.female' => 'female', 'rows.*.sections' => 'sections']);

        $school = $this->school();

        try {
            $books->authorize(Auth::user(), $school);
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        DB::transaction(function () use ($school): void {
            foreach ($this->rows as $gradeId => $row) {
                $keys = ['organization_id' => $school->id, 'academic_year' => $this->year, 'grade_id' => $gradeId];
                $total = (int) $row['male'] + (int) $row['female'];

                if ($total === 0) {
                    SchoolEnrolment::query()->where($keys)->delete();
                    continue;
                }

                SchoolEnrolment::query()->updateOrCreate($keys, [
                    'male' => (int) $row['male'],
                    'female' => (int) $row['female'],
                    'sections' => $row['sections'] !== '' ? (int) $row['sections'] : null,
                ]);
            }
        });

        $this->emit('schoolRecordsChanged');
        $this->alert('success', 'Enrolment saved.');
    }

    private function grades()
    {
        return Grade::query()->orderByRaw('CAST(name AS UNSIGNED)')->get(['id', 'name']);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'manual-tracking.schools.view',
            'actions' => [
                'save' => 'manual-tracking.schools.enrol',
            ],
        ];
    }
}
