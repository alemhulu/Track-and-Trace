<?php

namespace App\Policies;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\User;

class ManualDistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manual-tracking.view');
    }

    public function view(User $user, ManualDistribution $distribution): bool
    {
        return $user->can('manual-tracking.view')
            && ManualDistribution::query()->accessibleBy($user)->whereKey($distribution->id)->exists();
    }

    public function create(User $user): bool
    {
        // Schools only receive. Which source others may dispatch from is checked per shipment
        if ($user->isSchoolUser() || ! $user->can('manual-tracking.shipments.dispatch')) {
            return false;
        }

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
