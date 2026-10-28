{{-- Stop trying to control. --}}
<x-page.nav-link class="bg-yellow-600" title="Country" link="location.country" can="locations.countries.view" icon="fi fi-rr-plus"
    total="{{ $countries }}" />
<x-page.nav-link class="bg-green-600" title="Region / City" link="location.region" can="locations.regions.view" icon="fi fi-rr-plus"
    total="{{ $regions }}" />
<x-page.nav-link class="bg-blue-800" title="Zone / SubCity" link="location.zone" can="locations.zones.view" icon="fi fi-rr-plus"
    total="{{ $zones }}" />
<x-page.nav-link class="bg-pink-800" title="Woreda" link="location.woreda" can="locations.woredas.view" icon="fi fi-rr-plus"
    total="{{ $woredas }}" />