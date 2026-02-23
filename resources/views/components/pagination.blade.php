@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <ul class="flex flex-wrap items-center justify-center gap-1.5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="px-2.5 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed transition" aria-hidden="true">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-2.5 py-1.5 text-sm rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition" rel="prev">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements with Limited Range (2 pages max on each side) --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();

                // Only show 2 pages on each side of current page
                $start = max(1, $currentPage - 1); // 1 before current
                $end = min($lastPage, $currentPage + 2); // 2 after current
            @endphp

            {{-- First page and ellipsis if needed --}}
            @if ($start > 1)
                <li class="page-item">
                    <a href="{{ $paginator->url(1) }}" class="px-2.5 py-1.5 text-sm rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition">1</a>
                </li>
                @if ($start > 2)
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="px-2.5 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 transition">...</span>
                    </li>
                @endif
            @endif

            {{-- Page links in the range (max 4 total: 2 before + current + 1 after) --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active" aria-current="page">
                        <span class="px-2.5 py-1.5 text-sm rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold transition">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a href="{{ $paginator->url($page) }}" class="px-2.5 py-1.5 text-sm rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last page and ellipsis if needed --}}
            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="px-2.5 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 transition">...</span>
                    </li>
                @endif
                <li class="page-item">
                    <a href="{{ $paginator->url($lastPage) }}" class="px-2.5 py-1.5 text-sm rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition">{{ $lastPage }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-2.5 py-1.5 text-sm rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition" rel="next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="px-2.5 py-1.5 text-sm rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed transition" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif