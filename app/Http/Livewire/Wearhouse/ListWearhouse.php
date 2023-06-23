<?php

namespace App\Http\Livewire\Wearhouse;

use App\Models\WareHouse;
use Livewire\Component;
use Livewire\WithPagination;

class ListWearhouse extends Component
{
    use WithPagination;
    public function render()
    {
        $wearehouses = WareHouse::paginate(10);
        return view('livewire.wearhouse.list-wearhouse' , ['wearehouses' => $wearehouses]);
    }
}
