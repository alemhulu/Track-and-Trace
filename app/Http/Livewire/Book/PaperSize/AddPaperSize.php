<?php

namespace App\Http\Livewire\Book\PaperSize;

use App\Models\Book;
use App\Models\PaperSize;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddPaperSize extends Component
{
    public $paperSizes = [];
    public $editingPaperSizeId = null;
    public $name = '';
    public $code = '';
    public $width = '';
    public $height = '';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'min:2',
                'max:100',
                Rule::unique('paper_sizes', 'name')->ignore($this->editingPaperSizeId),
            ],
            'code' => 'nullable|max:50',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
        ];
    }

    public function mount()
    {
        $this->paperSizes = PaperSize::query()->orderBy('name')->get();
    }

    public function render()
    {
        $this->paperSizes = PaperSize::query()->orderBy('name')->get();

        return view('livewire.book.paper-size.add-paper-size');
    }

    public function addPaperSize()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'width' => $this->width,
            'height' => $this->height,
        ];

        if ($this->editingPaperSizeId) {
            $record = PaperSize::find($this->editingPaperSizeId);
            if (! $record) {
                return $this->alertError('Paper size not found');
            }

            $record->update($payload);
            $this->alertSuccess('Paper size updated successfully!');
        } else {
            PaperSize::create($payload);
            $this->alertSuccess('Paper size added successfully!');
        }

        $this->resetFields();
    }

    public function editPaperSize($id)
    {
        $record = PaperSize::find($id);
        if (! $record) {
            return $this->alertError('Paper size not found');
        }

        $this->editingPaperSizeId = $record->id;
        $this->name = $record->name;
        $this->code = $record->code;
        $this->width = $record->width;
        $this->height = $record->height;
    }

    public function deletePaperSize($id)
    {
        $record = PaperSize::find($id);
        if (! $record) {
            return $this->alertError('Paper size not found');
        }

        $values = array_values(array_filter([$record->code, $record->name], function ($value) {
            return $value !== null && $value !== '';
        }));
        if (! empty($values) && Book::whereIn('paper_size', $values)->exists()) {
            return $this->alertError('Paper size cannot be deleted, it has related books.');
        }

        $record->delete();
        $this->alertDelete('Paper size deleted successfully!');
    }

    public function clearid()
    {
        $this->resetFields();
    }

    protected function resetFields()
    {
        $this->editingPaperSizeId = null;
        $this->name = '';
        $this->code = '';
        $this->width = '';
        $this->height = '';
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
