<?php

namespace App\Providers;

use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\Organization;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\WareHouse;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\DistributionPolicy;
use App\Policies\DistributionRoutePolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PackagePolicy;
use App\Policies\PrintOrderPolicy;
use App\Policies\ManualDistributionPolicy;
use App\Policies\WareHousePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Distribution::class => DistributionPolicy::class,
        DistributionRoute::class => DistributionRoutePolicy::class,
        Organization::class => OrganizationPolicy::class,
        Package::class => PackagePolicy::class,
        PrintOrder::class => PrintOrderPolicy::class,
        ManualDistribution::class => ManualDistributionPolicy::class,
        WareHouse::class => WareHousePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super-Admin" role all permission checks using can()
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super-Admin')) {
                return true;
            }
        });
    }
}
