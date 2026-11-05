<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        @if ($current && $current->year !== $calendarYear)
        <x-ui.alert type="warning">
            Today falls in {{ \App\Support\AcademicYear::label($calendarYear) }}, but {{ $current->label }} is marked current.
            Screens keep opening on {{ $current->year }} E.C. until you make another year current.
        </x-ui.alert>
        @elseif (! $current)
        <x-ui.alert type="warning">
            No academic year is marked current, so screens fall back to the calendar ({{ \App\Support\AcademicYear::label($calendarYear) }}).
        </x-ui.alert>
        @endif

        <x-ui.card title="Academic years" subtitle="Ethiopian calendar. Years with allocations, enrolments or visits cannot be deleted." icon="fi-rr-calendar" flush>
            @if ($years->isEmpty())
            <x-ui.empty-state icon="fi-rr-calendar" title="No academic years yet" message="Add the first year to choose which one screens open on." />
            @else
            <x-ui.table class="border-0 rounded-none shadow-none">
                <x-slot name="head">
                    <th scope="col">Academic year</th>
                    <th scope="col">Dates</th>
                    <th scope="col">Status</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </x-slot>

                @foreach ($years as $row)
                <tr wire:key="year-{{ $row->id }}" @class(['bg-brand-50/40 dark:bg-brand-900/10' => $editingId === $row->id])>
                    <td>
                        <div class="font-semibold text-slate-900 dark:text-white">{{ $row->year }} E.C.</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $row->year + 7 }}/{{ sprintf('%02d', ($row->year + 8) % 100) }}@if ($row->notes) · {{ $row->notes }}@endif</div>
                    </td>
                    <td class="text-sm whitespace-nowrap">
                        {{ $row->starts_on->format('j M Y') }} – {{ $row->ends_on->format('j M Y') }}
                    </td>
                    <td>
                        @if ($row->is_current)
                        <x-ui.badge tone="success" dot>Current</x-ui.badge>
                        @elseif ($row->ends_on->lt($today))
                        <x-ui.badge tone="neutral">Past</x-ui.badge>
                        @elseif ($row->starts_on->gt($today))
                        <x-ui.badge tone="info">Upcoming</x-ui.badge>
                        @else
                        <x-ui.badge tone="warning">In progress</x-ui.badge>
                        @endif
                    </td>
                    <td class="text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            @can('academic-years.edit')
                            @unless ($row->is_current)
                            <button type="button" wire:click="makeCurrent({{ $row->id }})" class="btn-secondary btn-sm">Make current</button>
                            @endunless
                            <button type="button" wire:click="edit({{ $row->id }})" class="icon-btn-edit" title="Edit {{ $row->year }} E.C.">
                                <i class="flex fi fi-rr-edit"></i><span class="sr-only">Edit</span>
                            </button>
                            @endcan
                            @can('academic-years.delete')
                            @unless ($row->is_current)
                            <button type="button" wire:click="delete({{ $row->id }})"
                                onclick="confirm('Delete {{ $row->year }} E.C.?') || event.stopImmediatePropagation()"
                                class="icon-btn-delete" title="Delete {{ $row->year }} E.C.">
                                <i class="flex fi fi-rr-trash"></i><span class="sr-only">Delete</span>
                            </button>
                            @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-ui.table>
            @endif
        </x-ui.card>
    </div>

    @if ($editingId ? auth()->user()->can('academic-years.edit') : auth()->user()->can('academic-years.create'))
    <div>
        <x-ui.card :title="$editingId ? 'Edit '.$year.' E.C.' : 'Add academic year'"
            subtitle="Dates default to Meskerem 1 through the day before the next Meskerem 1." icon="{{ $editingId ? 'fi-rr-edit' : 'fi-rr-add' }}">
            <form wire:submit.prevent="save" class="space-y-4">
                <x-ui.field label="Academic year (E.C.)" for="year" required
                    :hint="is_numeric($year) ? \App\Support\AcademicYear::label((int) $year) : null">
                    <x-jet-input wire:model.lazy="year" id="year" type="number" min="1990" max="2100" class="block w-full mt-1" />
                </x-ui.field>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-1 2xl:grid-cols-2">
                    <x-ui.field label="Starts" for="starts_on" required>
                        <x-jet-input wire:model.defer="starts_on" id="starts_on" type="date" class="block w-full mt-1" />
                    </x-ui.field>
                    <x-ui.field label="Ends" for="ends_on" required>
                        <x-jet-input wire:model.defer="ends_on" id="ends_on" type="date" class="block w-full mt-1" />
                    </x-ui.field>
                </div>

                <x-ui.field label="Notes" for="notes" hint="Optional, e.g. a delayed start.">
                    <x-jet-input wire:model.defer="notes" id="notes" type="text" maxlength="255" class="block w-full mt-1" />
                </x-ui.field>

                <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" wire:model.defer="make_current" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                    <span>Make this the current year<span class="block text-xs text-slate-500 dark:text-slate-400">Allocation, school and visit screens open on it.</span></span>
                </label>

                <div class="flex items-center justify-end gap-2 pt-1">
                    @if ($editingId)
                    <button type="button" wire:click="cancel" class="btn-ghost">Cancel</button>
                    @endif
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                        {{ $editingId ? 'Save changes' : 'Add year' }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
    @endif
</div>
