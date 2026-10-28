<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('packages.view');
    }

    public function view(User $user, Package $package): bool
    {
        return $user->can('packages.view') && $this->inScope($user, $package);
    }

    public function create(User $user): bool
    {
        return $user->can('packages.request');
    }

    public function update(User $user, Package $package): bool
    {
        return $user->can('packages.send') && $this->inScope($user, $package);
    }

    public function delete(User $user, Package $package): bool
    {
        return $user->can('packages.send') && $this->inScope($user, $package);
    }

    private function inScope(User $user, Package $package): bool
    {
        return Package::query()->accessibleBy($user)->whereKey($package->id)->exists();
    }
}
