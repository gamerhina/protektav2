@extends('layouts.app')

@section('title', 'Tugas Evaluasi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="py-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tugas Evaluasi</h1>

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
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
                </div>
            </form>
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                @if($evalSeminars->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <x-sortable-th column="mahasiswa" label="Mahasiswa" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                    <x-sortable-th column="jenis" label="Jenis" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                    <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Judul</th>
                                    <x-sortable-th column="tanggal" label="Tanggal" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                    <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                                    <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($evalSeminars as $seminar)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $seminar->mahasiswa->nama ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $seminar->seminarJenis->nama ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="font-medium text-gray-900 max-w-[200px] sm:max-w-xs truncate" title="{{ strip_tags($seminar->judul) }}">{!! strip_tags($seminar->judul, '<b><i><u><strong><em>') !!}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}</td>
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
                                            <!-- Evaluator Type -->
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 w-fit">
                                                @if($evaluatorType == 'p1') Pembimbing 1
                                                @elseif($evaluatorType == 'p2') Pembimbing 2
                                                @elseif($evaluatorType == 'pembahas') Pembahas
                                                @endif
                                            </span>
                                            
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full w-fit
                                                @if($nilai) bg-blue-50 text-blue-700
                                                @else bg-gray-100 text-gray-500
                                                @endif">
                                                Nilai: {{ $nilai ? 'Sudah' : 'Belum' }}
                                            </span>

                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full w-fit
                                                @if($signature) bg-emerald-50 text-emerald-700
                                                @else bg-gray-100 text-gray-500
                                                @endif">
                                                TTD: {{ $signature ? 'Sudah' : 'Belum' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center gap-3">
                                            <!-- Input Nilai Button -->
                                            <a
                                                href="{{ route('dosen.nilai.input', ['seminar' => $seminar->id]) }}"
                                                class="text-blue-600 hover:text-blue-800"
                                                title="Input nilai seminar"
                                            >
                                                <i class="fas fa-pen-to-square text-xl"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600">Anda tidak memiliki tugas evaluasi yang tertunda.</p>
                @endif
            </div>
            @if($evalSeminars->count() > 0)
                <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <form method="GET" class="w-full md:w-auto">
                        @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                        <input type="hidden" name="page" value="1">
                        @include('components.page-size-selector', ['perPage' => $perPage ?? 10, 'autoSubmit' => true])
                    </form>
                    <div class="w-full md:w-auto">
                        {{ $evalSeminars->links('components.pagination') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
