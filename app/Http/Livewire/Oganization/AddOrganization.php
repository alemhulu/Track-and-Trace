<?php

namespace App\Http\Livewire\Oganization;

use App\Models\Country;
use App\Models\Kebele;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Livewire\Component;

class AddOrganization extends Component
{
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
        'name' => 'required|min:2|max:50|unique:organization_types',
        'user_id' => 'required',
        'email' =>'nullable|email|unique:organizations'
    ];

    public function mount(){
        $this->countries = Country::all();
        $this->regions=Region::all();
    }

       // Reset Error 
       public function hydrate()
       {
           $this->resetErrorBag();
           $this->resetValidation();
       }

       public function resetFields()
       {
           $this->name="";
           $this->user_id="";
           $this->email="";
       }

    public function render()
    {
        $this->countries = Country::all();
        $this->regions = Region::where('country_id', $this->country_id)->get();
        $this->zones = Zone::where('region_id', $this->region_id)->get();
        $this->woredas = Woreda::where('zone_id', $this->zone_id)->get();
        $this->kebeles = Kebele::where('woreda_id', $this->woreda_id)->get();
        $this->users;
        $this->user_id;   
        return view('livewire.oganization.add-organization', ['users']);
    }
}
