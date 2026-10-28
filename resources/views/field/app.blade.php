<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1d4ed8">
    <title>Field App · {{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased text-gray-900 bg-gray-100 dark:bg-gray-900 dark:text-gray-100">
<div x-data="fieldApp(@js([
        'userId' => auth()->id(),
        'csrf' => csrf_token(),
        'packUrl' => route('field.pack'),
        'syncUrl' => route('field.sync'),
        'defaultOrganization' => (string) (auth()->user()->organization_id ?? ''),
    ]))" class="max-w-3xl px-4 pb-16 mx-auto">

    {{-- Status bar --}}
    <header class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-2 py-3 bg-gray-100 dark:bg-gray-900">
        <div>
            <p class="text-xs text-gray-500">Field App · {{ auth()->user()->name }}</p>
            <p class="font-semibold" x-text="pack ? pack.organization.name : 'Not downloaded yet'"></p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="online ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
                x-text="online ? 'Online' : 'Offline'"></span>
            <button type="button" x-on:click="sync()" :disabled="busy || !online || !queue.length"
                class="btn-primary btn-sm">
                Sync <span x-text="queue.length"></span>
            </button>
        </div>
    </header>

    <p x-show="notice" x-text="notice" class="p-3 mb-3 text-sm rounded-md"
        :class="{ 'bg-red-100 text-red-800': noticeType === 'error', 'bg-green-100 text-green-800': noticeType === 'success', 'bg-blue-100 text-blue-800': noticeType === 'info' }"></p>

    {{-- Download --}}
    <section class="p-4 mb-4 card">
        <div class="flex flex-wrap items-end gap-2">
            <div class="flex-1 min-w-[12rem]">
                <label for="field-org" class="block text-xs text-gray-500">Organization</label>
                <select id="field-org" x-model="organizationId" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                    <option value="">Choose…</option>
                    @foreach ($organizations as $organization)
                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" x-on:click="download()" :disabled="busy || !online"
                class="btn-success">Download for offline use</button>
        </div>
        <p class="mt-2 text-xs text-gray-500" x-show="pack">
            Data from <span x-text="pack && new Date(pack.generated_at).toLocaleString()"></span>.
            Download again before going somewhere without a connection.
        </p>
        <p class="mt-2 text-xs text-gray-500">
            Add this page to the home screen to open it without a connection. <a href="{{ route('manual-tracking.distribution.list') }}" class="text-blue-700 underline">Back to the full system</a>
        </p>
    </section>

    <template x-if="pack">
        <div>
            <nav class="flex gap-1 mb-3 text-sm">
                <button type="button" x-on:click="tab = 'receive'" class="px-3 py-2 rounded-md" :class="tab === 'receive' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800'">
                    Receive (<span x-text="shipments().length"></span>)
                </button>
                <button type="button" x-show="pack.organization.is_school" x-on:click="tab = 'issue'" class="px-3 py-2 rounded-md" :class="tab === 'issue' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800'">Issue books</button>
                <button type="button" x-on:click="tab = 'outbox'" class="px-3 py-2 rounded-md" :class="tab === 'outbox' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800'">
                    Outbox (<span x-text="queue.length"></span>)
                </button>
            </nav>

            {{-- Receive --}}
            <section x-show="tab === 'receive'" class="space-y-3">
                <template x-if="!shipmentId">
                    <div class="space-y-2">
                        <p x-show="!shipments().length" class="p-4 text-sm text-gray-500 bg-white rounded-lg dark:bg-gray-800">No shipments on the way to you.</p>
                        <template x-for="item in shipments()" :key="item.id">
                            <button type="button" x-on:click="!isQueued(item.id) && openShipment(item.id)"
                                class="block w-full p-4 text-left card">
                                <span class="font-semibold" x-text="item.reference"></span>
                                <span class="block text-sm text-gray-500" x-text="'From ' + item.from + ' · sent ' + item.dispatched_at"></span>
                                <span class="block text-sm" x-text="item.lines.length + ' titles'"></span>
                                <span x-show="isQueued(item.id)" class="inline-block px-2 mt-1 text-xs font-semibold rounded bg-amber-100 text-amber-800">Receipt saved, waiting to sync</span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="shipmentId">
                    <div class="p-4 space-y-3 card">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold" x-text="shipment().reference + ' from ' + shipment().from"></p>
                            <button type="button" x-on:click="shipmentId = null" class="text-sm text-gray-500 underline">Back</button>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Enter the copies that arrived. The numbers start at what was sent.</p>

                        <x-scan.scanner placeholder="Optional: scan each package label to count them" />
                        <p class="text-sm" x-show="packageScans.own || packageScans.other.length">
                            Packages scanned: <strong x-text="packageScans.own"></strong> of <span x-text="expectedPackages()"></span>
                            <span x-show="packageScans.other.length" class="font-semibold text-red-700" x-text="' · ' + packageScans.other.length + ' for another zone'"></span>
                        </p>

                        <template x-for="line in shipment().lines" :key="line.id">
                            <div class="p-3 border border-gray-200 rounded-md dark:border-gray-700">
                                <p class="font-medium" x-text="line.title"></p>
                                <p class="text-xs text-gray-500" x-text="'Sent ' + line.student_books + ' student books, ' + line.teacher_guides + ' teacher guides in ' + line.packages + ' packages'"></p>
                                <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                                    <label>Student books received<input type="number" min="0" inputmode="numeric" x-model="counts[line.id].student_books" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600"></label>
                                    <label>Teacher guides received<input type="number" min="0" inputmode="numeric" x-model="counts[line.id].teacher_guides" :disabled="!line.has_teacher_guide" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600"></label>
                                    <label>Damaged student books<input type="number" min="0" inputmode="numeric" x-model="counts[line.id].damaged_quantity" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600"></label>
                                    <label>Damaged teacher guides<input type="number" min="0" inputmode="numeric" x-model="counts[line.id].damaged_teacher_guides" :disabled="!line.has_teacher_guide" class="block w-full mt-1 border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600"></label>
                                </div>
                            </div>
                        </template>

                        <label class="block text-xs">Notes
                            <textarea x-model="receiptNotes" rows="2" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600"></textarea>
                        </label>
                        <button type="button" x-on:click="saveReceipt()" class="w-full btn-success btn-lg">Save receipt</button>
                    </div>
                </template>
            </section>

            {{-- Issue --}}
            <section x-show="tab === 'issue'" class="space-y-3">
                <div class="grid grid-cols-2 gap-2 p-4 bg-white rounded-lg shadow-sm sm:grid-cols-4 dark:bg-gray-800">
                    <label class="text-xs">Grade
                        <select x-model="gradeId" x-on:change="section = ''; studentId = null" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                            <option value="">Choose…</option>
                            <template x-for="grade in grades()" :key="grade.id"><option :value="grade.id" x-text="'Grade ' + grade.name"></option></template>
                        </select>
                    </label>
                    <label class="text-xs">Section
                        <select x-model="section" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                            <option value="">All</option>
                            <template x-for="option in sections()" :key="option"><option :value="option" x-text="option"></option></template>
                        </select>
                    </label>
                    <label class="text-xs">Search
                        <input type="search" x-model="search" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                    </label>
                    <label class="text-xs">Condition given
                        <select x-model="condition" class="block w-full mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                            @foreach (\App\Models\School\StudentBookIssue::CONDITIONS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <template x-if="studentId">
                    <div class="p-4 space-y-3 bg-white border-2 border-blue-500 rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="flex items-center justify-between gap-2">
                            <p><span class="font-semibold" x-text="student().name"></span> <span class="text-xs text-gray-500" x-text="student().student_no"></span></p>
                            <button type="button" x-on:click="nextStudent()" class="px-3 py-1 text-sm text-blue-700 rounded-md bg-blue-50">Next student →</button>
                        </div>
                        <x-scan.scanner placeholder="Scan each book as you hand it over" />
                        <template x-for="title in titlesFor(student())" :key="title.id">
                            <div class="flex items-center justify-between gap-2 py-1 border-b border-gray-100 dark:border-gray-700">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" x-model="picked[title.id]" :disabled="student().held.includes(title.id)" class="text-blue-600 border-gray-300 rounded">
                                    <span x-text="title.title"></span>
                                    <span class="text-xs text-gray-500" x-text="'(' + onHand(title.id) + ' on hand)'"></span>
                                </label>
                                <template x-if="student().held.includes(title.id)">
                                    <span class="flex items-center gap-1 text-xs">
                                        <span class="font-semibold text-green-700">✓ Holds</span>
                                        <button type="button" x-on:click="returnBook(title.id)" class="text-gray-500 underline">Return</button>
                                    </span>
                                </template>
                            </div>
                        </template>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" x-on:click="issuePicked()" class="flex-1 btn-success btn-lg">Give ticked books</button>
                            <label class="text-xs">Return condition
                                <select x-model="returnCondition" class="block mt-1 text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:border-gray-600">
                                    @foreach (\App\Models\School\StudentBookIssue::CONDITIONS + \App\Models\School\StudentBookIssue::UNUSABLE_RETURN_CONDITIONS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </template>

                <div x-show="gradeId" class="overflow-hidden card">
                    <template x-for="item in classList()" :key="item.id">
                        <button type="button" x-on:click="chooseStudent(item.id)"
                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-left border-b border-gray-100 dark:border-gray-700"
                            :class="item.id === studentId ? 'bg-blue-50 dark:bg-blue-900/30' : ''">
                            <span><span x-text="item.name"></span> <span class="text-xs text-gray-500" x-text="item.student_no + (item.section ? ' · ' + item.section : '')"></span></span>
                            <span class="text-xs" :class="missing(item) ? 'text-amber-700' : 'text-green-700'" x-text="missing(item) ? missing(item) + ' missing' : 'All books'"></span>
                        </button>
                    </template>
                </div>
            </section>

            {{-- Outbox --}}
            <section x-show="tab === 'outbox'" class="space-y-3">
                <div class="card">
                    <p class="px-4 pt-3 text-sm font-semibold">Waiting to sync</p>
                    <p x-show="!queue.length" class="px-4 py-3 text-sm text-gray-500">Nothing waiting.</p>
                    <template x-for="action in queue" :key="action.uuid">
                        <div class="px-4 py-2 text-sm border-t border-gray-100 dark:border-gray-700">
                            <span x-text="action.description"></span>
                            <span class="block text-xs text-gray-500" x-text="new Date(action.recorded_at).toLocaleString()"></span>
                        </div>
                    </template>
                </div>
                <div class="card">
                    <div class="flex items-center justify-between px-4 pt-3">
                        <p class="text-sm font-semibold">Sent</p>
                        <button type="button" x-show="log.length" x-on:click="discardLog()" class="text-xs text-gray-500 underline">Clear</button>
                    </div>
                    <p x-show="!log.length" class="px-4 py-3 text-sm text-gray-500">Nothing sent yet.</p>
                    <template x-for="action in log" :key="action.uuid">
                        <div class="px-4 py-2 text-sm border-t border-gray-100 dark:border-gray-700">
                            <span x-text="action.description"></span>
                            <span class="block text-xs" :class="action.status === 'applied' ? 'text-green-700' : 'text-red-700 font-semibold'"
                                x-text="(action.status === 'applied' ? 'Done: ' : 'Not applied: ') + (action.message || '')"></span>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-gray-500">Work that was not applied must be corrected in the full system, for example after a missing delivery arrives.</p>
            </section>
        </div>
    </template>
</div>

<script src="/js/qr-scanner.js"></script>
<script src="/js/field-app.js"></script>
</body>
</html>
