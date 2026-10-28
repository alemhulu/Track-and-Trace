<?php

namespace App\Policies;

use App\Models\DistributionRoute;
use App\Models\User;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class DistributionRoutePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('routes.view');
    }

    public function view(User $user, DistributionRoute $distributionRoute): bool
    {
        return $user->can('routes.view') && $this->inScope($user, $distributionRoute);
    }

    public function create(User $user): bool
    {
        return $user->can('routes.create');
    }

    public function update(User $user, DistributionRoute $distributionRoute): bool
    {
        return $user->can('routes.edit') && $this->inScope($user, $distributionRoute);
    }

    public function delete(User $user, DistributionRoute $distributionRoute): bool
    {
        return $user->can('routes.edit') && $this->inScope($user, $distributionRoute);
    }

    private function inScope(User $user, DistributionRoute $distributionRoute): bool
    {
        return DistributionRoute::query()->accessibleBy($user)->whereKey($distributionRoute->id)->exists();
    }
}
