<div class="w-full border-t border-slate-100 dark:border-slate-800">
    @if ($paginator->hasPages())
    @php(isset($this->numberOfPaginatorsRendered[$paginator->getPageName()]) ?
    $this->numberOfPaginatorsRendered[$paginator->getPageName()]++ :
    $this->numberOfPaginatorsRendered[$paginator->getPageName()] = 1)

    <nav role="navigation" aria-label="Pagination Navigation"
        class="flex items-center justify-between px-5 py-3">
        <div class="flex justify-between flex-1 sm:hidden">
            <span>
                @if ($paginator->onFirstPage())
                <span
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium cursor-not-allowed text-slate-300 dark:text-slate-600"
                    disabled>
                    <i class="fi fi-rr-angle-left flex pr-2"></i> {{ __('Previous') }}
                </span>
                @else
                <x-actionbtn wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                    class="px-3 py-1.5 text-sm font-medium">
                    <i class="fi fi-rr-angle-left flex"></i>{{ __('Previous') }}
                </x-actionbtn>
                @endif
            </span>

            <span>
                @if ($paginator->hasMorePages())
                <x-actionbtn wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before"
                    class="px-3 py-1.5 text-sm font-medium">
                    {{ __('Next') }}<i class="fi fi-rr-angle-right flex "></i>
                </x-actionbtn>
                @else
                <span
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium cursor-not-allowed text-slate-300 dark:text-slate-600"
                    disabled>
                    {{ __('Next') }} <i class="fi fi-rr-angle-right flex pl-2"></i>
                </span>
                @endif
            </span>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div class="">
                <p class="text-sm leading-5 text-slate-500 dark:text-slate-400">
                    <span>{!! __('Showing') !!}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                    <span>{!! __('to') !!}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                    <span>{!! __('of') !!}</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span>
                    <span>{!! __('results') !!}</span>
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex items-center gap-1">
                    <span>
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 cursor-not-allowed text-slate-300 dark:text-slate-600"
                                disabled aria-hidden="true">
                                <i class="flex fi fi-rr-angle-left"></i>
                            </span>
                        </span>
                        @else
                        <x-actionbtn wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                            rel="prev"
                            class="w-8 h-8"
                            aria-label="{{ __('pagination.previous') }}"><i class="flex fi fi-rr-angle-left"></i>
                        </x-actionbtn>
                        @endif
                    </span>

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                    <span aria-disabled="true">
                        <span
                            class="flex items-center h-8 px-1 text-slate-400"><i
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
                            <span class="flex items-center justify-center min-w-[2rem] h-8 px-2 text-sm font-semibold text-white rounded-lg shadow-sm bg-brand-600">{{ $page }}</span>
                        </span>
                        @else
                        <x-actionbtn wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                            class="min-w-[2rem] h-8 px-2 text-sm font-medium"
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
                            class="w-8 h-8"
                            aria-label="{{ __('pagination.next') }}"><i class="flex fi fi-rr-angle-right"></i>
                        </x-actionbtn>
                        @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 cursor-not-allowed text-slate-300 dark:text-slate-600"
                                disabled aria-hidden="true">
                                <i class="flex fi fi-rr-angle-right"></i>
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