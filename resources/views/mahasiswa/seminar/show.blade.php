@extends('layouts.app')

@section('title', 'Seminar Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Seminar Details</h1>
            <div class="flex space-x-2 justify-center sm:justify-start">
                @if($seminar->status === 'diajukan' || $seminar->status === 'belum_lengkap')
                    <button type="submit" form="seminar-update-form" class="btn-pill btn-pill-primary">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                @endif
                <a href="{{ route('mahasiswa.dashboard') }}" class="btn-pill btn-pill-secondary">
                    Back to Dashboard
                </a>
            </div>
        </div>

        @if($seminar->status === 'diajukan' || $seminar->status === 'belum_lengkap')
            <form id="seminar-update-form" action="{{ route('mahasiswa.seminar.update', $seminar) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="seminar_jenis_id" value="{{ $seminar->seminar_jenis_id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Details (Col-span 2) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Header Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fas fa-graduation-cap text-8xl"></i>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-full text-center">
                                {{ $seminar->seminarJenis->nama ?? 'N/A' }}
                            </span>
                            <span class="inline-flex font-bold rounded-full text-[10px] px-3 py-1 uppercase tracking-wider text-center
                                @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                @endif">
                                {{ $seminar->status == 'belum_lengkap' ? 'Belum Lengkap' : ucwords(str_replace('_', ' ', $seminar->status)) }}
                            </span>
                        </div>

                        @if($seminar->status === 'diajukan' || $seminar->status === 'belum_lengkap')
                            <div class="space-y-2 mb-4">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul Seminar</label>
                                <x-tinymce-editor
                                    name="judul"
                                    id="judul"
                                    :content="old('judul', $seminar->judul)"
                                    placeholder="Masukkan Judul Seminar..."
                                    :has-header="false"
                                    height="300"
                                />
                            </div>
                        @else
                            <h2 class="text-xl md:text-2xl font-semibold text-gray-800 leading-tight mb-4 text-center sm:text-left">
                                {!! $seminar->judul !!}
                            </h2>
                        @endif

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-id-card text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pendaftar</p>
                                <p class="text-sm font-bold text-gray-800">{{ optional($seminar->mahasiswa)->nama ?? Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $seminar->mahasiswa->npm ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluators Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php $isEditable = in_array($seminar->status, ['diajukan', 'belum_lengkap']); @endphp
                    <!-- P1 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P1</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 1</span>
                        </div>
                        @if($isEditable)
                            <select name="p1_dosen_id" id="p1_dosen_id" required class="w-full bg-gray-50 border-0 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-blue-500 dosen-select-toggle">
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}" {{ $seminar->p1_dosen_id == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                                <option value="manual" {{ old('p1_dosen_id', $seminar->p1_dosen_id ?? ($seminar->p1_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                            </select>
                            <div id="p1_manual_fields" class="{{ old('p1_dosen_id', $seminar->p1_dosen_id ?? ($seminar->p1_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 mt-2">
                                <input type="text" name="p1_nama" value="{{ old('p1_nama', $seminar->p1_nama) }}" placeholder="Nama Pembimbing 1" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-blue-500 px-3 py-2">
                                <input type="text" name="p1_nip" value="{{ old('p1_nip', $seminar->p1_nip) }}" placeholder="NIP Pembimbing 1" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-blue-500 px-3 py-2 mt-2">
                            </div>
                        @else
                            <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}</p>
                        @endif
                    </div>

                    <!-- P2 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-green-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P2</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 2</span>
                        </div>
                        @if($isEditable)
                            <select name="p2_dosen_id" id="p2_dosen_id" class="w-full bg-gray-50 border-0 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-green-500 dosen-select-toggle">
                                <option value="">Tidak ada</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}" {{ $seminar->p2_dosen_id == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                                <option value="manual" {{ old('p2_dosen_id', $seminar->p2_dosen_id ?? ($seminar->p2_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                            </select>
                            <div id="p2_manual_fields" class="{{ old('p2_dosen_id', $seminar->p2_dosen_id ?? ($seminar->p2_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 mt-2">
                                <input type="text" name="p2_nama" value="{{ old('p2_nama', $seminar->p2_nama) }}" placeholder="Nama Pembimbing 2" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-green-500 px-3 py-2">
                                <input type="text" name="p2_nip" value="{{ old('p2_nip', $seminar->p2_nip) }}" placeholder="NIP Pembimbing 2" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-green-500 px-3 py-2 mt-2">
                            </div>
                        @else
                            <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}</p>
                        @endif
                    </div>

                    <!-- PMB -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-purple-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">PMB</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembahas</span>
                        </div>
                        @if($isEditable)
                            <select name="pembahas_dosen_id" id="pembahas_dosen_id" class="w-full bg-gray-50 border-0 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-purple-500 dosen-select-toggle">
                                <option value="">Tidak ada</option>
                                @foreach($dosens as $d)
                                    <option value="{{ $d->id }}" {{ $seminar->pembahas_dosen_id == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                                <option value="manual" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id ?? ($seminar->pembahas_nama ? 'manual' : '')) == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                            </select>
                            <div id="pembahas_manual_fields" class="{{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id ?? ($seminar->pembahas_nama ? 'manual' : '')) == 'manual' ? '' : 'hidden' }} space-y-2 mt-2">
                                <input type="text" name="pembahas_nama" value="{{ old('pembahas_nama', $seminar->pembahas_nama) }}" placeholder="Nama Pembahas" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 px-3 py-2">
                                <input type="text" name="pembahas_nip" value="{{ old('pembahas_nip', $seminar->pembahas_nip) }}" placeholder="NIP Pembahas" class="w-full bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-800 focus:ring-2 focus:ring-purple-500 px-3 py-2 mt-2">
                            </div>
                        @else
                            <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Requirements Files -->
                @if($seminar->berkas_syarat && is_array($seminar->berkas_syarat) && count($seminar->berkas_syarat) > 0)
                @php
                    $itemDefs = collect($seminar->seminarJenis->berkas_syarat_items ?? [])->keyBy('key');
                    $berkas = collect($seminar->berkas_syarat ?? []);
                    $sortedBerkas = $berkas->sortBy(function ($value, $key) use ($itemDefs) {
                        $def = $itemDefs->get($key);
                        // Default to file based on original logic or explicitly check for 'file'
                        return ($def['type'] ?? 'file') === 'file' ? 0 : 1;
                    });
                @endphp
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-list-check text-blue-500"></i> Persyaratan & Data
                    </h3>
                    <div class="flex flex-col gap-4">
                        @foreach($sortedBerkas as $key => $value)
                            @continue(empty($value))
                            @php
                                $def = $itemDefs->get($key);
                                $type = $def['type'] ?? 'file'; 
                                $label = $def['label'] ?? ucwords(str_replace('_', ' ', $key));
                                $isFile = $type === 'file';
                            @endphp

                             @if($isFile)
                                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="truncate">
                                                <p class="text-sm font-bold text-gray-700 truncate capitalize">{{ $label }}</p>
                                                <p class="text-[10px] text-gray-400 font-mono">{{ $isEditable ? 'GANTI FILE (OPSIONAL)' : 'FILE TERUNGGAH' }}</p>
                                            </div>
                                        </div>
                                        @if(!$isEditable)
                                        <div class="flex space-x-2">
                                            <a href="{{ route('mahasiswa.seminar.files.show', ['path' => $value]) }}" target="_blank" 
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg hover:from-blue-600 hover:to-indigo-700 hover:-translate-y-0.5 transition-all duration-200 group">
                                                <span>Buka Full</span>
                                                <i class="fas fa-external-link-alt group-hover:rotate-45 transition-transform duration-300"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                    @if($isEditable)
                                        <input type="file" name="berkas_syarat_items[{{ $key }}]" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-4 transition-all">
                                    @endif

                                </div>
                            @else
                                <div class="group flex flex-col p-4 rounded-2xl bg-gray-50 border border-transparent hover:border-blue-200 hover:bg-white transition-all">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-all shadow-sm">
                                            @if($type == 'date') <i class="fas fa-calendar text-xs"></i>
                                            @elseif($type == 'email') <i class="fas fa-envelope text-xs"></i>
                                            @elseif($type == 'number') <i class="fas fa-hashtag text-xs"></i>
                                            @else <i class="fas fa-align-left text-xs"></i>
                                            @endif
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $label }}</p>
                                    </div>
                                    <div class="pl-11">
                                        @if($isEditable)
                                            @if($type === 'date')
                                                <input type="date" name="berkas_syarat_items[{{ $key }}]" value="{{ $value }}" class="w-full bg-transparent border-0 text-sm text-gray-800 font-bold focus:ring-0 p-0">
                                            @else
                                                <input type="text" name="berkas_syarat_items[{{ $key }}]" value="{{ $value }}" class="w-full bg-transparent border-0 text-sm text-gray-800 font-bold focus:ring-0 p-0">
                                            @endif
                                        @else
                                            <p class="text-sm text-gray-800 font-bold break-words">
                                                @if(is_array($value))
                                                    {{ implode(', ', $value) }}
                                                @elseif($type == 'date')
                                                    {{ \Carbon\Carbon::parse($value)->translatedFormat('d F Y') }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column: Schedule & Metadata -->
            <div class="space-y-6">
                <!-- Schedule Card -->
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden border-t-4 border-t-blue-500">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6">Jadwal Pelaksanaan</h3>
                    
                    <div class="space-y-6">
                        <!-- Date -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</p>
                                @if($isEditable)
                                    <input type="date" name="tanggal" value="{{ $seminar->tanggal ? $seminar->tanggal->format('Y-m-d') : '' }}" required class="w-full bg-transparent border-0 text-sm font-bold text-gray-800 focus:ring-0 p-0">
                                @else
                                    <p class="text-sm font-bold text-gray-800">
                                        {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('l, d F Y') : 'N/A' }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</p>
                                @if($isEditable)
                                    <input type="time" name="waktu_mulai" value="{{ $seminar->waktu_mulai ? \Carbon\Carbon::parse($seminar->waktu_mulai)->format('H:i') : '' }}" required class="w-full bg-transparent border-0 text-sm font-bold text-gray-800 focus:ring-0 p-0">
                                @else
                                    <p class="text-sm font-bold text-gray-800">{{ $seminar->waktu_mulai }} - {{ $seminar->waktu_selesai ?: 'Selesai' }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi</p>
                                @if($isEditable)
                                    <input type="text" name="lokasi" value="{{ $seminar->lokasi }}" required class="w-full bg-transparent border-0 text-sm font-bold text-gray-800 focus:ring-0 p-0 shrink" placeholder="Masukkan lokasi...">
                                @else
                                    <p class="text-sm font-bold text-gray-800 line-clamp-2">{{ $seminar->lokasi }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $status = $seminar->status;
                    $availableTemplates = \App\Models\DocumentTemplate::where('aktif', true)
                        ->where(function($q) use ($seminar) {
                            $q->whereNull('seminar_jenis_id')
                            ->orWhere('seminar_jenis_id', $seminar->seminar_jenis_id);
                        })
                        ->get()
                        ->filter(function($template) use ($status) {
                            $rules = $template->download_rules ?? [];
                            if ($status === 'disetujui' && isset($rules['disetujui'])) {
                                return in_array('mahasiswa', $rules['disetujui']);
                            }
                            if ($status === 'selesai' && isset($rules['selesai'])) {
                                return in_array('mahasiswa', $rules['selesai']);
                            }
                            return false;
                        });
                @endphp

                @if($availableTemplates->count() > 0)
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-file-download text-indigo-500"></i> Unduh Dokumen
                    </h3>
                    <div class="space-y-3">
                        @foreach($availableTemplates as $template)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition-all">
                                <div class="min-w-0 mr-3">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $template->nama }}</p>
                                </div>
                                <a href="{{ route('seminar.document.download', [$seminar, $template]) }}" target="_blank"
                                class="flex items-center gap-2 bg-indigo-600 text-white px-3 py-1.5 rounded-xl text-[10px] font-bold hover:bg-indigo-700 transition-all shrink-0">
                                    <i class="fas fa-download"></i> Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($seminar->status === 'diajukan' || $seminar->status === 'belum_lengkap')
            </form>
        @endif

            @use('App\Helpers\Terbilang')
            @php
                $nilaiP1 = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'p1' && $n->dosen_id == $seminar->p1_dosen_id;
                });
                $nilaiP2 = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'p2' && $n->dosen_id == $seminar->p2_dosen_id;
                });
                $nilaiPembahas = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'pembahas' && $n->dosen_id == $seminar->pembahas_dosen_id;
                });
            @endphp

            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Rekapitulasi Nilai Seminar</h3>
                
                @if($nilaiP1 || $nilaiP2 || $nilaiPembahas)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Pembimbing 1 -->
                        <div class="border-2 border-blue-200 rounded-lg p-5 {{ $nilaiP1 ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P1</span>
                                {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}
                            </h3>
                            @if($nilaiP1)
                                @if($nilaiP1->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-blue-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiP1->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-blue-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-blue-700 break-words">{{ number_format($nilaiP1->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiP1->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiP1->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-blue-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP1->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>

                        <!-- Pembimbing 2 -->
                        <div class="border-2 border-green-200 rounded-lg p-5 {{ $nilaiP2 ? 'bg-green-50' : 'bg-gray-50' }}">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P2</span>
                                {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}
                            </h3>
                            @if($nilaiP2)
                                @if($nilaiP2->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-green-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiP2->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-green-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-green-700 break-words">{{ number_format($nilaiP2->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiP2->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiP2->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-green-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP2->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>

                        <!-- Pembahas -->
                        <div class="border-2 border-purple-200 rounded-lg p-5 {{ $nilaiPembahas ? 'bg-purple-50' : 'bg-gray-50' }}">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">PMB</span>
                                {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
                            </h3>
                            @if($nilaiPembahas)
                                @if($nilaiPembahas->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-purple-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiPembahas->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-purple-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-purple-700 break-words">{{ number_format($nilaiPembahas->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiPembahas->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiPembahas->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-purple-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiPembahas->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>
                    </div>

                    @php
                        $p1Percentage = $seminar->seminarJenis->p1_weight ?? 35;
                        $p2Percentage = $seminar->seminarJenis->p2_weight ?? 35;
                        $pembahasPercentage = $seminar->seminarJenis->pembahas_weight ?? 30;
                        $finalScore = $seminar->calculateWeightedScore();
                    @endphp
                    
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-400 rounded-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-shrink-0">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Nilai Akhir Keseluruhan</h4>
                                <p class="text-sm text-gray-600">
                                    P1 ({{ $p1Percentage }}%) + P2 ({{ $p2Percentage }}%) + Pembahas ({{ $pembahasPercentage }}%)
                                </p>
                            </div>
                            <div class="text-left md:text-right flex-shrink min-w-0">
                                <p class="text-4xl md:text-5xl font-bold text-orange-600 break-words">{{ number_format($finalScore, 2) }}</p>
                                <p class="text-sm md:text-base italic text-gray-700 mt-2 break-words overflow-wrap-anywhere">
                                    {{ ucwords(Terbilang::convert($finalScore)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-gray-600">Belum ada penilaian untuk seminar ini.</p>
                    </div>
                @endif
            </div>

            <!-- Signature Details -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-500">Tanda Tangan Elektronik</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    @php
                        $signatures = $seminar->signatures->keyBy('jenis_penilai');
                        $p1Signature = $signatures['p1'] ?? null;
                        $p2Signature = $signatures['p2'] ?? null;
                        $pembahasSignature = $signatures['pembahas'] ?? null;
                    @endphp

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembimbing 1</h4>
                        @if($p1Signature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembimbing 2</h4>
                        @if($p2Signature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>


                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembahas</h4>
                        @if($pembahasSignature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>
                </div>

                <!-- Seminar Discussion Section -->
                <div id="discussion" class="mt-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-comments text-orange-600 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                                    <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan prodi / dosen</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <!-- Comments List -->
                            <div class="space-y-4 mb-6 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                                @forelse($seminar->comments as $comment)
                                    <div class="flex gap-3 {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user()) ? 'flex-row-reverse' : '' }}">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm
                                                {{ $comment->user_type === 'App\Models\Admin' ? 'bg-indigo-100 text-indigo-600' : 
                                                  ($comment->user_type === 'App\Models\Dosen' ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600') }}">
                                                {{ substr($comment->user->nama ?? 'U', 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="flex flex-col max-w-[85%] {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user()) ? 'items-end' : 'items-start' }}">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="text-[10px] font-bold text-gray-900">{{ $comment->user->nama ?? 'Unknown' }}</span>
                                                <span class="text-gray-400 text-[9px]">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="px-3 py-2 rounded-xl text-xs leading-normal shadow-sm border
                                                {{ $comment->user_id === auth()->id() && $comment->user_type === get_class(auth()->user())
                                                    ? 'bg-blue-600 text-white border-blue-600 rounded-tr-none' 
                                                    : 'bg-white text-gray-700 border-gray-100 rounded-tl-none' }}">
                                                {!! nl2br(e($comment->message)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6">
                                        <i class="fas fa-comment-slash text-gray-200 text-2xl mb-2"></i>
                                        <p class="text-[11px] text-gray-400">Belum ada diskusi.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Comment Form -->
                            <form action="{{ route('mahasiswa.seminar.comment.store', $seminar) }}" method="POST">
                                @csrf
                                <div class="flex gap-2">
                                    <div class="flex-1 relative">
                                        <textarea name="message" rows="1" required
                                            class="w-full pl-3 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none resize-none"
                                            placeholder="Tulis pesan..."></textarea>
                                    </div>
                                    <button type="submit" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm flex-shrink-0 self-end">
                                        <i class="fas fa-paper-plane text-xs"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle anchor scroll
        if (window.location.hash) {
            setTimeout(() => {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }, 500);
        }

        // Manual Dosen Toggle
        document.querySelectorAll('.dosen-select-toggle').forEach(selectEl => {
            selectEl.addEventListener('change', function() {
                const targetId = this.id.replace('_dosen_id', '_manual_fields');
                const target = document.getElementById(targetId);
                if (target) {
                    if (this.value === 'manual') {
                        target.classList.remove('hidden');
                    } else {
                        target.classList.add('hidden');
                    }
                }
            });
        });
    });

    // Handle AJAX/Soft navigation
    window.addEventListener('page-loaded', function() {
        if (window.location.hash) {
            const element = document.querySelector(window.location.hash);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
</script>
@endpush
