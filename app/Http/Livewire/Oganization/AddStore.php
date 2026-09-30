<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddStore extends Component
{
    // Form fields
    public $organizaton_id = '', $store_id = '', $name = '', $description = '';
    public $organizations = [];
    public $stores = [], $assigned_user_id = '';

    public function mount()
    {
        $actor = Auth::user();
        if (! $actor) {
            $this->organizations = collect();
            $this->stores = [];
            return;
        }

        $this->organizations = Organization::query()->accessibleBy($actor)->orderBy('name')->get();
        $this->loadStores();
    }

    protected function loadStores()
    {
        $actor = Auth::user();
        if (! $actor) {
            $this->stores = [];
            return;
        }

        $this->stores = WareHouse::query()
            ->accessibleBy($actor)
            ->with(['organization', 'user'])
            ->orderByDesc('id')
            ->get();
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
        $actor = Auth::user();
        if (! $actor) {
            $this->user = null;
            $this->user_id = null;
            return;
        }

        $user = User::query()->accessibleBy($actor)->find($id);

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
        $actor = Auth::user();
        if (! $actor) {
            $this->user = null;
            $this->userName = 'Authentication required';
            return;
        }

        $user = User::query()->accessibleBy($actor)->find($this->user_id);

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

        $actor = Auth::user();
        if (! $actor) {
            return [];
        }

        return User::query()
            ->accessibleBy($actor)
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
        $actor = Auth::user();
        if (! $actor) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Authentication required.'
            ]);
        }

        $this->validate();

        $organization = Organization::query()->accessibleBy($actor)->find($this->organizaton_id);
        if (! $organization) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Selected organization is outside your scope.'
            ]);
        }

        $assignedUser = User::query()->accessibleBy($actor)->find($this->user_id);
        if (! $assignedUser) {
            return $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Selected user is outside your scope.'
            ]);
        }

        WareHouse::create([
            'branch' => $this->store_id ?: 1,
            'organization_id' => $organization->id,
            'assigned_user_id' => $assignedUser->id,
            'country_id' => $organization->country_id,
            'region_id' => $organization->region_id,
            'zone_id' => $organization->zone_id,
            'woreda_id' => $organization->woreda_id,
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
