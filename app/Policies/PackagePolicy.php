<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Package $package): bool
    {
        return Package::query()->accessibleBy($user)->whereKey($package->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Package $package): bool
    {
        return $this->view($user, $package);
    }

    public function delete(User $user, Package $package): bool
    {
        return $this->view($user, $package);
    }
}

