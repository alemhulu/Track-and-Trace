{{-- Stop trying to control. --}}
<x-page.nav-link class="bg-yellow-600" title="Country" link="location.country" icon="fi fi-rr-plus"
    total="{{ $countries }}" />
<x-page.nav-link class="bg-green-600" title="Region / City" link="location.region" icon="fi fi-rr-plus"
    total="{{ $regions }}" />
<x-page.nav-link class="bg-blue-800" title="Zone / SubCity" link="location.zone" icon="fi fi-rr-plus"
    total="{{ $zones }}" />
<x-page.nav-link class="bg-pink-800" title="Woreda" link="location.woreda" icon="fi fi-rr-plus"
    total="{{ $woredas }}" />