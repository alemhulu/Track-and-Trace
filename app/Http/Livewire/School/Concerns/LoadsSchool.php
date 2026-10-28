<?php

namespace App\Http\Livewire\School\Concerns;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

/**
 * School tabs are separate components and can be called directly, so each one loads its
 * school through the user's area rather than trusting the id it was given.
 */
trait LoadsSchool
{
    public $schoolId;
    public $year;

    protected function school(): Organization
    {
        return Organization::query()->accessibleBy(Auth::user())->findOrFail($this->schoolId);
    }

    protected function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }
}
