@props(['title', 'subTitle', 'action', 'enctype' => 'multipart/form-data', 'method' => 'POST', 'publish'])
<div>
    <div class="py-3">
        <div class="mx-auto">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <form enctype="{{ $enctype }}" action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)
                        <div class="space-y-8 sm:space-y-5">
                            <x-form.header>
                                {{ $title ?? 'form'}}
                            </x-form.header>


                            <div class="py-3">
                                {{ $body }}
                            </div>


                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <x-button type="button" btnType="secondary" class="">
                                        Cancel
                                    </x-button>
                                    <x-button type="submit" class="ml-3">
                                        Save
                                    </x-button>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>