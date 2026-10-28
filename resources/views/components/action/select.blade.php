<select {{ $attributes->merge([ 'class' => 'form-control py-1 pl-3 pr-8 text-sm disabled:opacity-40']) }}>
    {{ $slot }}
</select>
