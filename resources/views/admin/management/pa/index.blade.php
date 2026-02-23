@extends('layouts.app')

@section('title', 'Pembimbing Akademik')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pembimbing Akademik</h1>
                <p class="text-sm text-slate-500 mt-0.5">Daftar Dosen dan distribusi Mahasiswa Bimbingan Akademik (PA).</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.mahasiswa.import.form') }}" class="btn-pill btn-pill-primary inline-flex items-center gap-2 !no-underline">
                    <i class="fas fa-file-import"></i> Update PA via Impor
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
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nama dosen atau NIP..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($dosens as $dosen)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100 overflow-hidden">
                                @if(!empty($dosen->foto))
                                    <img src="{{ asset('uploads/' . $dosen->foto) }}" alt="{{ $dosen->nama }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($dosen->nama, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 leading-tight group-hover:text-blue-600 transition-colors">{{ $dosen->nama }}</h3>
                                <p class="text-xs text-gray-500 mt-1">NIP. {{ $dosen->nip }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="text-center flex-1">
                            <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Mahasiswa</p>
                            <p class="text-xl font-black text-blue-600">{{ $dosen->mahasiswa_bimbingan_akademik_count }}</p>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="flex-1 flex justify-center">
                            <a href="{{ route('admin.mahasiswa.index', ['pembimbing_akademik_id' => $dosen->id]) }}" class="text-xs font-bold text-blue-500 hover:text-blue-700 flex items-center gap-1">
                                DETAIL <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center text-[11px] text-gray-500">
                    <a href="mailto:{{ $dosen->email }}" class="hover:text-blue-600 transition-colors flex items-center" title="{{ $dosen->email }}">
                        <i class="fas fa-envelope mr-1"></i> {{ Str::limit($dosen->email, 20) }}
                    </a>
                    @if($dosen->hp)
                        @php
                            $waNumber = preg_replace('/^0/', '62', preg_replace('/\D/', '', $dosen->hp));
                        @endphp
                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="hover:text-green-600 transition-colors flex items-center font-medium" title="Chat via WhatsApp">
                            <i class="fa-brands fa-whatsapp mr-1.5 text-green-500 text-sm"></i> {{ $dosen->hp }}
                        </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-friends text-gray-300 text-3xl"></i>
                </div>
                <p class="text-gray-500 font-medium">Data dosen tidak ditemukan.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $dosens->links('components.pagination') }}
        </div>
    </div>
</div>
@endsection
