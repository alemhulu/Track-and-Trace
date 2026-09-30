<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WareHouse;

class WareHousePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WareHouse $wareHouse): bool
    {
        return WareHouse::query()->accessibleBy($user)->whereKey($wareHouse->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WareHouse $wareHouse): bool
    {
        return $this->view($user, $wareHouse);
    }

    public function delete(User $user, WareHouse $wareHouse): bool
    {
        return $this->view($user, $wareHouse);
    }
}

