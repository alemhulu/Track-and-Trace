<?php

namespace App\Policies;

use App\Models\DistributionRoute;
use App\Models\User;

class DistributionRoutePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DistributionRoute $distributionRoute): bool
    {
        return DistributionRoute::query()->accessibleBy($user)->whereKey($distributionRoute->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DistributionRoute $distributionRoute): bool
    {
        return $this->view($user, $distributionRoute);
    }

    public function delete(User $user, DistributionRoute $distributionRoute): bool
    {
        return $this->view($user, $distributionRoute);
    }
}

