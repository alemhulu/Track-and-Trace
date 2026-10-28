<?php

namespace App\Http\Livewire\ManualTracking\Book;

use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\Region;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AddManualBook extends Component
{
    public $code;
    public $title;
    public $region_id;
    public $grade_id;
    public $subject_id;
    public $has_teacher_guide = true;
    public $students_per_package = 40;
    public $isbn;
    public $edition;
    public $notes;
    public $editingBookId;

    // Printed QR text and code per edition
    public $codes = [
        ManualBook::EDITION_STUDENT => ['payload' => '', 'print_code' => ''],
        ManualBook::EDITION_TEACHER => ['payload' => '', 'print_code' => ''],
    ];

    protected function rules()
    {
        return [
            'code' => 'nullable|string|max:255|unique:manual_books,code,' . $this->editingBookId,
            'title' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'has_teacher_guide' => 'boolean',
            'students_per_package' => 'required|integer|min:1|max:500',
            'isbn' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'codes.*.payload' => 'nullable|string|max:255',
            'codes.*.print_code' => 'nullable|string|max:255',
        ];
    }

    protected $validationAttributes = [
        'region_id' => 'region',
        'grade_id' => 'grade',
        'subject_id' => 'subject',
        'codes.student.payload' => 'student QR text',
        'codes.teacher.payload' => 'teacher guide QR text',
    ];

    public function mount()
    {
        $editId = request()->query('edit');
        if ($editId) {
            $this->loadForEdit((int) $editId);
        }
    }

    public function render()
    {
        return view('livewire.manual-tracking.book.add-manual-book', [
            'regions' => Region::query()->orderBy('name')->get(['id', 'name']),
            'grades' => Grade::query()->orderByRaw('CAST(name AS UNSIGNED)')->get(['id', 'name']),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'name']),
        ])->extends('main.manual-tracking.index');
    }

    // Suggest the title and QR text in the format printed on the books
    public function updated($field)
    {
        if (! in_array($field, ['region_id', 'grade_id', 'subject_id'], true) || $this->editingBookId) {
            return;
        }

        $region = Region::query()->find($this->region_id)?->name;
        $grade = Grade::query()->find($this->grade_id)?->name;
        $subject = Subject::query()->find($this->subject_id)?->name;

        if ($region && $grade && $subject) {
            $this->title = "{$region} Grade {$grade} {$subject}";
            $this->codes[ManualBook::EDITION_STUDENT]['payload'] = "{$this->title} Student textbook";
            $this->codes[ManualBook::EDITION_TEACHER]['payload'] = "{$this->title} Teacher guide";
        }
    }

    public function saveBook()
    {
        $data = $this->validate();
        $this->ensureCodesAreUnused();

        $grade = Grade::query()->find($data['grade_id']);
        $subject = Subject::query()->find($data['subject_id']);
        $attributes = collect($data)->except('codes')->all() + [
            'grade_name' => 'Grade ' . $grade->name,
            'subject_name' => $subject->name,
        ];

        DB::transaction(function () use ($attributes): void {
            $book = $this->editingBookId
                ? tap(ManualBook::query()->findOrFail($this->editingBookId))->update($attributes)
                : ManualBook::query()->create($attributes);

            foreach ($this->codes as $edition => $code) {
                $keep = trim((string) $code['payload']) !== ''
                    && ($edition === ManualBook::EDITION_STUDENT || $book->has_teacher_guide);

                if (! $keep) {
                    $book->codes()->where('edition', $edition)->delete();
                    continue;
                }

                $book->codes()->updateOrCreate(['edition' => $edition], [
                    'payload' => trim($code['payload']),
                    'print_code' => trim((string) $code['print_code']) ?: null,
                ]);
            }
        });

        if ($this->editingBookId) {
            $this->alertSuccess('Title updated successfully.');
            return redirect()->route('manual-tracking.books.list');
        }

        $this->resetForm();
        $this->alertSuccess('Title created successfully.');
    }

    private function ensureCodesAreUnused(): void
    {
        foreach ($this->codes as $edition => $code) {
            if (trim((string) $code['payload']) === '') {
                continue;
            }

            $taken = ManualBookCode::query()
                ->where('normalized_payload', ManualBookCode::normalize($code['payload']))
                ->where(fn ($query) => $query
                    ->where('manual_book_id', '!=', $this->editingBookId ?? 0)
                    ->orWhere('edition', '!=', $edition))
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    "codes.{$edition}.payload" => 'This QR text is already linked to another title or edition.',
                ]);
            }
        }
    }

    private function loadForEdit(int $id): void
    {
        $book = ManualBook::query()->with('codes')->find($id);
        if (! $book) {
            $this->alertError('Title not found.');
            return;
        }

        $this->editingBookId = $book->id;
        $this->fill($book->only([
            'code', 'title', 'region_id', 'grade_id', 'subject_id',
            'has_teacher_guide', 'students_per_package', 'isbn', 'edition', 'notes',
        ]));

        foreach ($book->codes as $code) {
            $this->codes[$code->edition] = ['payload' => $code->payload, 'print_code' => (string) $code->print_code];
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'code', 'title', 'region_id', 'grade_id', 'subject_id', 'has_teacher_guide',
            'students_per_package', 'isbn', 'edition', 'notes', 'editingBookId', 'codes',
        ]);
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
