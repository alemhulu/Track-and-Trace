<?php

namespace App\Policies;

use App\Models\PrintOrder;
use App\Models\User;

/**
 * The module permission decides whether the user may act at all; the location scope decides on which records.
 */
class PrintOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('print-orders.view');
    }

    public function view(User $user, PrintOrder $printOrder): bool
    {
        return $user->can('print-orders.view') && $this->inScope($user, $printOrder);
    }

    public function create(User $user): bool
    {
        return $user->can('print-orders.create');
    }

    public function update(User $user, PrintOrder $printOrder): bool
    {
        return $user->can('print-orders.edit') && $this->inScope($user, $printOrder);
    }

    public function delete(User $user, PrintOrder $printOrder): bool
    {
        return $user->can('print-orders.edit') && $this->inScope($user, $printOrder);
    }

    private function inScope(User $user, PrintOrder $printOrder): bool
    {
        return PrintOrder::query()->accessibleBy($user)->whereKey($printOrder->id)->exists();
    }
}
