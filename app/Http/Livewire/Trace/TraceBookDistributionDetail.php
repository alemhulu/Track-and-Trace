<?php

namespace App\Http\Livewire\Trace;

use App\Http\Livewire\Concerns\AuthorizesPermissions;
use Livewire\Component;

class TraceBookDistributionDetail extends Component
{
    use AuthorizesPermissions;

    public function render()
    {
        return view('livewire.trace.trace-book-distribution-detail');
    }

    protected function permissionMap(): array
    {
        return ['view' => 'trace.view'];
    }
}
