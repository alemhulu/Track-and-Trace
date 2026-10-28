{{-- Link-based twin of vendor/livewire/tailwind.blade.php, so controller pages paginate like Livewire tables. --}}
<div class="w-full border-t border-slate-100 dark:border-slate-800">
    @if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
        class="flex items-center justify-between px-5 py-3">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium cursor-not-allowed text-slate-300 dark:text-slate-600">
                <i class="flex pr-2 fi fi-rr-angle-left"></i> {{ __('Previous') }}
            </span>
            @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:text-brand-700 hover:bg-brand-50 transition dark:text-slate-300 dark:hover:bg-slate-800">
                <i class="flex fi fi-rr-angle-left"></i>{{ __('Previous') }}
            </a>
            @endif

            @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:text-brand-700 hover:bg-brand-50 transition dark:text-slate-300 dark:hover:bg-slate-800">
                {{ __('Next') }}<i class="flex fi fi-rr-angle-right"></i>
            </a>
            @else
            <span class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium cursor-not-allowed text-slate-300 dark:text-slate-600">
                {{ __('Next') }} <i class="flex pl-2 fi fi-rr-angle-right"></i>
            </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <p class="text-sm leading-5 text-slate-500 dark:text-slate-400">
                <span>{!! __('Showing') !!}</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                <span>{!! __('to') !!}</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                <span>{!! __('of') !!}</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $paginator->total() }}</span>
                <span>{!! __('results') !!}</span>
            </p>

            <span class="relative z-0 inline-flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                    class="inline-flex items-center justify-center w-8 h-8 cursor-not-allowed text-slate-300 dark:text-slate-600">
                    <i class="flex fi fi-rr-angle-left" aria-hidden="true"></i>
                </span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 hover:text-brand-700 hover:bg-brand-50 transition dark:text-slate-300 dark:hover:bg-slate-800">
                    <i class="flex fi fi-rr-angle-left"></i>
                </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <span aria-disabled="true" class="flex items-center h-8 px-1 text-slate-400"><i class="flex fi fi-rr-menu-dots"></i></span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span aria-current="page"
                    class="flex items-center justify-center min-w-[2rem] h-8 px-2 text-sm font-semibold text-white rounded-lg shadow-sm bg-brand-600">{{ $page }}</span>
                @else
                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                    class="inline-flex items-center justify-center min-w-[2rem] h-8 px-2 text-sm font-medium rounded-lg text-slate-600 hover:text-brand-700 hover:bg-brand-50 transition dark:text-slate-300 dark:hover:bg-slate-800">
                    {{ $page }}
                </a>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 hover:text-brand-700 hover:bg-brand-50 transition dark:text-slate-300 dark:hover:bg-slate-800">
                    <i class="flex fi fi-rr-angle-right"></i>
                </a>
                @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                    class="inline-flex items-center justify-center w-8 h-8 cursor-not-allowed text-slate-300 dark:text-slate-600">
                    <i class="flex fi fi-rr-angle-right" aria-hidden="true"></i>
                </span>
                @endif
            </span>
        </div>
    </nav>
    @endif
</div>
