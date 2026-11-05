<?php

namespace App\Http\Livewire\AcademicYear;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\AcademicYear;
use App\Support\AcademicYear as AcademicYearCalendar;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Admins add academic years, set their dates and pick the current one, which every
 * screen that filters by year opens on.
 */
class AcademicYearIndex extends Component
{
    use AuthorizesPermissions;

    public ?int $editingId = null;
    public $year = '';
    public $starts_on = '';
    public $ends_on = '';
    public $notes = '';
    public bool $make_current = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    // Typing a year fills in its usual Meskerem-to-Nehase dates
    public function updatedYear($value): void
    {
        if (preg_match('/^\d{4}$/', (string) $value)) {
            [$this->starts_on, $this->ends_on] = AcademicYearCalendar::defaultDates((int) $value);
        }
    }

    public function edit(int $id): void
    {
        $year = AcademicYear::query()->findOrFail($id);
        $this->resetValidation();
        $this->editingId = $year->id;
        $this->year = $year->year;
        $this->starts_on = $year->starts_on->toDateString();
        $this->ends_on = $year->ends_on->toDateString();
        $this->notes = (string) $year->notes;
        $this->make_current = $year->is_current;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $this->authorizePermission($this->editingId ? 'academic-years.edit' : 'academic-years.create');

        $data = $this->validate([
            'year' => ['required', 'integer', 'between:1990,2100', Rule::unique('academic_years', 'year')->ignore($this->editingId)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [], ['year' => 'academic year', 'starts_on' => 'start date', 'ends_on' => 'end date']);

        $overlap = AcademicYear::query()
            ->when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))
            ->where('starts_on', '<=', $data['ends_on'])
            ->where('ends_on', '>=', $data['starts_on'])
            ->first();
        if ($overlap) {
            $this->addError('starts_on', "These dates overlap {$overlap->label}.");

            return;
        }

        $academicYear = $this->editingId
            ? tap(AcademicYear::query()->findOrFail($this->editingId))->update($data + ['notes' => $data['notes'] ?: null])
            : AcademicYear::query()->create($data + ['notes' => $data['notes'] ?: null]);

        // The first year added is current by default, so screens always have one to open on
        if ($this->make_current || ! AcademicYear::query()->where('is_current', true)->exists()) {
            $academicYear->makeCurrent();
        }

        $this->alert('success', ($this->editingId ? 'Updated ' : 'Added ').$academicYear->label.'.');
        $this->resetForm();
    }

    public function makeCurrent(int $id): void
    {
        $year = AcademicYear::query()->findOrFail($id);
        $year->makeCurrent();
        $this->alert('success', "{$year->label} is now the current academic year.");
    }

    public function delete(int $id): void
    {
        $year = AcademicYear::query()->findOrFail($id);

        if ($year->is_current) {
            $this->alert('error', 'Make another year current before deleting this one.');

            return;
        }

        if ($usage = $year->usage()) {
            $this->alert('error', "{$year->label} cannot be deleted: it has ".collect($usage)
                ->map(fn ($count, $label) => number_format($count).' '.$label)->join(', ', ' and ').'.');

            return;
        }

        $year->delete();
        if ($this->editingId === $id) {
            $this->resetForm();
        }
        $this->alert('success', "Deleted {$year->label}.");
    }

    public function render()
    {
        $years = AcademicYear::query()->orderByDesc('year')->get();
        $today = Carbon::today();

        return view('livewire.academic-year.academic-year-index', [
            'years' => $years,
            'current' => $years->firstWhere('is_current', true),
            'calendarYear' => AcademicYearCalendar::calendarYear(),
            'today' => $today,
        ])->extends('main.academic-year.index');
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->notes = '';
        $this->make_current = false;

        // Suggest the year after the newest one on record
        $next = (AcademicYear::query()->max('year') ?: AcademicYearCalendar::calendarYear() - 1) + 1;
        $this->year = $next;
        [$this->starts_on, $this->ends_on] = AcademicYearCalendar::defaultDates($next);
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'academic-years.view',
            'actions' => [
                'edit' => 'academic-years.edit',
                'makeCurrent' => 'academic-years.edit',
                'delete' => 'academic-years.delete',
            ],
        ];
    }
}
