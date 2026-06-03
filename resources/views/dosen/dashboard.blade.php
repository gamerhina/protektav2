@extends('layouts.app')

@section('title', 'Dosen Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="py-4 md:py-6">
        <!-- Header & Greeting -->
        <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-blue-600 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-chalkboard-teacher text-sm"></i>
                    </span>
                    <span>Dashboard • Dosen</span>
                </p>
                <h1 class="mt-2 text-2xl md:text-3xl font-bold tracking-tight text-slate-900">
                    Selamat datang, <span class="text-blue-600">{{ auth()->user() ? (auth()->user()->nama ?? auth()->user()->name) : 'Dosen' }}</span>
                </h1>
                <p class="mt-1 text-sm md:text-base text-slate-500">Pantau ringkasan aktivitas bimbingan dan evaluasi seminar Anda.</p>
            </div>
        </div>

        <!-- Stats & Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500/90 to-emerald-600/90 text-white shadow-lg border border-emerald-300/40 backdrop-blur-md">
                <div class="absolute -right-6 -top-6 opacity-20 text-6xl">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="px-4 py-3 md:px-4 md:py-3 space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-100">Seminar Ditinjau</p>
                    <p class="text-3xl md:text-4xl font-bold leading-tight">{{ $seminarDitinjauCount }}</p>
                    <p class="text-xs md:text-sm text-emerald-100">Total seminar yang pernah Anda tinjau.</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500/90 to-indigo-600/90 text-white shadow-lg border border-indigo-300/40 backdrop-blur-md">
                <div class="absolute -right-6 -top-6 opacity-20 text-6xl">
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <div class="px-4 py-3 md:px-4 md:py-3 space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-indigo-100">Nilai Diberikan</p>
                    <p class="text-3xl md:text-4xl font-bold leading-tight">{{ $nilaidiberikanCount }}</p>
                    <p class="text-xs md:text-sm text-indigo-100">Total penilaian yang sudah Anda input.</p>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500/90 to-blue-600/90 text-white shadow-lg border border-blue-300/40 backdrop-blur-md">
                <div class="absolute -right-6 -top-6 opacity-20 text-6xl">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="px-4 py-3 md:px-4 md:py-3 space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-blue-100">Bimbingan Akad. (PA)</p>
                    <p class="text-3xl md:text-4xl font-bold leading-tight">{{ $mahasiswaBimbinganAkademikCount }}</p>
                    <p class="text-xs md:text-sm text-blue-100">Total mahasiswa bimbingan akademik Anda.</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6 mb-8">
            <a href="{{ route('dosen.evaluasi.index') }}" class="group relative overflow-hidden rounded-2xl border border-blue-100/70 bg-gradient-to-br from-blue-50/80 to-indigo-50/80 p-4 md:p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 backdrop-blur-md transition">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-blue-500/10 group-hover:bg-blue-500/20 transition"></div>
                <div class="relative flex items-start gap-3">
                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-2xl bg-blue-500/90 text-white shadow-md backdrop-blur-md">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="flex-1 bg-white/60 md:bg-transparent rounded-xl md:rounded-none px-3 py-2 md:px-0 md:py-0 backdrop-blur-md md:backdrop-blur-0">
                        <h3 class="text-sm md:text-base font-semibold text-slate-900">Tugas Evaluasi</h3>
                        <p class="mt-1 text-xs md:text-sm text-slate-500">Lihat daftar lengkap seminar yang menunggu penilaian Anda.</p>
                        <div class="mt-3 inline-flex items-center text-xs font-semibold text-blue-700 group-hover:translate-x-1 transition bg-blue-50/70 md:bg-transparent px-3 py-1 rounded-full md:px-0 md:py-0 md:rounded-none backdrop-blur-md md:backdrop-blur-0">
                            Kelola evaluasi
                            <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('dosen.manage-seminar.index') }}" class="group relative overflow-hidden rounded-2xl border border-emerald-100/70 bg-gradient-to-br from-emerald-50/80 to-teal-50/80 p-4 md:p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 backdrop-blur-md transition">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-500/10 group-hover:bg-emerald-500/20 transition"></div>
                <div class="relative flex items-start gap-3">
                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-2xl bg-emerald-500/90 text-white shadow-md backdrop-blur-md">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="flex-1 bg-white/60 md:bg-transparent rounded-xl md:rounded-none px-3 py-2 md:px-0 md:py-0 backdrop-blur-md md:backdrop-blur-0">
                        <h3 class="text-sm md:text-base font-semibold text-slate-900">Seminar Selesai</h3>
                        <p class="mt-1 text-xs md:text-sm text-slate-500">Pantau seminar yang telah selesai beserta status penilaiannya.</p>
                        <div class="mt-3 inline-flex items-center text-xs font-semibold text-emerald-700 group-hover:translate-x-1 transition bg-emerald-50/70 md:bg-transparent px-3 py-1 rounded-full md:px-0 md:py-0 md:rounded-none backdrop-blur-md md:backdrop-blur-0">
                            Lihat riwayat
                            <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.approval.stamping.index') }}" class="group relative overflow-hidden rounded-2xl border border-orange-100/70 bg-gradient-to-br from-orange-50/80 to-amber-50/80 p-4 md:p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 backdrop-blur-md transition">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-orange-500/10 group-hover:bg-orange-500/20 transition"></div>
                <div class="relative flex items-start gap-3">
                    <div class="inline-flex items-center justify-center w-9 h-9 rounded-2xl bg-orange-500/90 text-white shadow-md backdrop-blur-md">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="flex-1 bg-white/60 md:bg-transparent rounded-xl md:rounded-none px-3 py-2 md:px-0 md:py-0 backdrop-blur-md md:backdrop-blur-0">
                        <h3 class="text-sm md:text-base font-semibold text-slate-900">Pembubuhan TTD</h3>
                        <p class="mt-1 text-xs md:text-sm text-slate-500">Bubuhi tanda tangan QR Code pada dokumen surat yang telah disetujui.</p>
                        <div class="mt-3 inline-flex items-center text-xs font-semibold text-orange-700 group-hover:translate-x-1 transition bg-orange-50/70 md:bg-transparent px-3 py-1 rounded-full md:px-0 md:py-0 md:rounded-none backdrop-blur-md md:backdrop-blur-0">
                            Bubuhi sekarang
                            <i class="fas fa-arrow-right ml-2"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tugas Evaluasi Preview -->
        <div id="tugas-evaluasi" class="mb-4 md:mb-0">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600">
                            <i class="fas fa-clock text-xs"></i>
                        </span>
                        <span>Tugas Evaluasi Terbaru</span>
                    </h2>
                    <p class="mt-1 text-xs md:text-sm text-slate-500">Daftar singkat seminar yang paling dekat atau baru diajukan.</p>
                </div>
            </div>
            
            @php
                $defaultSort = 'tanggal';
                $defaultDirection = 'desc';
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
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nama mahasiswa, judul, jenis, atau status)..."
                                       class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                    <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 5) }}">
                </div>
            </form>

            <div class="bg-white border border-gray-100 rounded-2xl p-4 md:p-6 shadow-sm w-full min-w-0 max-w-full">
                    @if($evalSeminars->count() > 0)
                        <div class="overflow-x-auto w-full max-w-full">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <x-sortable-th column="mahasiswa" label="Mahasiswa" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <x-sortable-th column="jenis" label="Jenis" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <x-sortable-th column="tanggal" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($evalSeminars as $seminar)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $seminar->mahasiswa->nama ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $seminar->seminarJenis->nama ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="space-y-1">
                                                <span class="px-2.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                                    @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                                    @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                                    @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                                    @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                                    @endif">
                                                    @if($seminar->status == 'belum_lengkap')
                                                        Belum Lengkap
                                                    @else
                                                        {{ ucfirst($seminar->status) }}
                                                    @endif
                                                </span>

                                                @php
                                                    $evaluatorType = null;
                                                    if ($seminar->p1_dosen_id == auth()->guard('dosen')->id()) {
                                                        $evaluatorType = 'p1';
                                                    } elseif ($seminar->p2_dosen_id == auth()->guard('dosen')->id()) {
                                                        $evaluatorType = 'p2';
                                                    } elseif ($seminar->pembahas_dosen_id == auth()->guard('dosen')->id()) {
                                                        $evaluatorType = 'pembahas';
                                                    }

                                                    $nilai = $seminar->nilai->firstWhere('dosen_id', auth()->guard('dosen')->id());
                                                    $signature = $seminar->signatures->firstWhere('jenis_penilai', $evaluatorType);
                                                @endphp

                                                <div class="flex flex-col gap-1 mt-2">
                                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full inline-flex w-fit items-center gap-1
                                                        @if($nilai) bg-blue-100 text-blue-700
                                                        @else bg-gray-100 text-gray-500
                                                        @endif">
                                                        <i class="fas fa-star text-[10px]"></i>
                                                        <span>Nilai: {{ $nilai ? 'Sudah' : 'Belum' }}</span>
                                                    </span>

                                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full inline-flex w-fit items-center gap-1
                                                        @if($signature) bg-green-100 text-green-700
                                                        @else bg-gray-100 text-gray-500
                                                        @endif">
                                                        <i class="fas fa-signature text-[10px]"></i>
                                                        <span>TTD: {{ $signature ? 'Sudah' : 'Belum' }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center gap-3">
                                                <!-- Input Nilai Button -->
                                                <a
                                                    href="{{ route('dosen.nilai.input', ['seminar' => $seminar->id]) }}"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 shadow-sm"
                                                    title="Input nilai seminar"
                                                >
                                                    <i class="fas fa-pen-to-square text-base"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <form method="GET" class="w-full md:w-auto overflow-hidden">
                                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                                <input type="hidden" name="page" value="1">
                                @include('components.page-size-selector', ['perPage' => $perPage ?? 5, 'autoSubmit' => true])
                            </form>
                            <div class="w-full md:w-auto overflow-x-auto pb-2">
                                {{ $evalSeminars->links('components.pagination') }}
                            </div>
                        </div>
                    @else
                        <p class="text-gray-600">Anda tidak memiliki tugas evaluasi yang tertunda.</p>
                    @endif
                </div>
            </div>

        </div>

        <!-- Lulus Tepat Waktu Component -->
        <div class="mt-8">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600">
                    <i class="fas fa-user-graduate text-xs"></i>
                </span>
                <span>Bimbingan Akademik & Lulus Tepat Waktu</span>
            </h2>
            <p class="mt-1 text-xs md:text-sm text-slate-500">Pantau dan kelola estimasi kelulusan tepat waktu untuk mahasiswa bimbingan akademik Anda.</p>
            
            @if(session('success'))
                <div class="mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <x-lulus-tepat-waktu-section 
                :tepat-waktu-count="$tepatWaktuCount"
                :tidak-tepat-waktu-count="$tidakTepatWaktuCount"
                :ongoing-tepat-waktu-count="$ongoingTepatWaktuCount"
                :ongoing-overdue-count="$ongoingOverdueCount"
                :students-paginated="$studentsPaginated"
                :search-mhs="$searchMhs"
                update-route-prefix="dosen"
            />
        </div>
    </div>
</div>
@endsection
