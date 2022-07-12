@props(['label', 'id'])
<button type="button" for="{{ $id }}" class="flex space-x-2 w-full  items-center mt-2">
    <input type="radio" id="{{ $id }}" {!! $attributes->merge(['class' => 'rounded border-gray-300
    focus:border-indigo-300 focus:ring-indigo-200 focus:ring-opacity-50 p-3']) !!}>

    @isset($id)
    <label for="{{ $id }}"
        class="text-sm font-bold py-auto w-full text-left cursor-pointer text-gray-500">{{ $label }}</label>
    @endisset
</button>