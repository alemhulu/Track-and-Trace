<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4 md:flex-1">
            <div>
                <x-jet-label for="year" value="Academic year" />
                <x-form.select wire:model="year" id="year">
                    @forelse ($years as $option)
                    <option value="{{ $option }}">{{ \App\Support\AcademicYear::label((int) $option) }}</option>
                    @empty
                    <option value="{{ $year }}">{{ \App\Support\AcademicYear::label((int) $year) }}</option>
                    @endforelse
                </x-form.select>
            </div>
            <div>
                <x-jet-label for="regionId" value="Region" />
                <x-form.select wire:model="regionId" id="regionId" :disabled="$regionLocked">
                    @unless ($regionLocked)
                    <option value="">All regions</option>
                    @endunless
                    @foreach ($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            @if ($zoneRows->isNotEmpty() || $zoneLocked)
            <div>
                <x-jet-label for="zoneId" value="Zone" />
                <x-form.select wire:model="zoneId" id="zoneId" :disabled="$zoneLocked">
                    @unless ($zoneLocked)
                    <option value="">Whole region</option>
                    @endunless
                    @foreach ($zoneRows as $zone)
                    <option value="{{ $zone->zone_id }}">{{ $zone->name }}</option>
                    @endforeach
                </x-form.select>
            </div>
            @endif
            <label class="flex items-center gap-2 mt-6 text-sm text-gray-700 dark:text-gray-200">
                <input type="checkbox" wire:model="unconfirmedOnly" class="rounded border-gray-300 dark:border-gray-600">
                Only titles with unconfirmed type
            </label>
        </div>
    </div>

    @if ($lastImport)
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Last import: {{ $lastImport->file_name }} on {{ $lastImport->created_at->format('Y-m-d H:i') }}
        by {{ $lastImport->importer->name ?? 'console' }} · {{ number_format($lastImport->title_count) }} titles,
        {{ number_format($lastImport->total_quantity) }} copies.
    </p>
    @endif

    @if ($canImport)
    <div x-data="{ open: @js((bool) $preview) }" class="p-5 card">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="card-title">Import allocation</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">A Ministry workbook with a sheet per region (Region, Language, TYPE, SUBJECT, Grade, Book title, Final Quantity), optionally a Summary or All Regions sheet, and optionally a zone split: a column per zone after Final Quantity, or one sheet per zone named after the zone. It is checked first; nothing is saved until you confirm.</p>
            </div>
            <button type="button" x-on:click="open = ! open" class="btn-primary shrink-0" x-text="open ? 'Close' : 'Import workbook'"></button>
        </div>

        <div x-show="open" x-cloak class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <x-jet-label for="workbook" value="Workbook (.xlsx)" />
                    <input type="file" wire:model="workbook" id="workbook" accept=".xlsx"
                        class="block w-full mt-1 text-sm text-gray-700 dark:text-gray-200">
                    <div wire:loading wire:target="workbook" class="mt-1 text-xs text-gray-500">Uploading…</div>
                    <x-jet-input-error for="workbook" class="mt-1" />
                </div>
                <div>
                    <x-jet-label for="importYear" value="Academic year (E.C.)" />
                    <x-jet-input wire:model.defer="importYear" id="importYear" type="number" class="block w-full mt-1" />
                    <x-jet-input-error for="importYear" class="mt-1" />
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="checkWorkbook" wire:loading.attr="disabled" wire:target="checkWorkbook,workbook" class="btn-secondary">Check file</button>
                @if ($preview && ! $preview['error_count'])
                <button type="button" wire:click="importWorkbook" wire:loading.attr="disabled" class="btn-primary">
                    Save allocation for {{ \App\Support\AcademicYear::label((int) $importYear) }}
                </button>
                @endif
                @if ($preview)
                <button type="button" wire:click="cancelImport" class="btn-secondary">Cancel</button>
                @endif
            </div>

            @if ($preview)
            <div class="space-y-3">
                <p class="text-sm text-gray-700 dark:text-gray-200">
                    <strong>{{ number_format($preview['line_count']) }}</strong> lines →
                    <strong>{{ number_format($preview['title_count']) }}</strong> titles,
                    <strong>{{ number_format($preview['total']) }}</strong> copies.
                    @if (! $preview['error_count'])
                    Saving replaces the allocation of the regions below for that year.
                    @endif
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-left text-gray-500 uppercase dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Region</th>
                                <th class="px-3 py-2 text-right">Lines</th>
                                <th class="px-3 py-2 text-right">Titles</th>
                                <th class="px-3 py-2 text-right">Student texts</th>
                                <th class="px-3 py-2 text-right">Teacher guides</th>
                                <th class="px-3 py-2 text-right">Summary sheet</th>
                                <th class="px-3 py-2 text-right">Type TBD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($preview['regions'] as $name => $row)
                            @php $matches = $row['expected_total'] === null || $row['expected_total'] === $row['student'] + $row['teacher']; @endphp
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-3 py-1.5 font-medium">{{ $name }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['lines']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['titles']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['student']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['teacher']) }}</td>
                                <td class="px-3 py-1.5 text-right {{ $matches ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $row['expected_total'] === null ? '—' : number_format($row['expected_total']) }}
                                </td>
                                <td class="px-3 py-1.5 text-right">{{ $row['unconfirmed'] ?: '' }}</td>
                            </tr>
                            @endforeach
                            @foreach ($preview['awaiting'] as $name => $row)
                            <tr class="text-gray-500 dark:text-gray-400">
                                <td class="px-3 py-1.5 font-medium">{{ $name }}</td>
                                <td class="px-3 py-1.5 text-right" colspan="4"><x-ui.badge tone="warning">No sheet · awaiting data</x-ui.badge></td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['total']) }}</td>
                                <td></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($preview['zones'] ?? [])
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs text-left text-gray-500 uppercase dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Zone</th>
                                <th class="px-3 py-2 text-right">Lines</th>
                                <th class="px-3 py-2 text-right">Titles</th>
                                <th class="px-3 py-2 text-right">Student texts</th>
                                <th class="px-3 py-2 text-right">Teacher guides</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($preview['zones'] as $regionName => $zones)
                            @foreach ($zones as $zoneName => $row)
                            <tr class="text-gray-700 dark:text-gray-200">
                                <td class="px-3 py-1.5 font-medium">{{ $zoneName }} <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $regionName }}</span></td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['lines']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['titles']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['student']) }}</td>
                                <td class="px-3 py-1.5 text-right">{{ number_format($row['teacher']) }}</td>
                                <td class="px-3 py-1.5">@unless ($row['zone_id'])<x-ui.badge tone="info">Zone will be created</x-ui.badge>@endunless</td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if ($preview['error_count'])
                <div class="p-3 text-sm text-red-700 rounded-md bg-red-50 dark:bg-red-900/30 dark:text-red-300">
                    <p class="font-semibold">{{ number_format($preview['error_count']) }} errors; nothing can be saved until they are fixed.</p>
                    <ul class="mt-1 space-y-0.5 list-disc list-inside">
                        @foreach ($preview['errors'] as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if ($preview['warnings'])
                <div class="p-3 text-sm rounded-md text-amber-800 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-200">
                    <p class="font-semibold">Warnings</p>
                    <ul class="mt-1 space-y-0.5 list-disc list-inside">
                        @foreach ($preview['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    <x-form.table title="Allocation by region" :search="false" :entries="false">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Region') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Titles') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Student texts') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Teacher guides') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Dispatched in') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Received') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Progress') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($regionRows as $row)
            @php
                $allocated = $row->student + $row->teacher;
                $received = $row->progress->received ?? 0;
                $percent = $allocated > 0 ? min(100, round($received / $allocated * 100, 1)) : 0;
            @endphp
            <x-data-table.tr wire:key="region-{{ $row->region_id }}">
                <td class="px-5 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    <button type="button" wire:click="$set('regionId', '{{ $row->region_id }}')" class="link">{{ $row->name }}</button>
                    @if ($row->unconfirmed)
                    <x-ui.badge tone="warning" class="ml-1">{{ $row->unconfirmed }} type TBD</x-ui.badge>
                    @endif
                </td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->titles) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->student) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->teacher) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->progress->sent ?? 0) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($received) }}</td>
                <td class="px-5 py-2 text-sm min-w-[10rem]">
                    <div class="h-2 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-700">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $percent }}% received · {{ number_format(max(0, $allocated - $received)) }} to go</span>
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="7" />
            @endforelse
            @foreach ($awaiting as $name => $row)
            <x-data-table.tr wire:key="awaiting-{{ $name }}">
                <td class="px-5 py-2 text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $name }}</td>
                <td class="px-5 py-2 text-sm" colspan="6">
                    <x-ui.badge tone="warning">Awaiting data</x-ui.badge>
                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">In the Summary sheet ({{ number_format($row['lines']) }} lines, {{ number_format($row['total']) }} copies) but no region sheet was imported.</span>
                </td>
            </x-data-table.tr>
            @endforeach
        </x-slot>
    </x-form.table>

    @if ($zoneRows->isNotEmpty())
    <x-form.table title="Allocation by zone" :search="false" :entries="false">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Zone') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Titles') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Student texts') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Teacher guides') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Dispatched in') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Received') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Progress') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @foreach ($zoneRows as $row)
            @php
                $allocated = $row->student + $row->teacher;
                $received = $row->progress->received ?? 0;
                $percent = $allocated > 0 ? min(100, round($received / $allocated * 100, 1)) : 0;
            @endphp
            <x-data-table.tr wire:key="zone-{{ $row->zone_id }}" class="{{ (string) $zoneId === (string) $row->zone_id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                <td class="px-5 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100">
                    <button type="button" wire:click="$set('zoneId', '{{ $row->zone_id }}')" class="link">{{ $row->name }}</button>
                </td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->titles) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->student) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->teacher) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($row->progress->sent ?? 0) }}</td>
                <td class="px-5 py-2 text-sm text-right">{{ number_format($received) }}</td>
                <td class="px-5 py-2 text-sm min-w-[10rem]">
                    <div class="h-2 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-700">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $percent }}% received · {{ number_format(max(0, $allocated - $received)) }} to go</span>
                </td>
            </x-data-table.tr>
            @endforeach
        </x-slot>
    </x-form.table>
    @endif

    <x-form.table :title="$titleZone ? 'Allocation by title · ' . $titleZone->name : 'Allocation by title'">
        <x-slot name="tableHeaders">
            <x-data-table.th scope="col">{{ __('Title') }}</x-data-table.th>
            <x-data-table.th scope="col">{{ __('Region') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Allocated') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Dispatched in') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Received') }}</x-data-table.th>
            <x-data-table.th scope="col" class="text-right">{{ __('Still due') }}</x-data-table.th>
        </x-slot>

        <x-slot name="tableRows">
            @forelse ($titles as $title)
            @php
                $progress = $titleProgress[$title->progress_key] ?? null;
                $dueStudent = max(0, $title->student - ($progress->received_student ?? 0));
                $dueTeacher = max(0, $title->teacher - ($progress->received_teacher ?? 0));
            @endphp
            <x-data-table.tr wire:key="title-{{ $title->region_id }}-{{ $title->manual_book_id }}" class="align-top">
                <td class="px-5 py-2 text-sm text-gray-800 dark:text-gray-100">
                    <x-ui.clamp title="Title">{{ $title->title }}</x-ui.clamp>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $title->language ?? '—' }}</span>
                    @unless ($title->type_confirmed)
                    <x-ui.badge tone="warning" class="ml-1" title="The workbook said TBD; taken as student text">Type TBD</x-ui.badge>
                    @endunless
                    @if ($title->teacher_title && $title->teacher_title !== $title->title)
                    <span class="block text-xs text-gray-500 dark:text-gray-400">TG: {{ $title->teacher_title }}</span>
                    @endif
                </td>
                <td class="px-5 py-2 text-sm whitespace-nowrap">{{ $regionNames[$title->region_id] ?? '—' }}</td>
                <td class="px-5 py-2 text-sm text-right whitespace-nowrap">
                    {{ number_format($title->student) }} ST
                    @if ($title->teacher)<span class="block text-xs text-gray-500 dark:text-gray-400">{{ number_format($title->teacher) }} TG</span>@endif
                </td>
                <td class="px-5 py-2 text-sm text-right whitespace-nowrap">
                    {{ number_format($progress->sent_student ?? 0) }}
                    @if ($title->teacher)<span class="block text-xs text-gray-500 dark:text-gray-400">{{ number_format($progress->sent_teacher ?? 0) }}</span>@endif
                </td>
                <td class="px-5 py-2 text-sm text-right whitespace-nowrap">
                    {{ number_format($progress->received_student ?? 0) }}
                    @if ($title->teacher)<span class="block text-xs text-gray-500 dark:text-gray-400">{{ number_format($progress->received_teacher ?? 0) }}</span>@endif
                </td>
                <td class="px-5 py-2 text-sm font-semibold text-right whitespace-nowrap {{ $dueStudent + $dueTeacher > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ number_format($dueStudent) }}
                    @if ($title->teacher)<span class="block text-xs font-normal">{{ number_format($dueTeacher) }}</span>@endif
                </td>
            </x-data-table.tr>
            @empty
            <x-data-table.empty colspan="6" />
            @endforelse
        </x-slot>

        {{ $titles->links() }}
    </x-form.table>
</div>
