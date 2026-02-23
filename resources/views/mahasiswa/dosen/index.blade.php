@extends('layouts.app')

@section('title', 'Daftar Dosen')

@section('content')
@php
    $defaultSort = 'nama';
    $defaultDirection = 'asc';
@endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Daftar Dosen</h1>
                </div>
            </div>


            <form method="GET" class="mb-6">
                <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                    <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                        <div class="md:col-span-1">
                            <label for="search" class="text-sm font-medium text-gray-600">Cari Dosen</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                    </svg>
                                </span>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama, NIP, atau email"
                                       class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                            </div>
                        </div>
                        <div class="flex items-end gap-3">
                            <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition">
                                Cari
                            </button>
                            <a href="{{ route('mahasiswa.dosen.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                Reset
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="sort" value="{{ request('sort', $defaultSort ?? 'nama') }}">
                    <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection ?? 'asc') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
                </div>
            </form>

            <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <x-sortable-th column="nama" label="Nama" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                <x-sortable-th column="nip" label="NIP" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                <x-sortable-th column="email" label="Email" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                <th class="px-6 py-3 text-center text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($dosens as $dosen)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dosen->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $dosen->nip ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $dosen->email ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-3">
                                            @if($dosen->hp)
                                                <a href="tel:{{ $dosen->hp }}" class="hover:scale-110 transition-transform" title="Telepon" style="color: #2563eb !important;"><i class="fas fa-phone fa-fw"></i></a>
                                                <a href="#" data-wa="{{ $dosen->hp }}" class="hover:scale-110 transition-transform" title="WhatsApp" style="color: #10b981 !important;"><i class="fa-brands fa-whatsapp fa-fw"></i></a>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-600">Belum ada data dosen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(($dosens ?? null) && $dosens->hasPages())
                <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form method="GET" class="w-full md:w-auto">
                        @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                        <input type="hidden" name="page" value="1">
                        @include('components.page-size-selector', ['perPage' => $perPage, 'autoSubmit' => true])
                    </form>
                    <div class="w-full md:w-auto">
                        {{ $dosens->links('components.pagination') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
