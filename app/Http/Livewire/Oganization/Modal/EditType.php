<?php

namespace App\Http\Livewire\Oganization\Modal;

use App\Models\OrganizationType as ModelsOrganizationType;;
use Livewire\Component;
class EditType extends Component
{
    public $Name;
    public $Code;
    public $Description;
  

    public EditType $EditType;

    function rules(){
        return  [
            'name' => 'required|min:2|max:50|unique:organization_types',
            'code' =>'nullable|min:2|max:5|unique:organization_types'
        ];
    }

    protected $listeners=[
        'editType'
    ];

    public function editType(){
        $this->column='updated_at';
        $this->sortType='desc';
        $this->resetPage();   }

    public function mount()
        {
            // $this->organizationType=$organizationType;
            $this->types = OrganizationType::all();
        }
        
   
        public function clearid()
        {
            $this->name="";
            $this->code="";
            $this->description="";
    
        }

    public function submit(){
        $this->validate();
        $this->OrganizationType->save();
        $this->alertSuccess();
        $this->emit('updatedOrganizationType');
    }

    public function alertSuccess()
        {
            $this->dispatchBrowserEvent(
                'alert',
                ['type' => 'success',  'message' => 'OrganizationType Updated Successfully!']
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
        return view('livewire.oganization.modal.edit-type');
    }
}