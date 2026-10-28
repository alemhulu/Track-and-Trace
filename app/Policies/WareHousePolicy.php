<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WareHouse;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class WareHousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('warehouses.view');
    }

    public function view(User $user, WareHouse $wareHouse): bool
    {
        return $user->can('warehouses.view') && $this->inScope($user, $wareHouse);
    }

    public function create(User $user): bool
    {
        // Adding an organization also creates its store
        return $user->can('warehouses.create') || $user->can('organizations.create');
    }

    public function update(User $user, WareHouse $wareHouse): bool
    {
        return $user->can('warehouses.edit') && $this->inScope($user, $wareHouse);
    }

    public function delete(User $user, WareHouse $wareHouse): bool
    {
        return $user->can('warehouses.delete') && $this->inScope($user, $wareHouse);
    }

    private function inScope(User $user, WareHouse $wareHouse): bool
    {
        return WareHouse::query()->accessibleBy($user)->whereKey($wareHouse->id)->exists();
    }
}
