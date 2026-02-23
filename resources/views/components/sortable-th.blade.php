@props([
    'column',
    'label',
    'defaultSort' => null,
    'defaultDirection' => 'asc',
    'align' => 'left',
])

@php
$resolvedDefaultSort = $defaultSort ?? $column;
$currentSort = request('sort', $resolvedDefaultSort);
$currentDirection = request('direction', $defaultDirection);
$isActive = $currentSort === $column;
$nextDirection = $isActive
    ? ($currentDirection === 'asc' ? 'desc' : 'asc')
    : $defaultDirection;
$url = request()->fullUrlWithQuery([
    'sort' => $column,
    'direction' => $nextDirection,
    'page' => 1,
]);
$iconPath = match (true) {
    !$isActive => 'M7 10l5-5 5 5M7 14l5 5 5-5',
    $currentDirection === 'asc' => 'M5 15l7-7 7 7',
    default => 'M19 9l-7 7-7-7',
};
$textClasses = $isActive ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700';
@endphp

<th class="px-6 py-3 text-{{ $align }} uppercase bg-gray-50">
    <a href="{{ $url }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.2em] transition-colors {{ $textClasses }}">
        <span>{{ $label }}</span>
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
        </svg>
    </a>
</th>
