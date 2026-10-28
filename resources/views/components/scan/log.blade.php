@props(['scans', 'undo' => 'void'])
<div {{ $attributes->merge(['class' => 'overflow-x-auto bg-white rounded-lg shadow-sm dark:bg-gray-800']) }}>
    <table class="w-full text-sm text-left">
        <thead class="text-xs text-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-3 py-2">Time</th>
                <th class="px-3 py-2">Scanned</th>
                <th class="px-3 py-2">Zone / Title</th>
                <th class="px-3 py-2">Check</th>
                <th class="px-3 py-2"><span class="sr-only">Undo</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($scans as $scan)
            <tr wire:key="scan-{{ $scan->id }}">
                <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ $scan->scanned_at->format('H:i:s') }}</td>
                <td class="px-3 py-2 text-gray-700 break-all dark:text-gray-200">{{ $scan->payload }}</td>
                <td class="px-3 py-2 text-gray-700 dark:text-gray-200">
                    {{ $scan->zone->name ?? '' }}{{ $scan->zone && $scan->book ? ' · ' : '' }}{{ $scan->book->title ?? '' }}
                </td>
                <td class="px-3 py-2 text-xs {{ $scan->isAlert() ? 'text-red-600 font-semibold' : ($scan->flag ? 'text-amber-600' : 'text-green-700') }}">
                    {{ $scan->flag ? (\App\Models\ManualTracking\ManualScan::FLAG_LABELS[$scan->flag] ?? $scan->flag) : 'OK' }}
                </td>
                <td class="px-3 py-2 text-right">
                    @if ((int) $scan->scanned_by === (int) auth()->id())
                    <button type="button" wire:click="{{ $undo }}({{ $scan->id }})" class="text-xs text-red-600 hover:underline">Undo</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-3 py-4 text-center text-gray-500">No scans yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
