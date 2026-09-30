<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ListOrganization extends Component
{
    use WithPagination;

    public $search = '';
    public $recordes = 5;
    public $column = 'created_at';
    public $sortType = 'asc';
    protected $columns = ['name'];

    //  Search function
    public function updatedSearch()
    {
        $this->column = 'name';
        $this->sortType = 'asc';
        $this->resetPage();
    }

    // sort function
    public function sort($value)
    {
        if ($this->column == $value && $this->sortType == 'asc') {
            $this->sortType = 'desc';
        } else {
            $this->column = $value;
            $this->sortType = 'asc';
        }
        $this->resetPage();
    }

    // Reset pagination on every variable updated
    public function updated()
    {
        $this->resetPage();
    }


    public function render()
    {
        $actor = Auth::user();

        if (! $actor) {
            return view('livewire.oganization.list-organization', [
                'organizations' => Organization::query()->whereRaw('1 = 0')->paginate($this->recordes),
            ])->extends('main.organization.index');
        }

        return view(
            'livewire.oganization.list-organization',
            [
                'organizations' => Organization::query()
                    ->with(['country', 'region', 'zone', 'woreda', 'contact', 'organizationType'])
                    ->accessibleBy($actor)
                    ->search($this->columns, $this->search)
                    ->when($this->column, function ($q, $column) {
                        return $q->orderBy($this->column, $this->sortType);
                    })->paginate($this->recordes)
            ]
        )->extends('main.organization.index');
    }

    public $deleteId = "";

    public function deleteId($id)
    {
        $this->deleteId = $id;
    }

    // Delete the available data
    public function deleteOrganization($id)
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->alertError('Authentication required.');
        }

        $organization = Organization::query()->accessibleBy($actor)->find($id);

        if (! $organization) {
            return $this->alertError('You are not allowed to delete this organization.');
        }

        if ($organization->users->count()) {
            return $this->deleteError('Organization cannot be deleted, it has related data!');
        }
        $organization->delete();
        $this->alertDelete();
        $this->resetPage();
    }

    // Clear input variables
    public function clearid()
    {
        $this->deleteId = "";
    }


    // Alert Error Notification
    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name . ' Required!']
        );
    }

    public function deleteError($message)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error', 'message' => $message]
        );
    }

    // Alert Success Notification
    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Organization Added Successfuly']
        );
    }

    // Alert Delete Notification
    public function alertDelete()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Organization Deleted Successfully!']
        );
    }

    // Reset Error
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
