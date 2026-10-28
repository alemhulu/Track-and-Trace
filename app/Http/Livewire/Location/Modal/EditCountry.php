<?php

namespace App\Http\Livewire\Location\Modal;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;
use App\Models\Country;

class EditCountry extends Component
{
    use AuthorizesPermissions;

    public country $country;

    function rules(){
        return ['country.name'=>'required|unique:countries,name,'.$this->country->id];
    }

    protected $listeners=[
        'editCountry'
    ];

    public function editCountry(country $country){
        $this->country = $country;
    }

    public function render()
    {
        return view('livewire.location.modal.edit-country');
    }

    public function submit(){
        $this->validate();
        $this->country->save();
        $this->alertSuccess();
        $this->emit('updatedCountry');
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name.' Required!']
        );
    }

    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Country Updated Successfuly']
        );
    }

    public function clearid(){
        unset($this->country);
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'locations.countries.view',
            'actions' => [
                'editCountry' => 'locations.countries.edit',
                'submit' => 'locations.countries.edit',
            ],
        ];
    }
}
