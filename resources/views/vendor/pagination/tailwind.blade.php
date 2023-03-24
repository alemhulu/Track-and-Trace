@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
    class=" border-t border-light/30 dark:border-gray/40 pt-3  px-7 flex items-center justify-between">
    <div class="flex justify-between flex-1 sm:hidden">
        @if ($paginator->onFirstPage())
        <span
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
            {!! __('pagination.previous') !!}
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}"
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
            {!! __('pagination.previous') !!}
        </a>
        @endif

        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
            class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
            {!! __('pagination.next') !!}
        </a>
        @else
        <span
            class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
            {!! __('pagination.next') !!}
        </span>
        @endif
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700 dark:text-white leading-5">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                @else
                {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-medium">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>
        </div>

        <div>
            <span class="relative z-0 inline-flex rounded-md gap-x-2">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span
                        class="hover:bg-gray-200 hover:border-gray-300 flex h-8 w-8 items-center justify-center rounded-md border border-[#EDEFF1] text-base text-[#838995] hover:text-gray-500 cursor-not-allowed ransition ease-in-out duration-150 bg-gray-100"
                        aria-hidden="true">
                        <span>
                            <svg width="8" height="15" viewBox="0 0 8 15" fill="currentColor">
                                <path
                                    d="M7.12979 1.91389L7.1299 1.914L7.1344 1.90875C7.31476 1.69833 7.31528 1.36878 7.1047 1.15819C7.01062 1.06412 6.86296 1.00488 6.73613 1.00488C6.57736 1.00488 6.4537 1.07206 6.34569 1.18007L6.34564 1.18001L6.34229 1.18358L0.830207 7.06752C0.830152 7.06757 0.830098 7.06763 0.830043 7.06769C0.402311 7.52078 0.406126 8.26524 0.827473 8.73615L0.827439 8.73618L0.829982 8.73889L6.34248 14.6014L6.34243 14.6014L6.34569 14.6047C6.546 14.805 6.88221 14.8491 7.1047 14.6266C7.30447 14.4268 7.34883 14.0918 7.12833 13.8693L1.62078 8.01209C1.55579 7.93114 1.56859 7.82519 1.61408 7.7797L1.61413 7.77975L1.61729 7.77639L7.12979 1.91389Z"
                                    stroke-width="0.3"></path>
                            </svg>
                        </span>
                    </span>
                </span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="relative inline-flex items-center justify-center h-8 w-8 text-sm font-medium text-gray bg-white border border-[#EDEFF1] leading-5 hover:text-white hover:bg-blue-500 focus:z-10 focus:outline-none focus:ring ring-gray-200 focus:border-blue-500 active:bg-blue-500 active:text-blue transition ease-in-out duration-150 rounded-md hover:border-blue-600 group"
                    aria-label="{{ __('pagination.previous') }}">
                    <span>
                        <svg width="8" height="15" viewBox="0 0 8 15" fill="currentColor">
                            <path
                                d="M7.12979 1.91389L7.1299 1.914L7.1344 1.90875C7.31476 1.69833 7.31528 1.36878 7.1047 1.15819C7.01062 1.06412 6.86296 1.00488 6.73613 1.00488C6.57736 1.00488 6.4537 1.07206 6.34569 1.18007L6.34564 1.18001L6.34229 1.18358L0.830207 7.06752C0.830152 7.06757 0.830098 7.06763 0.830043 7.06769C0.402311 7.52078 0.406126 8.26524 0.827473 8.73615L0.827439 8.73618L0.829982 8.73889L6.34248 14.6014L6.34243 14.6014L6.34569 14.6047C6.546 14.805 6.88221 14.8491 7.1047 14.6266C7.30447 14.4268 7.34883 14.0918 7.12833 13.8693L1.62078 8.01209C1.55579 7.93114 1.56859 7.82519 1.61408 7.7797L1.61413 7.77975L1.61729 7.77639L7.12979 1.91389Z"
                                stroke-width="0.3"></path>
                        </svg>
                    </span>
                </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <span aria-disabled="true">
                    <span
                        class="relative inline-flex items-center justify-center h-8 w-8 text-sm font-medium text-gray bg-white border border-[#EDEFF1] leading-5 hover:text-white hover:bg-blue-400 focus:z-10 focus:outline-none focus:ring ring-gray-200 focus:border-blue-500 active:bg-blue-500 active:text-blue-500 transition ease-in-out duration-150 rounded-md hover:border-blue-500 group">{{ $element }}</span>
                </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <span aria-current="page">
                    <span
                        class="relative inline-flex items-center justify-center h-8 w-8 text-sm font-medium  border border-[#EDEFF1] leading-5 text-white bg-blue-500 focus:z-10 focus:outline-none focus:ring ring-blue-600 focus:border-blue-500 active:bg-blue-599 active:text-blue-50 transition ease-in-out duration-150 rounded-md hover:border-blue-500 group">{{ $page }}</span>
                </span>
                @else
                <a href="{{ $url }}"
                    class="relative inline-flex items-center justify-center h-8 w-8 text-sm font-medium text-gray bg-white border border-[#EDEFF1] leading-5 hover:text-white hover:bg-blue-500 focus:z-10 focus:outline-none focus:ring ring-gray-200 focus:border-blue-600 active:bg-blue-500 active:text-blue-50 transition ease-in-out duration-150 rounded-md hover:border-blue-600 group"
                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                    {{ $page }}
                </a>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="relative inline-flex items-center justify-center h-8 w-8 text-sm font-medium text-gray bg-white border border-[#EDEFF1] leading-5 hover:text-white hover:bg-blue-500 focus:z-10 focus:outline-none focus:ring ring-gray-200 focus:border-blue-600 active:bg-blue-500 active:text-blue-50 transition ease-in-out duration-150 rounded-md hover:border-blue group"
                    aria-label="{{ __('pagination.next') }}">
                    <span>
                        <svg width="8" height="15" viewBox="0 0 8 15" fill="currentColor">
                            <path
                                d="M0.870212 13.0861L0.870097 13.086L0.865602 13.0912C0.685237 13.3017 0.684716 13.6312 0.895299 13.8418C0.989374 13.9359 1.13704 13.9951 1.26387 13.9951C1.42264 13.9951 1.5463 13.9279 1.65431 13.8199L1.65436 13.82L1.65771 13.8164L7.16979 7.93248C7.16985 7.93243 7.1699 7.93237 7.16996 7.93231C7.59769 7.47923 7.59387 6.73477 7.17253 6.26385L7.17256 6.26382L7.17002 6.26111L1.65752 0.398611L1.65757 0.398563L1.65431 0.395299C1.454 0.194997 1.11779 0.150934 0.895299 0.373424C0.695526 0.573197 0.651169 0.908167 0.871667 1.13067L6.37922 6.98791C6.4442 7.06886 6.43141 7.17481 6.38592 7.2203L6.38587 7.22025L6.38271 7.22361L0.870212 13.0861Z"
                                stroke-width="0.3"></path>
                        </svg>
                    </span>
                </a>
                @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span
                        class="hover:bg-gray-200 hover:border-gray-300 flex h-8 w-8 items-center justify-center rounded-md border border-[#EDEFF1] text-base text-[#838995] hover:text-gray-600 cursor-not-allowed ransition ease-in-out duration-150 bg-gray-100"
                        aria-hidden="true">
                        <span>
                            <svg width="8" height="15" viewBox="0 0 8 15" fill="currentColor">
                                <path
                                    d="M0.870212 13.0861L0.870097 13.086L0.865602 13.0912C0.685237 13.3017 0.684716 13.6312 0.895299 13.8418C0.989374 13.9359 1.13704 13.9951 1.26387 13.9951C1.42264 13.9951 1.5463 13.9279 1.65431 13.8199L1.65436 13.82L1.65771 13.8164L7.16979 7.93248C7.16985 7.93243 7.1699 7.93237 7.16996 7.93231C7.59769 7.47923 7.59387 6.73477 7.17253 6.26385L7.17256 6.26382L7.17002 6.26111L1.65752 0.398611L1.65757 0.398563L1.65431 0.395299C1.454 0.194997 1.11779 0.150934 0.895299 0.373424C0.695526 0.573197 0.651169 0.908167 0.871667 1.13067L6.37922 6.98791C6.4442 7.06886 6.43141 7.17481 6.38592 7.2203L6.38587 7.22025L6.38271 7.22361L0.870212 13.0861Z"
                                    stroke-width="0.3"></path>
                            </svg>
                        </span>
                    </span>
                </span>
                @endif
            </span>
        </div>
    </div>
</nav>
@endif