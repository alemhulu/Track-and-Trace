<div class="overflow-hidden card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                    {{ $header ?? " " }}
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Action</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-900 dark:divide-slate-800">
                {{ $row ?? $slot}}
            </tbody>
        </table>
    </div>
</div>
