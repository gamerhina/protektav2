@extends('layouts.app')

@section('title', 'Seminar Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Seminar Details</h1>
            <div class="flex space-x-2 justify-center sm:justify-start">
                @if($seminar->status == 'diajukan')
                    <form action="{{ route('admin.seminar.approve', $seminar->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-pill btn-pill-success" onclick="return confirm('Setujui pengajuan seminar ini?')">
                            Setujui
                        </button>
                    </form>
                    <form action="{{ route('admin.seminar.reject', $seminar->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-pill btn-pill-danger" onclick="return confirm('Tolak pengajuan seminar ini?')">
                            Tolak
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.seminar.edit', $seminar->id) }}" class="btn-pill btn-pill-warning">
                    Edit
                </a>
                <a href="{{ route('admin.seminar.index') }}" class="btn-pill btn-pill-secondary">
                    Kembali
                </a>
            </div>
        </div>

        @php
            $templates = \App\Models\DocumentTemplate::where('aktif', true)
                ->where(function($q) use ($seminar) {
                    $q->whereNull('seminar_jenis_id')
                      ->orWhere('seminar_jenis_id', $seminar->seminar_jenis_id);
                })
                ->get();
        @endphp

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
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                {{ $seminar->seminarJenis->nama ?? 'N/A' }}
                            </span>
                            <span class="inline-flex font-bold rounded-full text-[10px] px-3 py-1 uppercase tracking-wider
                                @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                @endif">
                                {{ $seminar->status == 'belum_lengkap' ? 'Belum Lengkap' : ucfirst($seminar->status) }}
                            </span>
                            @if($seminar->no_surat)
                                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-mono font-bold rounded-full">
                                    #{{ $seminar->no_surat }}
                                </span>
                            @endif
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

                <!-- Evaluators Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- P1 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P1</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 1</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}</p>
                    </div>

                    <!-- P2 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-green-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P2</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 2</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}</p>
                    </div>

                    <!-- PMB -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-purple-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">PMB</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembahas</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}</p>
                    </div>
                </div>

                <!-- Requirements Files -->
                @if($seminar->berkas_syarat && is_array($seminar->berkas_syarat) && count($seminar->berkas_syarat) > 0)
                @php
                    $itemDefs = collect($seminar->seminarJenis->berkas_syarat_items ?? [])->keyBy('key');
                    $berkas = collect($seminar->berkas_syarat ?? []);
                    $sortedBerkas = $berkas->sortBy(function ($value, $key) use ($itemDefs) {
                        $def = $itemDefs->get($key);
                        $type = $def['type'] ?? 'text'; // Default to text if missing for sorting safety, or check logic
                        // Actually original logic was default 'file'. Let's match typical priority.
                        // If it is explicitly 'file' -> 0. Else -> 1.
                        return ($def['type'] ?? 'file') === 'file' ? 0 : 1;
                    });
                @endphp
                <div class="bg-white rounded-3xl border border-gray-200 p-6 overflow-hidden">
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
                                {{-- File Display with Preview --}}
                                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="truncate">
                                                <p class="text-sm font-bold text-gray-700 truncate capitalize">{{ $label }}</p>
                                                <p class="text-[10px] text-gray-400 font-mono">PREVIEW FILE</p>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.seminar.files.show', ['path' => $value]) }}" target="_blank" 
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg hover:from-blue-600 hover:to-indigo-700 hover:-translate-y-0.5 transition-all duration-200 group">
                                                <span>Buka Full</span>
                                                <i class="fas fa-external-link-alt group-hover:rotate-45 transition-transform duration-300"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-xl overflow-hidden border border-gray-300 relative">
                                        <div class="aspect-w-16 aspect-h-9 h-[500px]">
                                            <iframe src="{{ route('admin.seminar.files.show', ['path' => $value]) }}" class="w-full h-full" loading="lazy"></iframe>
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
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('l, d F Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</p>
                                <p class="text-sm font-bold text-gray-800">{{ $seminar->waktu_mulai }} - {{ $seminar->waktu_selesai ?: 'Selesai' }}</p>
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

                @if($templates->count() > 0)
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-file-download text-indigo-500"></i> Unduh Dokumen
                    </h3>
                    <div class="space-y-4">
                        @foreach($templates as $template)
                            <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 hover:border-blue-200 transition-all">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-800 text-xs truncate">{{ $template->nama }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('admin.seminar.document.preview', [$seminar, $template]) }}" target="_blank"
                                   class="flex items-center justify-center gap-2 bg-indigo-600 border border-transparent py-1.5 rounded-lg text-[10px] font-bold text-white hover:bg-indigo-700 transition-colors w-full">
                                    <i class="fas fa-download"></i> Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Notifikasi & Pengiriman -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                <i class="fa-solid fa-paper-plane text-indigo-600 text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 leading-none mb-1">Notifikasi & Pengiriman</h3>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Kirim dokumen ke stakeholder</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <button type="button" id="btn-show-notification-preview" class="w-full px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all group mb-4">
                            <i class="fa-solid fa-paper-plane text-indigo-200 group-hover:text-white transition-colors text-sm"></i> 
                            <span>Kirim Notifikasi</span>
                        </button>
                        <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 text-[10px] text-blue-700 font-bold">
                            <i class="fas fa-info-circle mr-2 opacity-50"></i>
                            Kirim link dokumen atau pengumuman melalui Email/WA.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6 mt-6">
            <!-- Score Recapitulation with Terbilang -->
            @include('admin.management.seminar.score_recapitulation')



            {{-- Signature Details --}}
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-800 flex items-center gap-2">
                        <i class="fas fa-pen-nib text-indigo-500"></i>
                        Tanda Tangan Elektronik
                    </h3>
                    @php
                        $activeTemplate = $seminar->seminarJenis->documentTemplates()->where('aktif', true)->first();
                        $signatureMethod = $activeTemplate ? $activeTemplate->signature_method : 'qr_code';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $signatureMethod === 'qr_code' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                        <i class="fas {{ $signatureMethod === 'qr_code' ? 'fa-qrcode' : 'fa-signature' }} mr-1"></i>
                        {{ $signatureMethod === 'qr_code' ? 'QR Code Mode' : 'Manual Canvas Mode' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $signatures = $seminar->signatures->keyBy('jenis_penilai');
                        $p1Signature = $signatures['p1'] ?? null;
                        $p2Signature = $signatures['p2'] ?? null;
                        $pembahasSignature = $signatures['pembahas'] ?? null;
                    @endphp

                    {{-- P1 Signature --}}
                    <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-white to-blue-50/30">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold">P1</span>
                            <h4 class="font-semibold text-gray-800 text-sm">Pembimbing 1</h4>
                        </div>
                        @if($p1Signature)
                            <div class="mt-3 space-y-2">
                                @if($p1Signature->signature_type === 'qr_code' && $p1Signature->qr_code_path)
                                    <div class="bg-white p-3 rounded-lg border-2 border-blue-200 flex items-center justify-center">
                                        <img src="{{ Storage::disk('uploads')->url($p1Signature->qr_code_path) }}" alt="QR Code P1" class="w-24 h-24">
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-green-600 bg-green-50 px-3 py-1.5 rounded-lg">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="font-semibold">Terverifikasi Digital</span>
                                    </div>
                                @elseif($p1Signature->tanda_tangan)
                                    <img src="{{ route('admin.seminar.files.show', ['path' => $p1Signature->tanda_tangan]) }}" alt="Tanda Tangan P1" class="max-w-full h-auto border border-gray-200 rounded-lg bg-white p-2">
                                @else
                                    <div class="text-center py-4 text-gray-400 text-xs italic">Berkas tanda tangan tidak ditemukan</div>
                                @endif
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-hourglass-half text-3xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-500 font-medium">Belum ditandatangani</p>
                            </div>
                        @endif
                    </div>

                    {{-- P2 Signature --}}
                    <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-white to-green-50/30">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold">P2</span>
                            <h4 class="font-semibold text-gray-800 text-sm">Pembimbing 2</h4>
                        </div>
                        @if($p2Signature)
                            <div class="mt-3 space-y-2">
                                @if($p2Signature->signature_type === 'qr_code' && $p2Signature->qr_code_path)
                                    <div class="bg-white p-3 rounded-lg border-2 border-green-200 flex items-center justify-center">
                                        <img src="{{ Storage::disk('uploads')->url($p2Signature->qr_code_path) }}" alt="QR Code P2" class="w-24 h-24">
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-green-600 bg-green-50 px-3 py-1.5 rounded-lg">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="font-semibold">Terverifikasi Digital</span>
                                    </div>
                                @elseif($p2Signature->tanda_tangan)
                                    <img src="{{ route('admin.seminar.files.show', ['path' => $p2Signature->tanda_tangan]) }}" alt="Tanda Tangan P2" class="max-w-full h-auto border border-gray-200 rounded-lg bg-white p-2">
                                @else
                                    <div class="text-center py-4 text-gray-400 text-xs italic">Berkas tanda tangan tidak ditemukan</div>
                                @endif
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-hourglass-half text-3xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-500 font-medium">Belum ditandatangani</p>
                            </div>
                        @endif
                    </div>

                    {{-- Pembahas Signature --}}
                    <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-white to-purple-50/30">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold">PMB</span>
                            <h4 class="font-semibold text-gray-800 text-sm">Pembahas</h4>
                        </div>
                        @if($pembahasSignature)
                            <div class="mt-3 space-y-2">
                                @if($pembahasSignature->signature_type === 'qr_code' && $pembahasSignature->qr_code_path)
                                    <div class="bg-white p-3 rounded-lg border-2 border-purple-200 flex items-center justify-center">
                                        <img src="{{ Storage::disk('uploads')->url($pembahasSignature->qr_code_path) }}" alt="QR Code Pembahas" class="w-24 h-24">
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-green-600 bg-green-50 px-3 py-1.5 rounded-lg">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="font-semibold">Terverifikasi Digital</span>
                                    </div>
                                @elseif($pembahasSignature->tanda_tangan)
                                    <img src="{{ route('admin.seminar.files.show', ['path' => $pembahasSignature->tanda_tangan]) }}" alt="Tanda Tangan Pembahas" class="max-w-full h-auto border border-gray-200 rounded-lg bg-white p-2">
                                @else
                                    <div class="text-center py-4 text-gray-400 text-xs italic">Berkas tanda tangan tidak ditemukan</div>
                                @endif
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}
                                </p>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <i class="fas fa-hourglass-half text-3xl text-gray-300 mb-2"></i>
                                <p class="text-xs text-gray-500 font-medium">Belum ditandatangani</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Seminar Discussion Section -->
                <div class="mt-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-comments text-orange-600 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                                    <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan mahasiswa / prodi</p>
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
                            <form action="{{ route('admin.seminar.comment.store', $seminar) }}" method="POST">
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

                <!-- Delete Seminar Button -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="mt-4">
                        <form action="{{ route('admin.seminar.destroy', $seminar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seminar ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-pill btn-pill-danger">
                                Delete Seminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notification-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" data-modal-toggle="notification-modal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full z-10">
                <form id="notification-form" action="{{ route('admin.seminar.send-notification', $seminar) }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4 border-b pb-4">
                            <h3 class="text-lg font-bold text-gray-900">Kirim Notifikasi Seminar</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-500 btn-close-modal" data-modal-toggle="notification-modal"><i class="fas fa-times"></i></button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Penerima</label>
                                <select id="notif-recipient-select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="mahasiswa">Mahasiswa ({{ $seminar->mahasiswa->nama }})</option>
                                    @if($seminar->p1Dosen) <option value="p1">Pembimbing 1 ({{ $seminar->p1Dosen->nama }})</option> @endif
                                    @if($seminar->p2Dosen) <option value="p2">Pembimbing 2 ({{ $seminar->p2Dosen->nama }})</option> @endif
                                    @if($seminar->pembahasDosen) <option value="pembahas">Pembahas ({{ $seminar->pembahasDosen->nama }})</option> @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Template</label>
                                <select id="notif-template-select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">-- Dasar (Tanpa Template) --</option>
                                    @foreach($templates as $t)
                                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="modal-loading-state" class="py-10 text-center hidden">
                            <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-3"></i>
                            <p class="text-xs text-gray-400">Menyiapkan pratinjau...</p>
                        </div>

                        <div id="modal-editor-state" class="space-y-4 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase">Email</label>
                                    <input type="email" name="recipient_email" id="notif-preview-email" class="w-full px-3 py-2 border rounded-lg bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase">WhatsApp</label>
                                    <input type="text" name="recipient_wa" id="notif-preview-wa" class="w-full px-3 py-2 border rounded-lg bg-gray-50" readonly>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase">Subjek Email</label>
                                <input type="text" name="subject" id="notif-preview-subject" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase">Isi Pesan</label>
                                <textarea name="body" id="notif-preview-body" rows="6" class="w-full px-3 py-2 border rounded-lg"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row-reverse gap-3">
                        <input type="hidden" name="channel" id="notif-channel" value="email">
                        <button type="submit" id="btn-notif-send-email" class="btn-pill btn-pill-info px-8"><i class="fa-solid fa-paper-plane mr-2"></i> Kirim Email</button>
                        <button type="button" id="btn-notif-send-wa" class="btn-pill btn-pill-success px-8"><i class="fa-brands fa-whatsapp mr-2"></i> Kirim WA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const stakeholders = {
                'mahasiswa': {
                    'email': '{{ $seminar->mahasiswa->email }}',
                    'wa': '{{ $seminar->mahasiswa->wa ?: ($seminar->mahasiswa->hp ?? "") }}'
                },
                'p1': {
                    'email': '{{ $seminar->p1Dosen->email ?? "" }}',
                    'wa': '{{ $seminar->p1Dosen->wa ?? ($seminar->p1Dosen->hp ?? "") }}'
                },
                'p2': {
                    'email': '{{ $seminar->p2Dosen->email ?? "" }}',
                    'wa': '{{ $seminar->p2Dosen->wa ?? ($seminar->p2Dosen->hp ?? "") }}'
                },
                'pembahas': {
                    'email': '{{ $seminar->pembahasDosen->email ?? "" }}',
                    'wa': '{{ $seminar->pembahasDosen->wa ?? ($seminar->pembahasDosen->hp ?? "") }}'
                }
            };

            async function updatePreview() {
                const templateId = document.getElementById('notif-template-select').value;
                const recipientType = document.getElementById('notif-recipient-select').value;
                const loader = document.getElementById('modal-loading-state');
                const editor = document.getElementById('modal-editor-state');

                // Update contact info
                const s = stakeholders[recipientType];
                document.getElementById('notif-preview-email').value = s.email || '';
                document.getElementById('notif-preview-wa').value = s.wa || '';

                loader.classList.remove('hidden');
                editor.classList.add('hidden');

                try {
                    const res = await fetch(`{{ route('admin.seminar.preview-notification', $seminar) }}`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            template_id: templateId,
                            recipient_type: recipientType
                        })
                    });
                    const data = await res.json();
                    document.getElementById('notif-preview-subject').value = data.subject || '';
                    document.getElementById('notif-preview-body').value = data.body || '';
                } catch (err) {
                    console.error(err);
                } finally {
                    loader.classList.add('hidden');
                    editor.classList.remove('hidden');
                }
            }

            document.addEventListener('change', function(e) {
                if (e.target.id === 'notif-template-select' || e.target.id === 'notif-recipient-select') {
                    updatePreview();
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('#btn-show-notification-preview')) {
                    Protekta.modal.show('notification-modal');
                    updatePreview();
                }

                if (e.target.closest('#btn-notif-send-email')) {
                    document.getElementById('notif-channel').value = 'email';
                }

                if (e.target.closest('#btn-notif-send-wa')) {
                    const body = document.getElementById('notif-preview-body').value;
                    const wa = document.getElementById('notif-preview-wa').value;
                    if (wa) {
                        window.open(`https://wa.me/${Protekta.helpers.formatWA(wa)}?text=${encodeURIComponent(body)}`, '_blank');
                    } else {
                        alert('Nomor WhatsApp tidak tersedia untuk penerima ini.');
                    }
                }
            });

        })();

        function openPdf(path) {
            if (!path) return;
            const baseUrl = '{{ route("admin.seminar.files.show", ["path" => "PATH_PLACEHOLDER"]) }}';
            window.open(baseUrl.replace('PATH_PLACEHOLDER', encodeURIComponent(path)), '_blank');
        }
    </script>
@endsection
