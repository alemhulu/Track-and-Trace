{{--
    Region → zone → woreda → organization selects built from App\Support\ScopedLocation::picker().
    Levels fixed by the user's access level are filled in and disabled. Renders one cell per
    select, so place it inside the caller's grid.
--}}
@props([
    'picker',
    'model',
    'organizationModel' => null,
    'organizationLabel' => 'Organization',
    'organizationPlaceholder' => null,
    'label' => '',
    'mode' => 'filter',
])

@php
    $locked = $picker['locked'];
    $first = fn (string $level) => $mode === 'filter' ? "All {$level}s" : "Select {$level}";
    $levels = [
        'region_id' => ['label' => 'Region', 'options' => $picker['regions'], 'enabled' => true],
        'zone_id' => ['label' => 'Zone', 'options' => $picker['zones'], 'enabled' => filled($picker['area']['region_id'])],
        'woreda_id' => ['label' => 'Woreda', 'options' => $picker['woredas'], 'enabled' => filled($picker['area']['zone_id'])],
    ];
@endphp

@foreach ($levels as $key => $level)
<div>
    <x-jet-label for="{{ $model }}-{{ $key }}" value="{{ trim($label . ' ' . ($label ? strtolower($level['label']) : $level['label'])) }}" />
    <x-form.select wire:model="{{ $model }}.{{ $key }}" id="{{ $model }}-{{ $key }}"
        :disabled="array_key_exists($key, $locked) || ! $level['enabled']"
        title="{{ array_key_exists($key, $locked) ? 'Set by your access level' : '' }}">
        <option value="">{{ $first(strtolower($level['label'])) }}</option>
        @foreach ($level['options'] as $option)
        <option value="{{ $option->id }}">{{ $option->name }}</option>
        @endforeach
    </x-form.select>
</div>
@endforeach

@if ($organizationModel)
<div>
    <x-jet-label for="{{ $organizationModel }}" value="{{ $organizationLabel }}" />
    <x-form.select wire:model="{{ $organizationModel }}" id="{{ $organizationModel }}"
        :disabled="array_key_exists('organization_id', $locked)"
        title="{{ array_key_exists('organization_id', $locked) ? 'Set by your access level' : '' }}">
        <option value="">{{ $organizationPlaceholder ?? ($mode === 'filter' ? 'All organizations' : 'Select organization') }}</option>
        @foreach ($picker['organizations'] as $option)
        <option value="{{ $option->id }}">{{ $option->name }}</option>
        @endforeach
    </x-form.select>
    {{ $slot }}
</div>
@endif
