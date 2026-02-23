@extends('layouts.app')

@section('title', 'Permohonan Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Kelola Surat / Riwayat Permohonan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Daftar permohonan surat Anda dan status pengerjaannya.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dosen.surat.create') }}" class="btn-pill btn-pill-primary inline-flex items-center gap-2 !no-underline shadow-lg shadow-indigo-100">
                    <i class="fas fa-plus"></i> Buat Surat
                </a>
            </div>
        </div>

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
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nomor, Tujuan, Perihal, Jenis)..."
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
                        <x-sortable-th column="surat_jenis_id" label="Jenis Layanan" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="tanggal_surat" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" class="w-40" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($items as $s)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800 break-all">
                             {{ $s->no_surat ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                            {{ $s->jenis->nama ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $s->tanggal_surat ? $s->tanggal_surat->timezone('Asia/Jakarta')->translatedFormat('d M Y') : ($s->created_at ? $s->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : '-') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = match($s->status) {
                                    'diajukan', 'submitted' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                    'diproses', 'approved_by_admin' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
                                    'approved_by_pimpinan' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',
                                    'completed', 'approved', 'selesai' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                    'ditolak', 'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                    default => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'
                                };
                                $statusLabel = match($s->status) {
                                    'diajukan', 'submitted' => 'Menunggu Review',
                                    'diproses', 'approved_by_admin' => 'Proses TTD',
                                    'approved_by_pimpinan' => 'Sedang Diproses',
                                    'completed', 'selesai' => 'Selesai',
                                    'ditolak', 'rejected' => 'Ditolak',
                                    default => ucfirst($s->status)
                                };
                            @endphp
                            <span class="inline-flex font-semibold rounded-full text-xs px-3 py-1 {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('dosen.surat.show', $s) }}" class="hover:scale-110 transition-transform" title="Lihat/Edit" style="color: #2563eb !important;"><i class="fas fa-eye fa-fw"></i></a>
                                @if(in_array($s->status, ['diajukan', 'submitted']))
                                    <form action="{{ route('dosen.surat.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan surat ini? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="hover:scale-110 transition-transform" title="Batalkan Permohonan" style="color: #f43f5e !important; border: none; background: none; padding: 0;">
                                            <i class="fas fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-inbox text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data permohonan surat yang ditemukan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau belum ada surat yang dibuat.</p>
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
                {{ $items->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
