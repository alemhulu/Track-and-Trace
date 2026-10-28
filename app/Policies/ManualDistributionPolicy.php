<?php

namespace App\Policies;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\User;

class ManualDistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ManualDistribution $distribution): bool
    {
        return ManualDistribution::query()->accessibleBy($user)->whereKey($distribution->id)->exists();
    }

    public function create(User $user): bool
    {
        // Which source they may dispatch from is checked per shipment
        return $user->hasNationalAccess()
            || $user->hasRole('Admin')
            || ! empty($user->organization_id)
            || ! empty($user->region_id);
    }

    public function update(User $user, ManualDistribution $distribution): bool
    {
        return $this->view($user, $distribution);
    }

    public function delete(User $user, ManualDistribution $distribution): bool
    {
        return $this->view($user, $distribution);
    }
}
