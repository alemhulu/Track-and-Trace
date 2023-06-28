<?php

namespace App\Http\Livewire\Print;

use App\Models\PrintOrder;
use Livewire\Component;

class ListPrintOrder extends Component
{
    public function render()
    {
        $printOrders = PrintOrder::get();
        return view('livewire.print.list-print-order', ['orders'=>$printOrders])->extends('main.print-order.index');
    }
}
