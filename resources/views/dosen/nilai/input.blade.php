@extends('layouts.app')

@section('title', 'Input Nilai')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Input Nilai Seminar</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column: Core Details (Col-span 2) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Header Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fas fa-file-signature text-8xl"></i>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                {{ $seminar->seminarJenis->nama ?? 'N/A' }}
                            </span>
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                Peran: 
                                @if($evaluatorType == 'p1') Pembimbing 1
                                @elseif($evaluatorType == 'p2') Pembimbing 2
                                @else Pembahas
                                @endif
                            </span>
                        </div>

                        <h2 class="text-xl md:text-2xl font-semibold text-gray-800 leading-tight mb-4">
                            {!! $seminar->judul !!}
                        </h2>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mahasiswa</p>
                                <p class="text-sm font-bold text-gray-800">{{ $seminar->mahasiswa->nama ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $seminar->mahasiswa->npm ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requirements Files Card -->
                @php
                    $berkas = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
                    $syaratItems = collect(is_array($seminar->seminarJenis?->berkas_syarat_items) ? $seminar->seminarJenis->berkas_syarat_items : []);
                    
                    // Sort: files first (priority 0), then others (priority 1)
                    $sortedItems = $syaratItems->sortBy(function($item) {
                        $type = $item['type'] ?? 'file';
                        return $type === 'file' ? 0 : 1;
                    });
                @endphp

                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-list-check text-blue-500"></i> Persyaratan & Data
                    </h3>

                    @if(!empty($berkas))
                        <div class="flex flex-col gap-4">
                            @foreach($sortedItems as $item)
                                @php
                                    $key = $item['key'] ?? null;
                                    $label = $item['label'] ?? $key;
                                    $type = $item['type'] ?? 'file';
                                    $value = $berkas[$key] ?? null;
                                    $isFile = $type === 'file';
                                @endphp

                                @if($value)
                                    @if($isFile)
                                        {{-- File Display --}}
                                        <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:bg-white transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </div>
                                                    <div class="truncate">
                                                        <p class="text-sm font-bold text-gray-700 truncate capitalize">{{ $label }}</p>
                                                        <p class="text-[10px] text-gray-400 font-mono">FILE TERUNGGAH</p>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ asset('uploads/' . $value) }}" target="_blank" 
                                                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg hover:from-blue-600 hover:to-indigo-700 hover:-translate-y-0.5 transition-all duration-200 group">
                                                        <span>Buka Full</span>
                                                        <i class="fas fa-external-link-alt group-hover:rotate-45 transition-transform duration-300"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Text/Data Display --}}
                                        <div class="group flex items-center justify-between p-3 rounded-2xl bg-gray-50 border border-transparent hover:border-blue-200 hover:bg-white transition-all">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-all">
                                                    @if($type == 'date') <i class="fas fa-calendar"></i>
                                                    @elseif($type == 'email') <i class="fas fa-envelope"></i>
                                                    @elseif($type == 'number') <i class="fas fa-hashtag"></i>
                                                    @else <i class="fas fa-align-left"></i>
                                                    @endif
                                                </div>
                                                <div class="truncate w-full pr-2">
                                                    <p class="text-xs font-bold text-gray-700 truncate capitalize">{{ $label }}</p>
                                                    <p class="text-sm text-gray-600 font-medium truncate">
                                                        @if(is_array($value))
                                                            {{ implode(', ', $value) }}
                                                        @elseif($type == 'date')
                                                            {{ \Carbon\Carbon::parse($value)->translatedFormat('d F Y') }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fas fa-box-open text-3xl mb-2 opacity-20"></i>
                            <p class="text-xs italic">Tidak ada berkas yang diunggah.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Schedule Card -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden border-t-4 border-t-blue-500">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6">Jadwal Seminar</h3>
                    
                    <div class="space-y-6">
                        <!-- Date -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</p>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('l, d F Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi</p>
                                <p class="text-sm font-bold text-gray-800 line-clamp-2">{{ $seminar->lokasi }}</p>
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
                                return in_array('dosen', $rules['disetujui']);
                            }
                            if ($status === 'selesai' && isset($rules['selesai'])) {
                                return in_array('dosen', $rules['selesai']);
                            }
                            return false;
                        });
                @endphp

                @if($availableTemplates->count() > 0)
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden border-t-4 border-t-indigo-500">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-file-download text-indigo-500"></i> Unduh Dokumen
                    </h3>
                    <div class="space-y-3">
                        @foreach($availableTemplates as $template)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition-all">
                                <div class="min-w-0 mr-3">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $template->nama }}</p>
                                </div>
                                <a href="{{ route('seminar.document.download', [$seminar, $template]) }}" 
                                class="flex items-center gap-2 bg-indigo-600 text-white px-3 py-1.5 rounded-xl text-[10px] font-bold hover:bg-indigo-700 transition-all shrink-0" target="_blank">
                                    <i class="fas fa-download"></i> Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dosen.nilai.store', $seminar) }}" method="POST" id="nilaiForm">
            @csrf

            <h2 class="text-xl font-semibold text-gray-800 mb-4">Form Penilaian</h2>

            @php
                $activeTemplate = $seminar->seminarJenis->documentTemplates()->where('aktif', true)->first();
                $signatureMethod = $activeTemplate ? $activeTemplate->signature_method : 'qr_code';
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Column 1: Aspects --}}
                <div class="h-full">
                    <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden h-full flex flex-col">
                        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <i class="fas fa-star text-blue-500"></i> Aspek Penilaian
                        </h3>

                        <!-- Decimal Instruction -->
                        <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-blue-50/50 rounded-xl border border-blue-100/50">
                            <i class="fas fa-info-circle text-blue-500 text-[10px]"></i>
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wider">Gunakan titik (<span class="text-blue-600">.</span>) untuk angka desimal.</p>
                        </div>

                        @if($aspects->count() > 0)
                            <div class="flex flex-col gap-4">
                                @foreach($aspects as $aspect)
                                    @php
                                        $isCalc = $aspect->type !== 'input';
                                        $bgColor = $isCalc ? 'bg-emerald-50/30' : 'bg-gray-50/50';
                                        $iconColor = $isCalc ? 'text-emerald-500' : 'text-blue-500';
                                        $iconBg = $isCalc ? 'bg-emerald-50' : 'bg-blue-50';
                                    @endphp
                                    <div class="border border-gray-200 rounded-2xl p-4 {{ $bgColor }} hover:bg-white transition-all group relative overflow-hidden">
                                        @if($isCalc)
                                            <div class="absolute top-0 right-0 px-2 py-0.5 bg-emerald-500 text-[8px] font-bold text-white rounded-bl-lg uppercase">
                                                AUTO
                                            </div>
                                        @endif

                                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center {{ $iconColor }} group-hover:scale-110 transition-transform">
                                                    @if($isCalc)
                                                        <i class="fas fa-calculator text-sm"></i>
                                                    @else
                                                        <i class="fas fa-edit text-sm"></i>
                                                    @endif
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-sm font-bold text-gray-700 truncate capitalize">{{ $aspect->nama_aspek }}</p>
                                                    <div class="flex items-center gap-2">
                                                        <p class="text-[10px] text-gray-400 font-mono">
                                                            @if($isCalc)
                                                                {{ $aspect->type === 'sum' ? 'JUMLAH' : 'RATA-RATA' }}
                                                            @else
                                                                SKALA 0-100
                                                            @endif
                                                        </p>
                                                        @if($aspect->persentase > 0)
                                                            <span class="text-[9px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $aspect->persentase }}%</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 shrink-0">
                                                <div class="relative">
                                                    <input
                                                        type="number"
                                                        name="aspect_{{ $aspect->id }}"
                                                        id="aspect_{{ $aspect->id }}"
                                                        min="0"
                                                        max="5000"
                                                        step="0.01"
                                                        value="{{ old('aspect_' . $aspect->id, $existingScores[$aspect->id] ?? '') }}"
                                                        class="w-24 px-3 py-2 border {{ $isCalc ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10' }} rounded-xl aspect-input text-sm font-bold text-gray-800 text-center transition-all @error('aspect_' . $aspect->id) border-red-500 @enderror"
                                                        placeholder="0.00"
                                                        data-id="{{ $aspect->id }}"
                                                        data-type="{{ $aspect->type }}"
                                                        data-category="{{ $aspect->category }}"
                                                        data-related-aspects="{{ json_encode($aspect->related_aspects ?: []) }}"
                                                        data-weight="{{ $aspect->persentase }}"
                                                        {{ $isCalc ? 'readonly' : 'required' }}
                                                    />
                                                    <div class="absolute -right-1 -top-1">
                                                        @error('aspect_' . $aspect->id)
                                                            <span class="flex h-2 w-2">
                                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter w-6">PTS</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <label for="catatan" class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <i class="fas fa-comment-dots text-blue-500"></i> Catatan (Opsional)
                            </label>
                            <textarea
                                name="catatan"
                                id="catatan"
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-2xl bg-gray-50 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm @error('catatan') border-red-500 @enderror"
                                placeholder="Tambahkan catatan untuk penilaian ini..."
                            >{{ old('catatan', $existingNilai->catatan ?? '') }}</textarea>
                            @error('catatan')
                                <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Column 2: Signature Preview --}}
                <div class="h-full">
                    <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden h-full flex flex-col">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <i class="fas fa-signature text-blue-500"></i> Pratinjau Tanda Tangan
                        </label>
                        
                        <div class="flex-1 flex flex-col justify-center">
                            @if($existingSignature && ($existingSignature->qr_code_path || $existingSignature->tanda_tangan))
                                <div class="text-center bg-gray-50/50 rounded-2xl p-8 border border-gray-100 shadow-inner w-full">
                                @if($existingSignature->signature_type === 'qr_code' && $existingSignature->qr_code_path)
                                    <div class="relative inline-block group">
                                        <img src="{{ Storage::disk('uploads')->url($existingSignature->qr_code_path) }}" alt="QR Code" class="w-32 h-32 mx-auto">
                                        <div class="absolute inset-0 bg-white/10 group-hover:bg-transparent transition-all"></div>
                                    </div>
                                    <p class="text-[10px] font-bold text-green-600 uppercase tracking-widest mt-3 flex items-center justify-center gap-1">
                                        <i class="fas fa-check-circle"></i> Terverifikasi Digital
                                    </p>
                                @elseif($existingSignature->tanda_tangan)
                                    <img src="{{ asset('uploads/' . $existingSignature->tanda_tangan) }}" alt="Manual Signature" class="h-20 mx-auto">
                                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-3 flex items-center justify-center gap-1">
                                        <i class="fas fa-signature"></i> Tanda Tangan Manual
                                    </p>
                                @endif
                                <p class="text-[9px] text-gray-400 mt-2 font-mono">
                                    {{ $existingSignature->tanggal_ttd ? $existingSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') : '' }}
                                </p>
                            </div>
                        @else
                            <div class="text-center py-16 bg-gray-50/30 rounded-2xl border border-dashed border-gray-200 w-full flex flex-col items-center justify-center">
                                <i class="fas fa-signature text-gray-200 text-4xl mb-4"></i>
                                <p class="text-xs text-gray-400 font-medium">Belum ada tanda tangan terdeteksi</p>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Full Width Bottom Row: Agreement & Actions --}}
            <div class="mt-8 pt-8 border-t border-gray-100 space-y-8">
                <!-- Agreement Section -->
                <div class="signature-pad-wrapper-{{ $evaluatorType }}">
                    @if($signatureMethod === 'qr_code')
                        {{-- QR Code Agreement UI --}}
                        <div class="bg-gradient-to-br from-indigo-50/50 to-blue-50/50 border border-blue-200 rounded-2xl p-4 shadow-sm ring-1 ring-blue-600/5">
                            <div class="flex flex-col md:flex-row items-center gap-6">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white text-blue-600 shadow-sm border border-blue-100 flex items-center justify-center">
                                        <i class="fas fa-qrcode text-xl"></i>
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-sm font-bold text-gray-800 leading-tight">Metode QR Code Aktif</h4>
                                        <p class="text-[10px] text-gray-500 leading-relaxed">Centang kotak untuk menyetujui pembubuhan tanda tangan elektronik.</p>
                                    </div>
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer py-3 px-6 rounded-xl border-2 border-dashed border-blue-300 hover:bg-white hover:border-blue-400 transition-all group bg-blue-50/30 whitespace-nowrap">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="qr_agreement" value="1" class="peer h-6 w-6 cursor-pointer appearance-none rounded-lg border-2 border-blue-300 bg-white transition-all checked:border-blue-600 checked:bg-blue-600 shadow-sm">
                                        <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 group-hover:text-blue-700">
                                        {{ $existingSignature && $existingSignature->signature_type === 'qr_code' ? 'Update Tanda Tangan QR' : 'Setujui Tanda Tangan' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    @else
                        {{-- Manual Canvas Mode (Full Width) --}}
                        <div class="border border-gray-200 rounded-3xl p-6 bg-white shadow-sm ring-1 ring-gray-900/5">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-4 text-center">Buat Tanda Tangan Manual</label>
                            <input type="hidden" name="signature" class="signature-input-{{ $evaluatorType }}">
                            
                            <div class="flex justify-center mb-6">
                                <button type="button" class="toggle-signature-btn-{{ $evaluatorType }} btn-pill btn-pill-info text-sm px-10 py-3 shadow-md">
                                    <i class="fas fa-signature mr-2"></i>
                                    {{ $existingSignature ? 'Ubah Tanda Tangan' : 'Buat Tanda Tangan' }}
                                </button>
                            </div>

                            <div class="signature-pad-container-{{ $evaluatorType }} hidden animate-fade-in">
                                <div class="relative max-w-lg mx-auto">
                                    <canvas width="600" height="200" class="signature-canvas-{{ $evaluatorType }} border-2 border-blue-100 rounded-2xl bg-gray-50/50 cursor-crosshair w-full shadow-inner"></canvas>
                                    <button type="button" class="clear-signature-btn-{{ $evaluatorType }} absolute bottom-3 right-3 text-[10px] font-bold px-3 py-1.5 bg-white text-gray-600 rounded-lg hover:bg-red-50 hover:text-red-600 transition-all border border-gray-200 shadow-sm uppercase tracking-wider">
                                        BERSIHKAN
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-3 italic text-center">Gunakan mouse atau sentuhan (untuk layar sentuh) untuk tanda tangan.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons Row -->
                <div class="flex flex-col sm:flex-row gap-4 justify-between items-center py-6 bg-gray-50/50 rounded-3xl border border-gray-100 px-8">
                    <a href="{{ route('dosen.dashboard') }}" class="btn-pill btn-pill-secondary w-full sm:w-56 py-4 text-center order-2 sm:order-1 font-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    @if($aspects->count() > 0)
                        <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-64 py-4 shadow-xl shadow-blue-600/20 order-1 sm:order-2 font-bold text-sm tracking-wide">
                            <i class="fas fa-save mr-2"></i> Simpan Nilai
                        </button>
                    @endif
                </div>
            </div>
        </form>

        @if(false)
            {{-- Redundant section removed --}}
        @endif


    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/signature-pad.js')
    <script>
        (function() {
            function initNilaiForm() {
                const form = document.getElementById('nilaiForm');
                const signatureInput = document.querySelector('.signature-input-{{ $evaluatorType }}');
                const hasExistingSignature = {{ $existingSignature ? 'true' : 'false' }};

                if (!form || !signatureInput) return;
                if (form.dataset.initialized === 'true') return;

                const inputs = form.querySelectorAll('.aspect-input');
                
                function recalculate() {
                    const values = {};
                    inputs.forEach(input => {
                        if (input.dataset.type === 'input') {
                            values[input.dataset.id] = parseFloat(input.value) || 0;
                        }
                    });

                    inputs.forEach(input => {
                        if (input.dataset.type !== 'input') {
                            const category = input.dataset.category;
                            const relatedIds = JSON.parse(input.dataset.relatedAspects || '[]');
                            const type = input.dataset.type;
                            
                            let targetInputs = [];
                            
                            if (relatedIds.length > 0) {
                                targetInputs = Array.from(inputs).filter(i => 
                                    relatedIds.includes(Number(i.dataset.id)) || relatedIds.includes(String(i.dataset.id))
                                );
                            } else if (category) {
                                targetInputs = Array.from(inputs).filter(i => 
                                    i.dataset.type === 'input' && i.dataset.category === category
                                );
                            }

                            if (targetInputs.length > 0) {
                                let total = 0;
                                targetInputs.forEach(i => {
                                    total += parseFloat(i.value) || 0;
                                });

                                let result = total;
                                if (type === 'prev_avg') {
                                    result = total / targetInputs.length;
                                }
                                
                                input.value = result.toFixed(2);
                            }
                        }
                    });
                }

                inputs.forEach(input => {
                    if (input.dataset.type === 'input') {
                        input.addEventListener('input', recalculate);
                    }
                });

                // Initial calculation
                recalculate();

                form.addEventListener('submit', function (e) {
                    if (!signatureInput.value) {
                        if (hasExistingSignature) {
                            if (!confirm('Perubahan nilai akan disimpan menggunakan tanda tangan yang sudah ada. Lanjutkan?')) {
                                e.preventDefault();
                            }
                        } else {
                            if (!confirm('Anda belum membubuhkan tanda tangan. Lanjutkan tanpa tanda tangan?')) {
                                e.preventDefault();
                            }
                        }
                    }
                });

                form.dataset.initialized = 'true';
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initNilaiForm();
            } else {
                document.addEventListener('DOMContentLoaded', initNilaiForm);
            }
            window.addEventListener('page-loaded', initNilaiForm);
        })();
    </script>
@endsection
