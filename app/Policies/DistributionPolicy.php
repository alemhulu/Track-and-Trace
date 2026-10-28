<?php

namespace App\Policies;

use App\Models\Distribution;
use App\Models\User;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class DistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('distributions.view');
    }

    public function view(User $user, Distribution $distribution): bool
    {
        return $user->can('distributions.view') && $this->inScope($user, $distribution);
    }

    public function create(User $user): bool
    {
        return $user->can('distributions.create');
    }

    public function update(User $user, Distribution $distribution): bool
    {
        return $user->can('distributions.edit') && $this->inScope($user, $distribution);
    }

    public function delete(User $user, Distribution $distribution): bool
    {
        return $user->can('distributions.delete') && $this->inScope($user, $distribution);
    }

    private function inScope(User $user, Distribution $distribution): bool
    {
        return Distribution::query()->accessibleBy($user)->whereKey($distribution->id)->exists();
    }
}
