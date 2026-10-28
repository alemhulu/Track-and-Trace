{{-- Fields shared by the create and edit role forms. Expects $permission, $selected and optional $role. --}}
@php $role ??= null; @endphp

<div class="divide-y divide-slate-100 dark:divide-slate-800">
    <x-admin.form-section title="Role" description="A short, recognisable name, e.g. Regional Officer.">
        <x-ui.field label="Role name" for="name" required class="max-w-md">
            <x-input type="text" name="name" id="name" value="{{ old('name', $role?->name) }}" />
        </x-ui.field>
    </x-admin.form-section>

    <x-admin.form-section title="Permissions" description="Grouped by module. Use Toggle all to grant a whole module at once.">
        <x-admin.permission-picker :permissions="$permission" :selected="$selected" />
        @error('permission')
        <p class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    </x-admin.form-section>
</div>
