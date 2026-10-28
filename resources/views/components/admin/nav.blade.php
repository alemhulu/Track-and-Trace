{{-- Administration sub-navigation shared by the user and role screens. --}}
<x-page.nav {{ $attributes }}>
    @canany(['view-user', 'user-list'])
    <x-page.nav-link title="Users" link="users.index" icon="fi fi-rr-user" />
    @endcanany
    @canany(['view-role', 'role-list'])
    <x-page.nav-link title="Roles & Permissions" link="roles.index" icon="fi fi-rr-key" />
    @endcanany
</x-page.nav>
