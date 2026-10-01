<?php

namespace App\Http\Livewire\Book\BookType;

use App\Models\Book;
use App\Models\BookType;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddBookType extends Component
{
    public $bookTypes = [];
    public $editingBookTypeId = null;
    public $name = '';
    public $code = '';
    public $description = '';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:2',
                'max:100',
                Rule::unique('book_types', 'name')->ignore($this->editingBookTypeId),
            ],
            'code' => 'nullable|max:50',
            'description' => 'nullable|max:255',
        ];
    }

    public function mount()
    {
        $this->bookTypes = BookType::query()->orderBy('name')->get();
    }

    public function render()
    {
        $this->bookTypes = BookType::query()->orderBy('name')->get();

        return view('livewire.book.book-type.add-book-type');
    }

    public function addBookType()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];

        if ($this->editingBookTypeId) {
            $record = BookType::find($this->editingBookTypeId);
            if (! $record) {
                return $this->alertError('Book type not found');
            }

            $record->update($payload);
            $this->alertSuccess('Book type updated successfully!');
        } else {
            BookType::create($payload);
            $this->alertSuccess('Book type added successfully!');
        }

        $this->resetFields();
    }

    public function editBookType($id)
    {
        $record = BookType::find($id);
        if (! $record) {
            return $this->alertError('Book type not found');
        }

        $this->editingBookTypeId = $record->id;
        $this->name = $record->name;
        $this->code = $record->code;
        $this->description = $record->description;
    }

    public function deleteBookType($id)
    {
        $record = BookType::find($id);
        if (! $record) {
            return $this->alertError('Book type not found');
        }

        $values = array_values(array_filter([$record->code, $record->name], function ($value) {
            return $value !== null && $value !== '';
        }));
        if (! empty($values) && Book::whereIn('book_type', $values)->exists()) {
            return $this->alertError('Book type cannot be deleted, it has related books.');
        }

        $record->delete();
        $this->alertDelete('Book type deleted successfully!');
    }

    public function clearid()
    {
        $this->resetFields();
    }

    protected function resetFields()
    {
        $this->editingBookTypeId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
    }

    public function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    public function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function alertDelete($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
