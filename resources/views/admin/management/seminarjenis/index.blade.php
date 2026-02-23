@extends('layouts.app')

@section('title', 'Kelola Jenis Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Kelola Jenis Seminar</h1>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <a href="{{ route('admin.seminarjenis.create') }}" class="btn-gradient inline-flex items-center gap-2 justify-center">
                    <i class="fas fa-plus"></i> Tambah Jenis
                </a>
            </div>
        </div>
        @php
            $defaultSort = 'nama';
            $defaultDirection = 'asc';
        @endphp

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4">
                    <div>
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Jenis Seminar</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nama, kode, atau keterangan)..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 15) }}">
            </div>
        </form>
        
        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <x-sortable-th column="nama" label="Nama" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="kode" label="Kode" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Syarat</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aspek</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($seminarJenis as $jenis)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $jenis->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $jenis->kode }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $jenis->keterangan ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $jenis->syarat_seminar ? \Illuminate\Support\Str::limit(strip_tags($jenis->syarat_seminar), 80) : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $aspectCount = $jenis->assessmentAspects->count();
                                $p1Count = $jenis->assessmentAspects->where('evaluator_type', 'p1')->count();
                                $p2Count = $jenis->assessmentAspects->where('evaluator_type', 'p2')->count();
                                $pembahasCount = $jenis->assessmentAspects->where('evaluator_type', 'pembahas')->count();
                            @endphp
                            @if($aspectCount > 0)
                                <div class="flex flex-col space-y-1 text-xs">
                                    <span class="text-blue-600">P1: {{ $p1Count }} aspek</span>
                                    <span class="text-green-600">P2: {{ $p2Count }} aspek</span>
                                    <span class="text-purple-600">PMB: {{ $pembahasCount }} aspek</span>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs italic">Belum ada aspek</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.document-template.index', $jenis) }}" class="text-amber-500 hover:text-amber-700 font-semibold" title="Template HTML">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <a href="{{ route('admin.seminarjenis.edit', $jenis) }}" class="text-blue-600 hover:text-blue-900 font-semibold" title="Edit & Aspek">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.seminarjenis.destroy', $jenis) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis seminar ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-semibold" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($seminarJenis->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada jenis seminar yang terdaftar.</p>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => $perPage ?? 15, 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $seminarJenis->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
