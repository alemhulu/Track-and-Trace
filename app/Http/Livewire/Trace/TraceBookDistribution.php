<?php

namespace App\Http\Livewire\Trace;

use Livewire\Component;

class TraceBookDistribution extends Component
{
   
    public function showDistribution($id)
    {
        dd($id);
        return redirect()->to('destribution-details.show',$id);
    }

    public function render()
    {
        return view('livewire.trace.trace-book-distribution');
    }
}
