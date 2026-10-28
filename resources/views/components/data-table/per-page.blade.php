@props(['value' => 10, 'options' => [5, 10, 25, 50, 100]])
{{-- "Show N entries" for controller-rendered tables; the Livewire tables use data-table.entries instead.
     Submits ?per_page=N, keeping the other query parameters and returning to page 1. --}}
<form method="GET" {{ $attributes->merge(['class' => 'flex items-center gap-2 px-5 pt-3 pb-2 text-sm text-slate-500 dark:text-slate-400']) }}>
    @foreach (request()->except(['page', 'per_page']) as $key => $param)
    @if (is_scalar($param))
    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
    @endif
    @endforeach
    <label for="per_page">Show</label>
    <x-action.select id="per_page" name="per_page" class="!w-20" onchange="this.form.submit()">
        @foreach ($options as $option)
        <option value="{{ $option }}" @selected((int) $value === $option)>{{ $option }}</option>
        @endforeach
    </x-action.select>
    <span>entries</span>
    <noscript><button type="submit" class="btn-secondary btn-sm">Apply</button></noscript>
</form>
