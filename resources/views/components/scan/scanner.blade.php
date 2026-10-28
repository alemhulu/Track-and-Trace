@props(['method' => 'scan', 'placeholder' => 'Scan or type the QR text, then press Enter'])
{{--
    QR input for Livewire screens. Works three ways: the phone camera, a handheld USB/Bluetooth
    scanner (which types the text and presses Enter), or typing the code printed under the QR.
    Each scan calls the component's {{ $method }}(payload, clientUuid).
--}}
<div wire:ignore x-data="qrScanner(@js($method))" x-on:scan-feedback.window="feedback($event.detail)"
    {{ $attributes->merge(['class' => 'p-3 border border-dashed border-gray-300 rounded-lg dark:border-gray-600']) }}>
    {{-- Not a <form>: the scanner often sits inside one, and Enter must never submit it --}}
    <div class="flex flex-wrap gap-2">
        <input x-model="typed" x-ref="typed" x-on:keydown.enter.prevent="submitTyped" type="text" autocomplete="off" placeholder="{{ $placeholder }}"
            class="flex-1 min-w-[12rem] form-control" />
        <button type="button" x-on:click="submitTyped" class="btn-primary">Add</button>
        <button type="button" x-on:click="running ? stop() : start()"
            class="px-3 py-2 text-sm font-medium text-white rounded-md"
            :class="running ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700'"
            x-text="running ? 'Stop camera' : 'Use camera'"></button>
    </div>
    <p x-show="error" x-text="error" class="mt-2 text-xs text-red-600"></p>
    <div x-show="running" class="max-w-sm mt-3 overflow-hidden rounded-lg">
        <div x-ref="reader" :id="readerId"></div>
    </div>
    <p x-show="running" class="mt-1 text-xs text-gray-500">Hold each package label in the frame. The camera pauses briefly after every read.</p>
</div>
