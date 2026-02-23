@extends('layouts.app')

@section('title', 'Dashboard Approval')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Dashboard Approval</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if($isAdmin)
                        Kelola antrian proses persetujuan dan tanda tangan
                    @else
                        Daftar surat yang menunggu proses persetujuan Anda
                    @endif
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 py-3 px-4 rounded-xl mb-6 flex items-center gap-3 border border-emerald-100 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <div>
                    <span class="font-bold">Berhasil!</span> {{ session('success') }}
                </div>
                <button type="button" class="ml-auto text-emerald-500 hover:text-emerald-700" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @php
            $defaultSort = 'created_at';
            $defaultDirection = 'desc';
        @endphp

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4">
                    <div>
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Surat</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nomor, Pemohon, Jenis, Status, atau Perihal)..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <x-sortable-th column="no_surat" label="Nomor Surat" :default-sort="$defaultSort" :default-direction="$defaultDirection" class="w-1/5" />
                        <x-sortable-th column="pemohon" label="Pemohon" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="surat_jenis_id" label="Jenis Layanan" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="tanggal_surat" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" class="w-40" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($surats as $item)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800 break-all">
                             {{ $item->no_surat ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <div>
                                <div class="font-bold text-gray-900 leading-tight">
                                    {{ $item->pemohonDosen->nama ?? $item->pemohonMahasiswa->nama ?? $item->pemohonAdmin->nama ?? 'Unknown' }}
                                </div>
                                <div class="text-[10px] uppercase font-bold tracking-wider mt-0.5">
                                    @if($item->pemohon_type === 'admin')
                                        <span class="text-indigo-600">Admin</span>
                                    @elseif($item->pemohon_type === 'mahasiswa')
                                        <span class="text-gray-400">Mahasiswa</span> <span class="text-gray-300">/</span> <span class="text-gray-500">{{ $item->pemohonMahasiswa->npm ?? '-' }}</span>
                                    @elseif($item->pemohon_type === 'dosen')
                                        <span class="text-gray-400">Dosen</span> <span class="text-gray-300">/</span> <span class="text-gray-500">{{ $item->pemohonDosen->nip ?? '-' }}</span>
                                    @else
                                        <span class="text-gray-400">{{ $item->pemohon_type }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->jenis->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->tanggal_surat?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($isAdmin)
                                @php
                                    $stLabel = match($item->approval_status ?? $item->status) {
                                        'diajukan', 'submitted' => 'Menunggu Review',
                                        'diproses', 'approved_by_admin' => 'Proses Approval',
                                        'approved_by_pimpinan' => 'Siap TTD/Kirim',
                                        'dikirim', 'completed', 'approved' => 'Selesai',
                                        'ditolak', 'rejected' => 'Ditolak',
                                        default => ucfirst($item->approval_status ?? $item->status)
                                    };
                                    $stClass = match($item->approval_status ?? $item->status) {
                                        'diajukan', 'submitted' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                        'diproses', 'approved_by_admin' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                                        'approved_by_pimpinan' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                        'dikirim', 'completed', 'approved' => 'bg-green-50 text-green-700 ring-1 ring-green-200',
                                        'ditolak', 'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                        default => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex font-semibold rounded-full text-xs px-3 py-1 {{ $stClass }}">
                                    {{ $stLabel }}
                                </span>
                            @else
                                {{-- For Approver --}}
                                @php
                                    $myApproval = $item->approvals->first();
                                    $appStatus = $myApproval ? $myApproval->status : null;
                                    $suratStatus = $item->status;

                                    // Prioritize surat-level status for ditolak/selesai
                                    if ($suratStatus === 'ditolak') {
                                        $stLabelApp = 'Ditolak';
                                        $stClassApp = 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
                                    } elseif ($suratStatus === 'selesai') {
                                        $stLabelApp = 'Selesai';
                                        $stClassApp = 'bg-green-50 text-green-700 ring-1 ring-green-200';
                                    } elseif ($appStatus === 'pending' && $myApproval && !$myApproval->isReady()) {
                                        $stLabelApp = 'Menunggu Urutan Sebelumnya';
                                        $stClassApp = 'bg-amber-50 text-amber-500 ring-1 ring-amber-200';
                                    } else {
                                        $stLabelApp = match($appStatus) {
                                            'pending' => 'Menunggu Anda',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            default => 'Menunggu Review'
                                        };
                                        $stClassApp = match($appStatus) {
                                            'pending' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                            'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                            'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                            default => 'bg-amber-50 text-amber-500 ring-1 ring-amber-200'
                                        };
                                    }
                                @endphp
                                <span class="inline-flex font-semibold rounded-full text-[11px] px-3 py-1 {{ $stClassApp }}">
                                    {{ $stLabelApp }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            @php
                                $processUrl = '#';
                                if ($isAdmin) {
                                    $processUrl = route('admin.surat.show', $item);
                                } else {
                                    // Provide link to specific approval if available
                                    $myApproval = $item->approvals->first(); 
                                    if ($myApproval) {
                                        $processUrl = route('admin.approval.show', $myApproval);
                                    }
                                }
                            @endphp
                            
                            <div class="flex items-center gap-3">
                                @if(Route::has('admin.surat.approval-history'))
                                    <a href="{{ route('admin.surat.approval-history', $item) }}" class="hover:scale-110 transition-transform" title="Riwayat" style="color: #10b981 !important;">
                                        <i class="fas fa-history fa-fw"></i>
                                    </a>
                                @endif
                                
                                @if($isAdmin)
                                    <a href="{{ $processUrl }}" class="hover:scale-110 transition-transform" title="Detail & Proses" style="color: #2563eb !important;">
                                        <i class="fas fa-arrow-right fa-fw"></i>
                                    </a>
                                @else
                                    <a href="{{ $processUrl }}" class="hover:scale-110 transition-transform" title="Lihat Detail Surat" style="color: #2563eb !important;">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-check-double text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada antrian surat.</p>
                                    <p class="text-xs text-gray-400 mt-1">Semua pekerjaan telah selesai!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => request('per_page', 20), 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $surats->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
