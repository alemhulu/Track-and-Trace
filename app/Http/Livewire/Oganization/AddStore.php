<?php

namespace App\Http\Livewire\Oganization;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Organization;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lists are bounded: a national manager reaches 50,000+ organizations and as many warehouses,
 * and loading them all here exhausted PHP's memory. Organizations are searched, the store list
 * is paged, and the branch list is the chosen organization's.
 */
class AddStore extends Component
{
    use AuthorizesPermissions;
    use WithPagination;

    private const OPTION_LIMIT = 50;

    // Form fields
    public $organizaton_id = '', $store_id = '', $name = '', $description = '';
    public $organizationSearch = '';
    public $assigned_user_id = '';

    public function updatedOrganizationSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOrganizatonId(): void
    {
        $this->store_id = '';
    }

    // The organizations whose name matches the search, and the one already chosen
    protected function organizationOptions($actor)
    {
        $search = trim((string) $this->organizationSearch);

        return Organization::query()
            ->accessibleBy($actor)
            ->where(fn ($query) => $query
                ->when($search !== '', fn ($match) => $match->where('name', 'like', "%{$search}%"), fn ($match) => $match->whereRaw('1 = 0'))
                ->when($this->organizaton_id, fn ($chosen) => $chosen->orWhere('id', $this->organizaton_id)))
            ->orderBy('name')
            ->limit(self::OPTION_LIMIT + 1)
            ->get(['id', 'name']);
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
        $actor = Auth::user();
        $organizations = $actor ? $this->organizationOptions($actor) : collect();

        return view('livewire.oganization.add-store', [
            'organizations' => $organizations->take(self::OPTION_LIMIT),
            'moreOrganizations' => $organizations->count() > self::OPTION_LIMIT,
            'branches' => $actor && $this->organizaton_id
                ? WareHouse::query()->accessibleBy($actor)->where('organization_id', $this->organizaton_id)->orderBy('branch')->get(['id', 'branch'])
                : collect(),
            'stores' => $actor
                ? WareHouse::query()->accessibleBy($actor)->with(['organization:id,name,email,phone', 'user:id,name,email,phone'])->orderByDesc('id')->paginate(20)
                : collect(),
        ])->extends('main.organization.index');
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'warehouses.create',
            'actions' => [
                'addStore' => 'warehouses.create',
            ],
        ];
    }
}
