<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organizations.view');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('organizations.view') && $this->inScope($user, $organization);
    }

    public function create(User $user): bool
    {
        return $user->can('organizations.create');
    }

    // There is no edit screen for organizations yet; whoever may create them may change them
    public function update(User $user, Organization $organization): bool
    {
        return $user->can('organizations.create') && $this->inScope($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can('organizations.delete') && $this->inScope($user, $organization);
    }

    private function inScope(User $user, Organization $organization): bool
    {
        return Organization::query()->accessibleBy($user)->whereKey($organization->id)->exists();
    }
}
