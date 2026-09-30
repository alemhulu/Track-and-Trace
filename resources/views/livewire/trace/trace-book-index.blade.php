<div>
    <x-form.card function="save" title="Trace Book">
        <form class=" space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200 sm:space-y-5">
                <div>
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-50">
                            Filter Book
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            Filter Book By Selecting Grade and Subject
                        </p>
                    </div>

                    <div class="grid md:grid-cols-3 mt-6 sm:mt-5 gap-5">
                        <div class="col-span-1">
                            <x-jet-label value="Grade" />
                            <x-form.select wire:model="grade_id" id="grade_id" class="max-w-md">
                                <option value="">select</option>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <div class="col-span-1">
                            <x-jet-label value="Subject" />
                            <x-form.select wire:model="subject_id" id="subject_id" class="max-w-md">
                                <option value="">select</option>
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
