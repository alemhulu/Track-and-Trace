{{-- Fields shared by the create and edit user forms. Expects $roles, $selectedRoles, $cascade and optional $user. --}}
@php
$user ??= null;
$roleNames = collect($roles)->map(fn ($role) => is_string($role) ? $role : $role->name)->values();
@endphp

<div class="divide-y divide-slate-100 dark:divide-slate-800">
    <x-admin.form-section title="Account" description="How this person is identified and signs in.">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Full name" for="name" required>
                <x-input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" autocomplete="name" />
            </x-ui.field>
            <x-ui.field label="Email" for="email" required>
                <x-input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" autocomplete="email" />
            </x-ui.field>
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Access scope" description="The level and area this user can see and act on. Lower levels need the areas above them.">
        @livewire('users.location-cascade', $cascade)
    </x-admin.form-section>

    <x-admin.form-section title="Password"
        description="{{ $user ? 'Leave both fields blank to keep the current password.' : 'Share it with the user securely; they can change it from their profile.' }}">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Password" for="password" :required="! $user">
                <x-input type="password" name="password" id="password" autocomplete="new-password" />
            </x-ui.field>
            <x-ui.field label="Confirm password" for="confirm-password" :required="! $user">
                <x-input type="password" name="confirm-password" id="confirm-password" autocomplete="new-password" />
            </x-ui.field>
        </div>
    </x-admin.form-section>

    <x-admin.form-section title="Roles" description="Roles decide which screens and actions are available. Pick at least one. Only roles within your own permissions are listed.">
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($roleNames as $role)
            <x-multi-checkbox name="roles[]" value="{{ $role }}" title="{{ $role }}"
                :checked="in_array($role, $selectedRoles, true)" />
            @endforeach
            @foreach ($keptRoles ?? [] as $role)
            <x-multi-checkbox name="roles[]" value="{{ $role }}" title="{{ $role }}" checked disabled
                reason="Has permissions you do not hold, so only someone who holds them can change it" />
            @endforeach
        </div>
        @if (! empty($keptRoles))
        <p class="mt-2 form-hint">Greyed-out roles have permissions you do not hold. They stay as they are.</p>
        @endif
        @error('roles')
        <p class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </x-admin.form-section>
</div>
