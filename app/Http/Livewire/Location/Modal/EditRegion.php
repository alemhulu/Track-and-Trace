<?php

namespace App\Http\Livewire\Location\Modal;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use App\Models\Region;
use App\Models\Country;
use Livewire\Component;

class EditRegion extends Component
{
    use AuthorizesPermissions;

    public $countries;
    public region $region;

    function rules(){
        return  [
             'region.name'=>'required|unique:regions,name,'.$this->region->id,
             'region.is_city'=>'required',
             'region.country_id'=>'required'
         ];
     }

     protected $listeners=[
        'editRegion'
    ];

    public function editRegion(region $region){
        $this->region=$region;
    }
     
    public function mount(region $region)
    {
        $this->region=$region;
        $this->countries = country::all();
    }

    public function clearid(){
        unset($this->region->name);
        unset($this->region->is_city);
    }

    public function submit(){
        $this->validate();
        $this->region->save();
        $this->alertSuccess();
        $this->emit('updatedRegion');
    }

    public function alertSuccess()
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'success',  'message' => 'Region Updated Successfully!']
        );
    }

    public function alertError($name)
    {
        $this->dispatchBrowserEvent(
            'alert',
            ['type' => 'error',  'message' => $name.' Required!']
        );
    }

    public function render()
    {
        return view('livewire.location.modal.edit-region');
    }

    protected function permissionMap(): array
    {
        return [
            'view' => 'locations.regions.view',
            'actions' => [
                'editRegion' => 'locations.regions.edit',
                'submit' => 'locations.regions.edit',
            ],
        ];
    }
}
