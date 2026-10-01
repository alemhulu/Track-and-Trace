<?php

namespace App\Http\Livewire\Book\PrintType;

use App\Models\Book;
use App\Models\PrintType;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddPrintType extends Component
{
    public $printTypes = [];
    public $editingPrintTypeId = null;
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
                Rule::unique('print_types', 'name')->ignore($this->editingPrintTypeId),
            ],
            'code' => 'nullable|max:50',
            'description' => 'nullable|max:255',
        ];
    }

    public function mount()
    {
        $this->printTypes = PrintType::query()->orderBy('name')->get();
    }

    public function render()
    {
        $this->printTypes = PrintType::query()->orderBy('name')->get();

        return view('livewire.book.print-type.add-print-type');
    }

    public function addPrintType()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];

        if ($this->editingPrintTypeId) {
            $record = PrintType::find($this->editingPrintTypeId);
            if (! $record) {
                return $this->alertError('Print type not found');
            }

            $record->update($payload);
            $this->alertSuccess('Print type updated successfully!');
        } else {
            PrintType::create($payload);
            $this->alertSuccess('Print type added successfully!');
        }

        $this->resetFields();
    }

    public function editPrintType($id)
    {
        $record = PrintType::find($id);
        if (! $record) {
            return $this->alertError('Print type not found');
        }

        $this->editingPrintTypeId = $record->id;
        $this->name = $record->name;
        $this->code = $record->code;
        $this->description = $record->description;
    }

    public function deletePrintType($id)
    {
        $record = PrintType::find($id);
        if (! $record) {
            return $this->alertError('Print type not found');
        }

        $values = array_values(array_filter([$record->code, $record->name], function ($value) {
            return $value !== null && $value !== '';
        }));
        if (! empty($values) && Book::whereIn('print_type', $values)->exists()) {
            return $this->alertError('Print type cannot be deleted, it has related books.');
        }

        $record->delete();
        $this->alertDelete('Print type deleted successfully!');
    }

    public function clearid()
    {
        $this->resetFields();
    }

    protected function resetFields()
    {
        $this->editingPrintTypeId = null;
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
