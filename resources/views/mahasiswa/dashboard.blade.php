@extends('layouts.app')

@section('title', 'Mahasiswa Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="py-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Mahasiswa Dashboard</h1>
            @php
                $mahasiswaUser = auth()->guard('mahasiswa')->user();
            @endphp
            <p class="text-gray-600">Welcome back, <strong>{{ $mahasiswaUser ? ($mahasiswaUser->nama ?? $mahasiswaUser->name) : 'Mahasiswa' }}</strong>!</p>
            <div class="flex flex-col sm:flex-row sm:gap-6 mt-1">
                <p class="text-gray-600"><span class="font-medium text-gray-500 uppercase text-[10px] tracking-wider block">NPM</span> {{ $mahasiswaUser ? ($mahasiswaUser->npm ?? 'N/A') : 'N/A' }}</p>
                <p class="text-gray-600"><span class="font-medium text-gray-500 uppercase text-[10px] tracking-wider block">Pembimbing Akademik</span> {{ $mahasiswaUser && $mahasiswaUser->pembimbingAkademik ? $mahasiswaUser->pembimbingAkademik->nama : 'Belum Ditentukan' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 mt-6">
                <!-- Stats -->
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                    <h3 class="text-lg font-medium text-blue-800">Seminar Terdaftar</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ auth()->user() ? (auth()->user()->seminars ?? collect())->count() : 0 }}</p>
                </div>

                <div class="bg-green-50 p-6 rounded-lg border border-green-100">
                    <h3 class="text-lg font-medium text-green-800">Status Terakhir</h3>
                    @php
                        $lastStatus = auth()->user() ? (optional((auth()->user()->seminars ?? collect())->last())->status ?? null) : null;
                        $lastStatusLabel = $lastStatus ? ucwords(str_replace('_', ' ', $lastStatus)) : 'N/A';
                    @endphp
                    <p class="text-3xl font-bold text-green-600">{{ $lastStatusLabel }}</p>
                </div>

                <div class="bg-purple-50 p-6 rounded-lg border border-purple-100">
                    <h3 class="text-lg font-medium text-purple-800">Nilai Tertinggi</h3>
                    @php
                        $maxAverage = auth()->user() ? (auth()->user()->seminars->map(function($seminar) {
                            return $seminar->calculateWeightedScore();
                        })->max() ?: 'N/A') : 'N/A';
                        $maxAverageDisplay = is_numeric($maxAverage) ? number_format($maxAverage, 2) : $maxAverage;
                    @endphp
                    <p class="text-3xl font-bold text-purple-600">{{ $maxAverageDisplay }}</p>
                </div>
            </div>

            <!-- My Seminars -->
            <div class="mb-8" id="seminar-saya">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Daftar Seminar Saya</h2>
                @php
                    $mahasiswa = auth()->guard('mahasiswa')->user();
                    $search = trim(request('search', ''));
                    $perPage = \App\Support\PaginationHelper::resolvePerPage(request(), 10);

                    $sortFields = [
                        'tanggal' => 'tanggal',
                        'status' => 'status',
                        'created_at' => 'created_at',
                    ];

                    $defaultSort = 'tanggal';
                    $defaultDirection = 'desc';

                    $sort = request('sort', $defaultSort);
                    if (!array_key_exists($sort, $sortFields)) {
                        $sort = $defaultSort;
                    }

                    $direction = strtolower(request('direction', $defaultDirection)) === 'asc' ? 'asc' : 'desc';

                    $seminarQuery = \App\Models\Seminar::with(['seminarJenis', 'nilai'])
                        ->where('mahasiswa_id', optional($mahasiswa)->id);

                    if ($search !== '') {
                        $like = "%{$search}%";
                        $seminarQuery->where(function ($query) use ($like) {
                            $query->where('judul', 'like', $like)
                                ->orWhereHas('seminarJenis', function ($q) use ($like) {
                                    $q->where('nama', 'like', $like);
                                })
                                ->orWhere('status', 'like', $like);
                        });
                    }

                    $mySeminars = $seminarQuery
                        ->orderBy($sortFields[$sort], $direction)
                        ->paginate($perPage)
                        ->withQueryString();
                @endphp

                <form method="GET" class="mb-4">
                    <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                        <div class="grid gap-4">
                            <div>
                                <label for="search" class="text-sm font-medium text-gray-600">Cari Seminar</label>
                                <div class="relative mt-1">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Judul, jenis, atau status)..."
                                            class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                        <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', $perPage) }}">
                    </div>
                </form>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    @if($mySeminars->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 w-[220px]">Jenis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Judul</th>
                                        <x-sortable-th column="tanggal" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">Nilai</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 w-24 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($mySeminars as $seminar)
                                    <tr>
                                        <td class="px-6 py-4 w-[220px] text-sm text-gray-600">
                                            <div class="text-gray-900 break-words whitespace-normal">
                                                {{ optional($seminar->seminarJenis)->nama ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="font-medium text-gray-900 max-w-[200px] sm:max-w-xs truncate" title="{{ strip_tags($seminar->judul) }}">{!! strip_tags($seminar->judul, '<b><i><u><strong><em>') !!}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                                @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                                @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                                @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                                @endif">
                                                {{ ucwords(str_replace('_', ' ', $seminar->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 text-center">
                                            @php
                                                $weightedScore = $seminar->calculateWeightedScore();
                                            @endphp
                                            @if($weightedScore > 0)
                                                {{ number_format($weightedScore, 2) }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <a href="{{ route('mahasiswa.seminar.show', $seminar) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Lihat Detail">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-600">Anda belum mendaftar seminar apapun.</p>
                    @endif
                </div>

                @if($mySeminars->count() > 0)
                    <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <form method="GET" class="w-full md:w-auto">
                            @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                            <input type="hidden" name="page" value="1">
                            @include('components.page-size-selector', ['perPage' => $perPage, 'autoSubmit' => true])
                        </form>
                        <div class="w-full md:w-auto">
                            {{ $mySeminars->links('components.pagination') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Tindakan Cepat</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('mahasiswa.seminar.register') }}" class="bg-white border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <i class="fas fa-plus-circle text-2xl text-blue-500 mb-2"></i>
                        <p class="font-medium">Daftar Seminar</p>
                    </a>

                    <a href="{{ route('mahasiswa.profile.edit') }}" class="bg-white border border-gray-200 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
                        <i class="fas fa-user text-2xl text-green-500 mb-2"></i>
                        <p class="font-medium">Profil Saya</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
