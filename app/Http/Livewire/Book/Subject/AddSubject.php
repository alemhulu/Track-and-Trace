<?php

namespace App\Http\Livewire\Book\Subject;

use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AddSubject extends Component
{
    use WithPagination;
    // Basic Profile variables
    public $subjects;
    public $code;
    public $name;
    public $description;
    public $user_id;

    // Table variables
    public $recordes=5;
    public $column='name';
    public $sortType='asc';

    // Search variables
    public $search='';
    public $attributes;

    // Search function 
    public function updatedSearch(){
        $this->column='name';
        $this->sortType='asc';
        $this->resetPage();
    }

    // Reset pagination on every variable updated
    public function updated(){
        $this->resetPage();
    }

    // sort function
    public function sort($value){
    if($this->column==$value && $this->sortType=='asc'){
        $this->sortType='desc';
    }else{
        $this->column=$value;
        $this->sortType='asc';
    }
    $this->resetPage();
    }

     // Validation rules
     protected $rules = [
        'name' => 'required|min:2|max:50|unique:subjects',
        'code' => 'nullable',
        'description' =>'nullable',
        'user_id'=> 'nullable|numeric',
    ];

    public function resetFields()
    {
        $this->name="";
        $this->code="";
        $this->description="";
        $this->user_id="";
    }

    // Reset Error 
    public function hydrate()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function mount()
    {
        $this->subjects = array();
    }

    public function render()
    {
        $this->subjects = Subject::all();
        return view('livewire.book.subject.add-subject', 
        ['subjects'=>Subject::when($this->column,function($q,$column){
            return $q->orderBy($this->column,$this->sortType);
        })->paginate($this->recordes)
]);
    }

    public function addSubject()
    {
        $this->validate();
        $this->user_id = Auth::user()->id;
        $validatedData = $this->validate($this->rules);

        Subject::create($validatedData);
        
        // $this->resetPage();
        $this->alertSuccess();
        $this->resetFields();
    }

    public $deleteId ="";
    public function deleteId($id){
        $this->deleteId = $id;
    }
    // Delete the available data
    public function deleteSubject(Subject $subject){
        if($subject->grades->count()){
            return $this->deleteError('Subject cannot be deleted, it has related data!');
        }
        $subject->delete();
        $this->alertDelete();
        $this->resetPage();
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

          // Clear input variables 
    public function clearid(){
        $this->name="";
    }
}
