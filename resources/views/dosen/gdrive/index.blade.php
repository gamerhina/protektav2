@extends('layouts.app')

@section('title', 'GDrive Folders')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Google Drive Folders</h1>
        </div>
        @php
            $defaultSort = 'nama';
            $defaultDirection = 'asc';
        @endphp

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                    <div>
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Folder</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama atau keterangan folder"
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition">
                            Cari
                        </button>
                        <a href="{{ route('dosen.gdrive.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                            Reset
                        </a>
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
                        <x-sortable-th column="keterangan" label="Keterangan" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($folders as $folder)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-800 font-medium">
                            <a href="{{ $folder->link }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold" title="Lihat Folder">
                                {{ $folder->nama }}
                                <i class="fas fa-external-link-alt text-xs ml-1"></i>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $folder->keterangan }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $folder->link }}" target="_blank" class="text-green-600 hover:text-green-800 font-semibold" title="Lihat Folder">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($folders->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada data folder Google Drive.</p>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => $perPage ?? 15, 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $folders->appends(request()->query())->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection
