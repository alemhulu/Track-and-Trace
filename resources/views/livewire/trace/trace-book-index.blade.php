<div>
    <x-form.card function="save" title="Trace Book">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Filter Book
                        </h3>
                        <p class="max-w-2xl mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Pick a grade and subject to follow those books through the supply chain.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 mt-6 sm:mt-5 gap-5">
                        <div class="col-span-1">
                            <x-jet-label value="Grade" />
                            <x-form.select wire:model="grade_id" id="grade_id" class="max-w-md">
                                <option value="">All grades</option>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <div class="col-span-1">
                            <x-jet-label value="Subject" />
                            <x-form.select wire:model="subject_id" id="subject_id" class="max-w-md">
                                <option value="">All subjects</option>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>

                    <div>
                        <livewire:trace.trace-book-info :gradeId="$grade_id" :subjectId="$subject_id"
                            :wire:key="'trace-book-info-'.$grade_id.'-'.$subject_id" />
                    </div>

                    <div>
                        <livewire:trace.trace-book-distribution :gradeId="$grade_id" :subjectId="$subject_id"
                            :wire:key="'trace-distribution-'.$grade_id.'-'.$subject_id" />
                    </div>
                </div>
            </div>
        </form>
    </x-form.card>
</div>
