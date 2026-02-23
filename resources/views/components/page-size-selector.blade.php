@props([
    'perPage' => 15,
    'autoSubmit' => false
])

<select
    name="per_page"
    class="w-auto rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
    data-role="selector"
    @if($autoSubmit) onchange="this.form.submit()" @endif
>
    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
    <option value="all" {{ $perPage == 'all' || $perPage >= 10000 ? 'selected' : '' }}>Semua</option>
</select>