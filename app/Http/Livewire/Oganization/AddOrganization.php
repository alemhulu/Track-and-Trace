<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Country;
use App\Models\Kebele;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Livewire\Component;
use Livewire\WithFileUploads;

class AddOrganization extends Component
{
    use WithFileUploads;
    // Search User To Select  START--------------------------------
    public $users='', $user='', $user_id;
    public $select, $selectList;
    public $hide=0;
    public $columns = [ 'id', 'name', 'phone', 'email' ];
    public $attributesList = [ 'user.id', 'user.name', 'user.email', 'user.phone' ];

    public function updatedSelectList(){
        $this->assignUser = User::search($this->attributesList,$this->selectList)->get();
    }

    public function setUserId($id)
    {
        $user=User::find($id);
        $this->select = $user->user_id;
        $this->user = $user;
        $this->hide=1;
        $this->user_id = $id;
        $this->assigned_user_id = $this->user_id;  
    }

    public function updatedSelect()
    {
        if ($this->select=="") {
            $this->users = [];
        } else {
            $this->users = User::search($this->columns, $this->select)->get();
        }
        $this->hide=0;
    }

    public function selectUser()
    {
        $user = User::find($this->user_id);
        if (!isset($user)) {
            $this->userName = "User Not Found";
        } else {
            $this->user = $user->first_name." ".$user->middle_name." ".$user->last_name;
        }
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
        'email' =>'nullable|email|unique:organizations',
        'website'=> 'nullable|url',
        'telephone' => 'nullable|numeric',
        'mobile' => 'required|numeric',
        'organization_type_id' => 'required|numeric',
        'assigned_user_id'=>'required',
        'country_id' =>'required',
        'region_id' =>'required',
        'zone_id' =>'nullable',
        'woreda_id' =>'nullable',
        'kebele_id' =>'nullable',
    ];

    public function mount(){
        $this->countries = Country::all();
        $this->regions = Region::all();
        $this->organizationTypes = OrganizationType::all();
    }

       public function resetFields()
       {
           $this->name="";
           $this->email="";
           $this->logo="";
           $this->website="";
           $this->telephone="";
           $this->mobile="";
           $this->organization_type_id="";
           $this->assigned_user_id="";
           $this->select="";
           $this->country_id = "";
           $this->region_id = "";
           $this->zone_id = "";
           $this->woreda_id = "";
           $this->kebele_id = "";
           $this->organizationTypes="";
       }

       // Reset Error 
       public function hydrate()
       {
           $this->resetErrorBag();
           $this->resetValidation();
       }

        public function render()
        {
            $this->organizationTypes = OrganizationType::all();
            $this->countries = Country::all();
            $this->regions = Region::where('country_id', $this->country_id)->get();
            $this->zones = Zone::where('region_id', $this->region_id)->get();
            $this->woredas = Woreda::where('zone_id', $this->zone_id)->get();
            $this->kebeles = Kebele::where('woreda_id', $this->woreda_id)->get();
            $this->users;
            $this->user_id;   
            return view('livewire.oganization.add-organization')->extends('main.organization.index');
        }

        public function addOrganization()
        {
            $this->validate();
            if($this->zone_id == ""){ $this->zone_id = null; }
            if($this->woreda_id == ""){ $this->woreda_id = null; }
            if($this->kebele_id == ""){ $this->kebele_id = null; }

            $validatedData = $this->validate($this->rules);

            if ($this->logo == "") {
                $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
                    return mb_substr($segment, 0, 1);
                })->join(' '));
        
                $imageName = "https://ui-avatars.com/api/?name=".urlencode($name)."&color=7F9CF5&background=EBF4FF";
                $validatedData['logo'] = $imageName;
            }
            else {
                $imageName = "storage/image/organization/logo/" . time(). '.' .$this->logo->extension();
                $logoName = time(). '.' .$this->logo->extension();
                $validatedData['logo'] = $imageName;
                $this->logo->storeAs('image/organization/logo/', $logoName, 'public');
            }
            
            // dd($validatedData);
            Organization::create($validatedData);
        
            // $this->resetPage();
            $this->alertSuccess();
            $this->resetFields();
        }

       // Alert Error Notification
       public function alertError($name)
       {
           $this->dispatchBrowserEvent(
               'alert', ['type' => 'error',  'message' => $name.' Required!']
           );
       }
   
       // Alert Success Notification
       public function alertSuccess()
       {
           $this->dispatchBrowserEvent(
               'alert', ['type' => 'success',  'message' => 'Organization Added Successfuly']
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
}
