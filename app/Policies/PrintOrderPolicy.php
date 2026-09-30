<?php

namespace App\Policies;

use App\Models\PrintOrder;
use App\Models\User;

class PrintOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PrintOrder $printOrder): bool
    {
        return PrintOrder::query()->accessibleBy($user)->whereKey($printOrder->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PrintOrder $printOrder): bool
    {
        return $this->view($user, $printOrder);
    }

    public function delete(User $user, PrintOrder $printOrder): bool
    {
        return $this->view($user, $printOrder);
    }
}

