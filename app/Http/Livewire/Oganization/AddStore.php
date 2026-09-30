<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Livewire\Component;

class AddStore extends Component
{
    // Form fields
    public $organizaton_id = '', $store_id = '', $name = '', $description = '';
    public $organizations = [];
    public $stores = [], $assigned_user_id = '';

    public function mount()
    {
        $this->organizations = Organization::query()->orderBy('name')->get();
        $this->loadStores();
    }

    protected function loadStores()
    {
        $this->stores = WareHouse::with(['organization', 'user'])->orderByDesc('id')->get();
    }

    // Search User To Select  START--------------------------------
    public $users = [], $user = null, $user_id, $userName = '';
    public $select, $selectList;
    public $hide = 0;
    public $columns = ['id', 'name', 'phone', 'email'];
    public $attributesList = ['id', 'name', 'email', 'phone'];

    public function updatedSelectList()
    {
        $this->users = $this->searchUsers($this->selectList);
    }

    public function setUserId($id)
    {
        $user = User::find($id);

        if (! $user) {
            $this->user = null;
            $this->user_id = null;
            return;
        }

        $this->select = $user->name;
        $this->user = $user;
        $this->hide = 1;
        $this->user_id = $user->id;
    }

    public function updatedSelect()
    {
        if (blank($this->select)) {
            $this->users = [];
        } else {
            $this->users = $this->searchUsers($this->select);
        }
        $this->hide = 0;
    }

    public function selectUser()
    {
        $user = User::find($this->user_id);

        if (! $user) {
            $this->user = null;
            $this->userName = 'User Not Found';
            return;
        }

        $this->user = $user->name;
    }

    protected function searchUsers($query)
    {
        if (blank($query)) {
            return [];
        }

        return User::query()
            ->where(function ($searchQuery) use ($query) {
                $searchQuery->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('id', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
    }
    // Search User To Select END ---------------------------

    // Validation rules
    protected $rules = [
        'organizaton_id' => 'required|exists:organizations,id',
        'user_id' => 'required|exists:users,id',
        'store_id' => 'nullable|integer|min:1',
    ];

    // Reset Error
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function addStore()
    {
        $this->validate();

        WareHouse::create([
            'branch' => $this->store_id ?: 1,
            'organization_id' => $this->organizaton_id,
            'assigned_user_id' => $this->user_id,
        ]);

        $this->loadStores();
        $this->resetFields();
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Store Added Successfully!'
        ]);
    }

    protected function resetFields()
    {
        $this->organizaton_id = '';
        $this->store_id = '';
        $this->name = '';
        $this->description = '';
        $this->user_id = null;
        $this->select = '';
        $this->user = null;
        $this->assigned_user_id = '';
    }

    public function render()
    {
        $this->loadStores();
        return view('livewire.oganization.add-store')->extends('main.organization.index');
    }
}
