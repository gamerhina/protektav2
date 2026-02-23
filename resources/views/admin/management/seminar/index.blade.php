@extends('layouts.app')

@section('title', 'Kelola Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Kelola Seminar</h1>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <a href="{{ route('admin.seminar.export') }}" download data-no-ajax class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-green-600 to-green-700 px-7 py-2.5 text-sm font-semibold text-white shadow-lg shadow-green-500/30 transition-all hover:-translate-y-0.5 hover:shadow-green-600/50">
                    <i class="fas fa-file-excel"></i> Ekspor Excel
                </a>
                <a href="{{ route('admin.seminar.create') }}" class="btn-gradient inline-flex items-center gap-2 justify-center">
                    <i class="fas fa-plus"></i> Tambah Seminar Baru
                </a>
            </div>
        </div>
        @php
            $defaultSort = 'tanggal';
            $defaultDirection = 'desc';
        @endphp

        <form method="GET" class="mb-6">
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
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nomor surat, mahasiswa, judul atau status)..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                    {{-- Status Filter --}}
                    <div>
                         <div class="flex flex-wrap gap-2">
                            @php
                                $currentStatus = request('status_filter');
                                $statuses = [
                                    '' => 'Semua',
                                    'diajukan' => 'Diajukan',
                                    'disetujui' => 'Disetujui',
                                    'belum_lengkap' => 'Belum Lengkap',
                                    'selesai' => 'Selesai',
                                    'ditolak' => 'Ditolak'
                                ];
                            @endphp
                            @foreach($statuses as $key => $label)
                                <a href="{{ request()->fullUrlWithQuery(['status_filter' => $key]) }}" 
                                class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all
                                {{ $currentStatus == (string)$key ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 20) }}">
            </div>
        </form>
        
        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <x-sortable-th column="no_surat" label="Nomor Surat" :default-sort="$defaultSort" :default-direction="$defaultDirection" class="w-1/5" />
                        <x-sortable-th column="mahasiswa" label="Mahasiswa" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="jenis" label="Jenis" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Judul</th>
                        <x-sortable-th column="tanggal" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" class="w-40" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($seminars as $seminar)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800 break-all">{{ $seminar->no_surat ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $seminar->mahasiswa->nama ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $seminar->seminarJenis->nama ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="font-medium text-gray-900 max-w-[200px] sm:max-w-xs truncate" title="{{ strip_tags($seminar->judul) }}">{!! strip_tags($seminar->judul, '<b><i><u><strong><em>') !!}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $seminar->tanggal ? \Carbon\Carbon::parse($seminar->tanggal)->timezone('Asia/Jakarta')->translatedFormat('d F Y') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex font-semibold rounded-full
                                @if($seminar->status == 'belum_lengkap') text-[10px] px-2 py-0.5
                                @else text-xs px-3 py-1
                                @endif
                                @if($seminar->status == 'diajukan') bg-amber-50 text-amber-700
                                @elseif($seminar->status == 'disetujui') bg-blue-50 text-blue-700
                                @elseif($seminar->status == 'ditolak') bg-rose-50 text-rose-700
                                @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                @elseif($seminar->status == 'selesai') bg-emerald-50 text-emerald-700
                                @else bg-gray-100 text-gray-600 @endif">
                                @if($seminar->status == 'belum_lengkap')
                                    Belum Lengkap
                                @else
                                    {{ ucfirst($seminar->status) }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.seminar.show', $seminar->id) }}" class="hover:scale-110 transition-transform" title="Lihat" style="color: #2563eb !important;"><i class="fas fa-eye fa-fw"></i></a>
                                <a href="{{ route('admin.seminar.edit', $seminar->id) }}" class="hover:scale-110 transition-transform" title="Ubah" style="color: #f59e0b !important;"><i class="fas fa-edit fa-fw"></i></a>
                                <form action="{{ route('admin.seminar.destroy', $seminar->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seminar ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="hover:scale-110 transition-transform" title="Hapus" style="color: #f43f5e !important; border: none; background: none; padding: 0;">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($seminars->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada data seminar.</p>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => $perPage ?? 20, 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $seminars->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
