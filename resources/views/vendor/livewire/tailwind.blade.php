<div class="absolute bottom-0 w-full">
    @if ($paginator->hasPages())
    @php(isset($this->numberOfPaginatorsRendered[$paginator->getPageName()]) ?
    $this->numberOfPaginatorsRendered[$paginator->getPageName()]++ :
    $this->numberOfPaginatorsRendered[$paginator->getPageName()] = 1)

    <nav role="navigation" aria-label="Pagination Navigation"
        class=" px-4 py-3 flex items-center justify-between sm:px-6 ">
        <div class="flex justify-between flex-1 sm:hidden">
            <span>
                @if ($paginator->onFirstPage())
                <span
                    class="relative inline-flex items-center px-4 py-1 text-xs font-medium text-gray-500  border border-gray-300 cursor-not-allowed leading-5 rounded-lg opacity-50"
                    disabled>
                    <i class="fi fi-rr-angle-left flex pr-2"></i> {{ __('Previous') }}
                </span>
                @else
                <x-actionbtn wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                    class="px-4 py-1 text-xs font-medium text-lime-700  leading-5 rounded-md hover:text-lime-500">
                    <i class="fi fi-rr-angle-left flex"></i>{{ __('Previous') }}
                </x-actionbtn>
                @endif
            </span>

            <span>
                @if ($paginator->hasMorePages())
                <x-actionbtn wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                    class="px-4 py-1 text-xs font-medium text-lime-700  leading-5 rounded-md hover:text-lime-500">
                    {{ __('Next') }}<i class="fi fi-rr-angle-right flex "></i>
                </x-actionbtn>
                @else
                <span
                    class="relative inline-flex items-center px-4 py-1 text-xs font-medium text-gray-500 border border-gray-300 cursor-not-allowed leading-5 rounded-lg opacity-50"
                    disabled>
                    {{ __('Next') }} <i class="fi fi-rr-angle-right flex pl-2"></i>
                </span>
                @endif
            </span>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div class="">
                <p class="text-xs text-lime-700 leading-5">
                    <span>{!! __('Showing') !!}</span>
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    <span>{!! __('to') !!}</span>
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    <span>{!! __('of') !!}</span>
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    <span>{!! __('results') !!}</span>
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-md  gap-x-1">
                    <span>
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="relative inline-flex items-center px-2 py-2 text-xs font-medium text-gray-500  border border-gray-300 cursor-not-allowed leading-5 rounded-lg opacity-50"
                                disabled aria-hidden="true">
                                <i class="fi fi-rr-angle-left flex pr-1"></i>
                            </span>
                        </span>
                        @else
                        <x-actionbtn wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                            rel="prev"
                            class="px-2 py-2 text-xs font-medium leading-5 hover:text-lime-400 focus:z-10 focus:outline-none active:text-lime-500 "
                            aria-label="{{ __('pagination.previous') }}"><i class="fi fi-rr-angle-left flex pr-1"></i>
                        </x-actionbtn>
                        @endif
                    </span>

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                    <span aria-disabled="true">
                        <span
                            class="px-1 py-2 text-sm font-medium leading-5 hover:text-lime-400 focus:z-10 focus:outline-none active:text-lime-500 flex items-center text-lime-700"><i
                                class="fi fi-rr-menu-dots flex"></i></span>
                    </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                    @foreach ($element as $page => $url)
                    <span
                        wire:key="paginator-{{ $paginator->getPageName() }}-{{ $this->numberOfPaginatorsRendered[$paginator->getPageName()] }}-page{{ $page }}">
                        @if ($page == $paginator->currentPage())
                        <span aria-current="page">
                            <span class="flex justify-center items-center px-3 py-1 text-xs p-1 transition
                                delay-75 ease-out transform -translate-y-0.5 scale-105 gap-2 border rounded-lg text-lime-900 shadow-sm
                                border-transparent border-lime-200
                                bg-lime-200 leading-5">{{ $page }}</span>
                        </span>
                        @else
                        <x-actionbtn wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                            class="py-1 px-3 text-xs text-lime-700 leading-5"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                            {{ $page }}
                        </x-actionbtn>
                        @endif
                    </span>
                    @endforeach
                    @endif
                    @endforeach

                    <span>
                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                        <x-actionbtn wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                            rel="next"
                            class="px-2 py-2 text-xs font-medium leading-5 hover:text-lime-400 focus:z-10 focus:outline-none active:text-lime-500 "
                            aria-label="{{ __('pagination.previous') }}"><i class="fi fi-rr-angle-right flex pl-1"></i>
                        </x-actionbtn>
                        @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span
                                class="relative inline-flex items-center px-2 py-2 text-xs font-medium text-gray-500 border border-gray-300 cursor-not-allowed leading-5 rounded-lg opacity-50"
                                disabled aria-hidden="true">
                                <i class="fi fi-rr-angle-right flex pl-1"></i>
                            </span>
                        </span>
                        @endif
                    </span>
                </span>
            </div>
        </div>
    </nav>
    @endif
</div>