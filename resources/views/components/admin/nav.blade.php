{{-- Administration sub-navigation shared by the user and role screens. --}}
<x-page.nav {{ $attributes }}>
    @can('users.view')
    <x-page.nav-link title="Users" link="users.index" icon="fi fi-rr-user" />
    @endcan
    @can('roles.view')
    <x-page.nav-link title="Roles & Permissions" link="roles.index" icon="fi fi-rr-key" />
    @endcan
</x-page.nav>
