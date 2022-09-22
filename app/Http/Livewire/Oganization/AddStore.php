<?php

namespace App\Http\Livewire\Oganization;

use App\Models\User;
use Livewire\Component;

class AddStore extends Component
{     
    public function mount()
    {
        $this->stores = [];
    }

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

    // Validation rules
    protected $rules = [
        'name' => 'required|min:2|max:50|unique:organization_types',
        'user_id' => 'required',
        'email' =>'nullable|email|unique:organizations'
    ];

    // Reset Error 
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $this->users;
        $this->user_id;   
        return view('livewire.oganization.add-store')->extends('main.organization.index');
    }
}
