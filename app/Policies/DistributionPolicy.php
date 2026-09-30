<?php

namespace App\Policies;

use App\Models\Distribution;
use App\Models\User;

class DistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Distribution $distribution): bool
    {
        return Distribution::query()->accessibleBy($user)->whereKey($distribution->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Distribution $distribution): bool
    {
        return $this->view($user, $distribution);
    }

    public function delete(User $user, Distribution $distribution): bool
    {
        return $this->view($user, $distribution);
    }
}

