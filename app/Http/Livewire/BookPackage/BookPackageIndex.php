<?php

namespace App\Http\Livewire\BookPackage;

use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookPackageIndex extends Component
{
    use WithPagination;
    //variables
    public $packages = [], $subjects = [];
    public $column, $recordes, $sortType;
    public $total, $sent, $received, $available = 0;

    // Search variables
    public $search = '';
    public $attributes;

    // Search function
    public function updatedSearch()
    {
        $this->column = 'order_organization_id';
        $this->sortType = 'asc';
        $this->resetPage();
    }
    public function mount()
    {
        $this->subjects = Package::with('subject', 'grade')->get()->groupBy('subject_id')->toArray();
        $this->recordes = 5;
        $this->column = '';

        $user = User::where('id', Auth::user()->id)->with('organization')->first();

        if (! $user || ! $user->organization) {
            $this->total = 0;
            $this->sent = 0;
            $this->received = 0;
            $this->available = 0;
            return;
        }

        if ($user->organization->id == 1) {
            $this->total = Package::sum('no_of_books');
            $this->sent = Package::sum('sent');
            $this->received = Package::sum('received');
            $this->available = $this->total + $this->received - $this->sent;
        } else {
            $this->total = Package::where('receiver_organization_id', $user->organization->id)->sum('no_of_books');
            $this->sent = Package::where('receiver_organization_id', $user->organization->id)->sum('sent');
            $this->received = Package::where('receiver_organization_id', $user->organization->id)->sum('received');
            $this->available = $this->total + $this->received - $this->sent;
        }
    }
    public function render()
    {

        return view('livewire.book-package.book-package-index')->extends('main.book-package.index');
    }
}
