<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Country;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\User;
use App\Models\WareHouse;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddOrganization extends Component
{
    use WithFileUploads;
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
            $this->assigned_user_id = null;
            return;
        }

        $this->select = $user->name;
        $this->user = $user;
        $this->hide = 1;
        $this->user_id = $user->id;
        $this->assigned_user_id = $user->id;
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
        $this->assigned_user_id = $user->id;
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

    // Basic Profile variables
    public $organizationTypes;
    public $logo;
    public $name;
    public $website;
    public $email;
    public $telephone;
    public $mobile;
    public $organization_type_id;
    public $assigned_user_id;

    //  Select Location Variables
    public $countries;
    public $country_id;
    public $regions;
    public $region_id;
    public $zones;
    public $zone_id;
    public $woredas;
    public $woreda_id;
    public $kebeles;
    public $kebele_id;

    // Validation rules
    protected $rules = [
        'name' => 'required|min:2|max:50|unique:organizations',
        'logo' => 'nullable|image|mimes:jpeg,png,svg,jpg,gif|max:1024',
        'email' => 'nullable|email|unique:organizations',
        'website' => 'nullable|url',
        'telephone' => 'nullable|numeric',
        'mobile' => 'required|numeric',
        'organization_type_id' => 'required|numeric',
        'assigned_user_id' => 'required',
        'country_id' => 'required',
        'region_id' => 'required',
        'zone_id' => 'nullable',
        'woreda_id' => 'nullable',
        'kebele_id' => 'nullable',
    ];

    public function mount()
    {
        $this->hydrateScopedLookups();
    }

    public function resetFields()
    {
        $this->name = "";
        $this->email = "";
        $this->logo = "";
        $this->website = "";
        $this->telephone = "";
        $this->mobile = "";
        $this->organization_type_id = "";
        $this->assigned_user_id = "";
        $this->select = "";
        $this->country_id = "";
        $this->region_id = "";
        $this->zone_id = "";
        $this->woreda_id = "";
        $this->kebele_id = "";
        $this->organizationTypes = "";
    }

    // Reset Error
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $this->hydrateScopedLookups();
        // $this->kebeles = Kebele::where('woreda_id', $this->woreda_id)->get();
        $this->users;
        $this->user_id;
        return view('livewire.oganization.add-organization')->extends('main.organization.index');
    }

    public function addOrganization()
    {
        $actor = Auth::user();
        if (! $actor) {
            return $this->alertError('Authentication required');
        }
        if (Gate::denies('create', Organization::class)) {
            abort(403, 'You are not authorized to create organizations.');
        }
        if (Gate::denies('create', WareHouse::class)) {
            abort(403, 'You are not authorized to create warehouses.');
        }

        if (! $actor->hasNationalAccess()) {
            if (! empty($actor->country_id) && (int) $this->country_id !== (int) $actor->country_id) {
                return $this->alertError('You can only create organization in your country');
            }

            if (! empty($actor->region_id) && (int) $this->region_id !== (int) $actor->region_id) {
                return $this->alertError('You can only create organization in your region');
            }

            if (! empty($actor->zone_id) && (int) $this->zone_id !== (int) $actor->zone_id) {
                return $this->alertError('You can only create organization in your zone');
            }

            if (! empty($actor->woreda_id) && (int) $this->woreda_id !== (int) $actor->woreda_id) {
                return $this->alertError('You can only create organization in your woreda');
            }
        }

        $assignableUser = User::query()->accessibleBy($actor)->find($this->assigned_user_id);
        if (! $assignableUser) {
            return $this->alertError('Assigned user is outside your scope');
        }

        if ($this->zone_id == "") {
            $this->zone_id = null;
        }
        if ($this->woreda_id == "") {
            $this->woreda_id = null;
        }
        if ($this->kebele_id == "") {
            $this->kebele_id = null;
        }

        $validatedData = $this->validate();

        if ($this->logo == "") {
            $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
                return mb_substr($segment, 0, 1);
            })->join(' '));

            $imageName = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&color=7F9CF5&background=EBF4FF";
            $validatedData['logo'] = $imageName;
        } else {
            $imageName = "storage/image/organization/logo/" . time() . '.' . $this->logo->extension();
            $logoName = time() . '.' . $this->logo->extension();
            $validatedData['logo'] = $imageName;
            $this->logo->storeAs('image/organization/logo/', $logoName, 'public');
        }

        // dd($validatedData);
        $organization = Organization::create($validatedData);
        WareHouse::create([
            "branch" => 1,
            "organization_id" => $organization->id,
            "country_id" => $organization->country_id,
            "region_id" => $organization->region_id,
            "zone_id" => $organization->zone_id,
            "woreda_id" => $organization->woreda_id,
        ]);

        // $this->resetPage();
        $this->alertSuccess();
        $this->resetFields();
    }

    // Alert Error Notification
    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name]
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
            ['type' => 'success',  'message' => 'OrganizationType Deleted Successfully!']
        );
    }

    private function hydrateScopedLookups(): void
    {
        $actor = Auth::user();

        if (! $actor) {
            $this->organizationTypes = collect();
            $this->countries = collect();
            $this->regions = collect();
            $this->zones = collect();
            $this->woredas = collect();
            return;
        }

        $countries = Country::query();
        $regions = Region::query();
        $zones = Zone::query();
        $woredas = Woreda::query();

        if (! $actor->hasNationalAccess()) {
            if (! empty($actor->country_id)) {
                $countries->where('id', $actor->country_id);
                $regions->where('country_id', $actor->country_id);
                $zones->where('country_id', $actor->country_id);
                $woredas->where('country_id', $actor->country_id);
            }

            if (! empty($actor->region_id)) {
                $regions->where('id', $actor->region_id);
                $zones->where('region_id', $actor->region_id);
                $woredas->where('region_id', $actor->region_id);
            }

            if (! empty($actor->zone_id)) {
                $zones->where('id', $actor->zone_id);
                $woredas->where('zone_id', $actor->zone_id);
            }

            if (! empty($actor->woreda_id)) {
                $woredas->where('id', $actor->woreda_id);
            }
        }

        $this->organizationTypes = OrganizationType::all();
        $this->countries = $countries->orderBy('name')->get();

        if (! empty($this->country_id)) {
            $regions->where('country_id', $this->country_id);
        }
        $this->regions = $regions->orderBy('name')->get();

        if (! empty($this->region_id)) {
            $zones->where('region_id', $this->region_id);
        }
        $this->zones = $zones->orderBy('name')->get();

        if (! empty($this->zone_id)) {
            $woredas->where('zone_id', $this->zone_id);
        }
        $this->woredas = $woredas->orderBy('name')->get();
    }
}
