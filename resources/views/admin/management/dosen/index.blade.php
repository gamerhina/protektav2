@extends('layouts.app')

@section('title', 'Kelola Dosen')

@section('content')
@php
    $defaultSort = 'nama';
    $defaultDirection = 'asc';
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Kelola Dosen</h1>
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <a href="{{ route('admin.dosen.import.form') }}" class="btn-gradient inline-flex items-center gap-2">
                    <i class="fas fa-file-import"></i> Impor Dosen
                </a>
                <a href="{{ route('admin.dosen.create') }}" class="btn-gradient inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Dosen
                </a>
            </div>
        </div>

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4">
                    <div class="md:col-span-1">
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Dosen</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nama, NIP, atau email)..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="sort" value="{{ request('sort', $defaultSort ?? 'nama') }}">
                <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection ?? 'asc') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 15) }}">
            </div>
        </form>
        
        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <x-sortable-th column="nama" label="Nama" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="nip" label="NIP" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="email" label="Email" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Bimbingan Akad.</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($dosens as $dosen)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-800 font-medium">{{ $dosen->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $dosen->nip }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $dosen->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $dosen->mahasiswa_bimbingan_akademik_count }} Mahasiswa
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($dosen->hp)
                                    <a href="tel:{{ $dosen->hp }}" class="hover:scale-110 transition-transform" title="Telepon" style="color: #2563eb !important;"><i class="fas fa-phone fa-fw"></i></a>
                                    <a href="#" data-wa="{{ $dosen->hp }}" class="hover:scale-110 transition-transform" title="WhatsApp" style="color: #10b981 !important;"><i class="fa-brands fa-whatsapp fa-fw"></i></a>
                                @endif
                                <a href="{{ route('admin.dosen.edit', $dosen->id) }}" class="hover:scale-110 transition-transform" title="Edit" style="color: #f59e0b !important;"><i class="fas fa-edit fa-fw"></i></a>
                                @role('admin')
                                <a href="{{ route('admin.impersonate', ['type' => 'dosen', 'id' => $dosen->id]) }}" class="hover:scale-110 transition-transform" title="Login Sebagai" style="color: #6366f1 !important;" data-no-ajax><i class="fas fa-user-secret fa-fw"></i></a>
                                <form action="{{ route('admin.dosen.destroy', $dosen->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" data-confirm="Apakah Anda yakin ingin menghapus dosen ini?" class="hover:scale-110 transition-transform" title="Hapus" style="color: #f43f5e !important; border: none; background: none; padding: 0;">
                                        <i class="fas fa-trash fa-fw"></i>
                                    </button>
                                </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($dosens->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada data dosen.</p>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => $perPage ?? 15, 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $dosens->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
