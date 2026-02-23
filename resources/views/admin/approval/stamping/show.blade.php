@extends('layouts.app')

@section('title', 'Bubuhkan Tanda Tangan')

@section('content')
<style>
    @font-face {
        font-family: 'Great Vibes';
        src: url('{{ asset("fonts/GreatVibes-Regular.ttf") }}') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }
</style>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 leading-none">Digital Stamping</h1>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold uppercase rounded-md border border-blue-100">
                    TTD {{ $approval->urutan }} - {{ $approval->role_nama ?: ($approval->role->nama ?? 'Pejabat') }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Bubuhkan tanda tangan untuk: <strong class="text-gray-900">{{ $approval->surat->jenis->nama ?? '-' }}</strong></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.approval.stamping.index') }}" class="btn-pill btn-pill-secondary !no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
    @php
        $surat = $approval->surat;
        $pemohon = $surat->pemohonDosen ?? $surat->pemohonMahasiswa ?? $surat->pemohonAdmin;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Detail Permohonan --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest">Detail Permohonan</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100 shrink-0">
                            {{ substr($pemohon->nama ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pemohon</p>
                            <p class="text-sm font-bold text-gray-900 leading-tight">{{ $pemohon->nama ?? 'Unknown' }}</p>
                            <p class="text-[10px] text-gray-500 font-mono mt-0.5">
                                @if($surat->pemohon_type === 'mahasiswa') MHS / {{ $surat->pemohonMahasiswa->npm }}
                                @elseif($surat->pemohon_type === 'dosen') DSN / {{ $surat->pemohonDosen->nip }}
                                @else ADMIN
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($surat->tujuan || $surat->perihal)
                        <div class="pt-4 border-t border-gray-50">
                            @if($surat->tujuan)
                                <div class="mb-3">
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Tujuan</p>
                                    <p class="text-xs text-gray-700 font-medium">{{ $surat->tujuan }}</p>
                                </div>
                            @endif
                            @if($surat->perihal)
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Perihal</p>
                                    <p class="text-xs text-gray-700 font-medium italic">"{{ $surat->perihal }}"</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">No. Surat</p>
                        <p class="text-xs font-bold text-gray-700">{{ $surat->no_surat ?: '(Draft)' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tanggal</p>
                        <p class="text-xs font-bold text-gray-700">{{ $surat->tanggal_surat ? $surat->tanggal_surat->translatedFormat('d F Y') : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status Utama</p>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                            @if($surat->status === 'diajukan') bg-blue-100 text-blue-700
                            @elseif($surat->status === 'diproses') bg-amber-100 text-amber-700
                            @elseif($surat->status === 'selesai' || $surat->status === 'dikirim') bg-emerald-100 text-emerald-700
                            @else bg-red-100 text-red-700
                            @endif
                        ">
                            {{ strtoupper($surat->status) }}
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Jenis Surat</p>
                        <p class="text-xs font-bold text-gray-700 truncate" title="{{ $surat->jenis->nama }}">{{ $surat->jenis->nama }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Tanda Tangan --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                <h3 class="text-[11px] font-bold text-gray-800 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-check-double text-blue-500"></i> Status Tanda Tangan
                </h3>
            </div>
            <div class="p-5 flex-1 overflow-y-auto max-h-[160px] custom-scrollbar">
                <div class="space-y-4">
                    @foreach($approvals as $app)
                        <div class="flex items-center gap-3 relative">
                            @if(!$loop->last)
                                <div class="absolute left-[11px] top-6 bottom-[-16px] w-[1px] bg-gray-100"></div>
                            @endif
                            
                            <div class="shrink-0 relative z-10 w-6 h-6 rounded-full flex items-center justify-center text-[10px] 
                                @if($app->status === 'approved') bg-emerald-100 text-emerald-600 border border-emerald-200
                                @elseif($app->status === 'rejected') bg-red-100 text-red-600 border border-red-200
                                @elseif($app->id === $approval->id) bg-blue-100 text-blue-600 border border-blue-200 ring-4 ring-blue-50
                                @else bg-amber-100 text-amber-600 border border-amber-200
                                @endif
                            ">
                                @if($app->status === 'approved') <i class="fas fa-check"></i>
                                @elseif($app->status === 'rejected') <i class="fas fa-times"></i>
                                @elseif($app->id === $approval->id) <i class="fas fa-pen-nib animate-pulse"></i>
                                @else <i class="fas fa-clock"></i>
                                @endif
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold leading-none {{ $app->id === $approval->id ? 'text-blue-700' : 'text-gray-700' }} truncate">
                                    {{ $app->role_nama ?: ($app->role->nama ?? 'Pejabat') }}
                                </p>
                                <p class="text-[9px] text-gray-400 mt-1 truncate">
                                    {{ $app->dosen->nama ?? '-' }}
                                </p>
                            </div>
                            
                            @if($app->status === 'approved' && $app->approved_at)
                                <div class="shrink-0 text-right">
                                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter">Selesai</p>
                                    <p class="text-[8px] text-gray-400 font-mono">{{ $app->approved_at->format('d/m y') }}</p>
                                </div>
                            @elseif($app->status === 'rejected')
                                <div class="shrink-0 text-right">
                                    <p class="text-[9px] font-bold text-red-600 uppercase tracking-tighter">Ditolak</p>
                                </div>
                            @elseif($app->status !== 'approved')
                                <div class="shrink-0 text-right">
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[9px] font-bold rounded-full border border-amber-200">Menunggu</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Persyaratan & Data + Diskusi (2 kolom) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" style="align-items: stretch;">
        {{-- Kiri: Persyaratan & Data --}}
        <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden min-h-[250px]">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-list-check text-blue-500"></i> Persyaratan & Data
            </h3>

            @if(!empty($surat->data) && is_array($surat->data))
                <div class="flex flex-col gap-4">
                    @foreach($surat->data as $key => $value)
                        @if(!empty($value))
                            @php
                                $isFile = is_string($value) && (
                                    str_contains($value, 'documents/') || 
                                    preg_match('/\.(pdf|jpg|jpeg|png|gif|doc|docx|xls|xlsx)$/i', $value)
                                );
                                $ext = $isFile ? strtolower(pathinfo($value, PATHINFO_EXTENSION)) : '';
                                $isPdf = $ext === 'pdf';
                                $isImage = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $label = ucwords(str_replace('_', ' ', $key));
                            @endphp

                            @if($isFile)
                                {{-- File Item --}}
                                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                                {{ $isPdf ? 'bg-red-50 text-red-500' : ($isImage ? 'bg-blue-50 text-blue-500' : 'bg-gray-100 text-gray-500') }}">
                                                <i class="fas {{ $isPdf ? 'fa-file-pdf' : ($isImage ? 'fa-file-image' : 'fa-file') }}"></i>
                                            </div>
                                            <div class="truncate">
                                                <p class="text-sm font-bold text-gray-700 truncate capitalize">{{ $label }} :</p>
                                                <p class="text-[10px] text-gray-400 font-mono">FILE TERUNGGAH</p>
                                            </div>
                                        </div>
                                        <a href="{{ url('/uploads/' . $value) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg hover:from-blue-600 hover:to-indigo-700 hover:-translate-y-0.5 transition-all duration-200 group shrink-0">
                                            <span>Buka Full</span>
                                            <i class="fas fa-external-link-alt group-hover:rotate-45 transition-transform duration-300"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                {{-- Text/Value Item --}}
                                <div class="border border-gray-200 rounded-2xl p-4 bg-gray-50/50 hover:bg-white transition-colors">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                                            <i class="fas fa-align-left"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-gray-700 capitalize mb-1">{{ $label }}</p>
                                            @if(is_array($value))
                                                <p class="text-xs text-gray-600 leading-relaxed">{{ implode(', ', $value) }}</p>
                                            @else
                                                <p class="text-xs text-gray-600 leading-relaxed">{{ $value }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-200 text-3xl mb-2"></i>
                    <p class="text-xs text-gray-400">Tidak ada data persyaratan.</p>
                </div>
            @endif
        </div>

        {{-- Kanan: Diskusi & Catatan --}}
        <div class="relative h-full min-h-[350px] lg:min-h-0">
            <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm flex flex-col h-full lg:absolute lg:inset-0 overflow-hidden">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2 shrink-0">
                    <i class="fas fa-comments text-orange-500"></i> Diskusi & Catatan
                </h3>

            {{-- Comments List --}}
            <div class="space-y-4 mb-4 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                @forelse($surat->comments as $comment)
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
                    <div class="text-center py-8">
                        <i class="fas fa-comment-slash text-gray-200 text-2xl mb-2"></i>
                        <p class="text-[11px] text-gray-400">Belum ada diskusi.</p>
                    </div>
                @endforelse
            </div>

            {{-- Comment Form --}}
            <div class="pt-4 border-t border-gray-100 mt-auto">
                <form action="{{ route('admin.surat.comment.store', $surat) }}" method="POST">
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

    {{-- Digital Stamping Editor --}}
    <div id="stamping-section" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 lg:p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col xl:flex-row gap-4 items-start xl:items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="shrink-0 w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-signature text-blue-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900 leading-tight mb-0.5 truncate">Editor Penempatan Tanda Tangan</h3>
                    <p class="text-[9px] md:text-[10px] text-gray-400 uppercase tracking-widest font-bold truncate">Tekan "Aktifkan Editor" untuk mulai menempatkan stempel</p>
                    
                    @php
                        $myPendingApprovals = $approvals->filter(function($app) use ($isAdmin) {
                            return !$app->isApproved() && ($isAdmin || $app->dosen_id == Auth::guard('dosen')->id());
                        });
                    @endphp

                    @if($myPendingApprovals->count() > 1)
                    <div class="mt-2 flex items-center gap-2 px-2 py-1 bg-amber-50 border border-amber-100 rounded-lg">
                        <i class="fas fa-info-circle text-amber-600 text-[10px]"></i>
                        <p class="text-[10px] font-bold text-amber-700">
                            Sistem mendeteksi Anda memiliki {{ $myPendingApprovals->count() }} peran dalam surat ini. Anda dapat menempatkan semua QR sekaligus.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-2 bg-white p-1.5 rounded-xl border border-gray-200 overflow-x-auto w-full xl:w-auto custom-scrollbar shadow-sm">
                 <button type="button" id="prev-page" class="p-2 hover:bg-gray-50 rounded-lg text-gray-600 transition-colors"><i class="fas fa-chevron-left"></i></button>
                 <div class="px-3 border-x border-gray-100 text-center min-w-[100px]">
                    <span class="text-xs font-bold text-gray-600">Hal. <span id="current-page-num" class="text-blue-600">1</span> / <span id="total-pages">...</span></span>
                 </div>
                 <button type="button" id="next-page" class="p-2 hover:bg-gray-50 rounded-lg text-gray-600 transition-colors"><i class="fas fa-chevron-right"></i></button>
                 <div class="w-px h-4 bg-gray-200 mx-1"></div>
                 <button type="button" id="zoom-out" class="p-2 hover:bg-gray-50 rounded-lg text-gray-600 transition-colors"><i class="fas fa-search-minus"></i></button>
                 <span id="zoom-level" class="text-xs font-bold text-gray-600 w-12 text-center">130%</span>
                 <button type="button" id="zoom-in" class="p-2 hover:bg-gray-50 rounded-lg text-gray-600 transition-colors"><i class="fas fa-search-plus"></i></button>
                 <div class="w-px h-4 bg-gray-200 mx-1"></div>
                 <button type="button" id="btn-fullscreen" class="p-2 hover:bg-blue-50 rounded-lg text-blue-600 transition-colors" title="Fullscreen">
                    <i class="fas fa-expand"></i>
                 </button>
            </div>
        </div>
        
        <div class="bg-gray-100 relative flex flex-col lg:flex-row">
            {{-- PDF Canvas Container --}}
            <div class="flex-1 p-12 flex justify-center bg-slate-300/50 backdrop-blur-sm shadow-inner overflow-visible relative" id="canvas-container">
                <div class="relative shadow-[0_25px_60px_rgba(0,0,0,0.2)] bg-white mb-20 transition-transform duration-300 origin-top" id="pdf-wrapper">
                    <canvas id="pdf-render"></canvas>
                    
                    {{-- Container for all draggable items --}}
                    <div id="stamps-container" class="absolute inset-0 z-40 pointer-events-none"></div>
                </div>
            </div>
            
            {{-- Stamping Controls Side Panel --}}
            <div class="lg:w-80 bg-white border-l border-gray-100 p-8 flex flex-col gap-8 shadow-2xl relative z-10 overflow-y-auto">
                @if($approvals->count() > 1)
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest leading-none">Pilih Penandatangan (Level)</label>
                        <select id="select-approval-level" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            @foreach($approvals as $app)
                                @php
                                    $canEdit = $isAdmin || (Auth::guard('dosen')->user() && $app->dosen_id == Auth::guard('dosen')->user()->id);
                                @endphp
                                @if($canEdit)
                                <option value="{{ $app->id }}" 
                                    {{ $approval->id == $app->id ? 'selected' : '' }}
                                    data-url="{{ route('admin.approval.stamping.process', $app) }}"
                                    data-x="{{ $app->stamp_x }}"
                                    data-y="{{ $app->stamp_y }}"
                                    data-width="{{ $app->stamp_width ?: 120 }}"
                                    data-height="{{ $app->stamp_height ?: 120 }}"
                                    data-page="{{ $app->stamp_page ?: 1 }}"
                                    data-additional="{{ json_encode($app->additional_stamps ?? []) }}">
                                    TTD {{ $app->urutan }} - {{ $app->role_nama ?: ($app->dosen->nama ?? 'Pejabat') }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($approval->isReady())
                    {{-- Control Buttons --}}
                    <div class="space-y-3">
                        <button type="button" id="toggle-drag" class="w-full py-4 rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/50 text-blue-700 font-bold text-xs hover:bg-blue-100 hover:border-blue-300 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-mouse-pointer"></i> Aktifkan Editor
                        </button>
                        
                        <button type="submit" form="stamp-form" id="submit-stamp" class="w-full py-4 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-xs shadow-xl shadow-blue-200 hover:shadow-2xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:grayscale disabled:pointer-events-none" disabled>
                            <i class="fas fa-check-circle mr-2 text-lg"></i> Simpan & Selesaikan
                        </button>
                    </div>

                    {{-- Add Items Toolbar --}}
                    <div id="add-items-toolbar" class="hidden space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 leading-none">Tambah Elemen</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="addTextStamp()" class="p-3 bg-gray-50 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 text-gray-600 hover:text-blue-600 transition-all flex flex-col items-center gap-1 text-xs font-bold">
                                <i class="fas fa-font text-lg"></i>
                                <span>Teks Custom</span>
                            </button>
                            <button type="button" onclick="addTagStamp('tanggal')" class="p-3 bg-gray-50 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 text-gray-600 hover:text-blue-600 transition-all flex flex-col items-center gap-1 text-xs font-bold">
                                <i class="fas fa-calendar-alt text-lg"></i>
                                <span>Tgl. Validasi</span>
                            </button>
                        </div>
                        
                        <button type="button" id="btn-add-qr" onclick="addQrStamp()" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl hover:bg-indigo-50 hover:border-indigo-200 text-gray-600 hover:text-indigo-600 transition-all flex flex-col items-center gap-1 text-xs font-bold hidden">
                            <i class="fas fa-qrcode text-lg"></i>
                            <span>Tambah Info QR Code di Hal <span id="btn-add-qr-page">Ini</span></span>
                        </button>
                        
                        <div class="grid grid-cols-1 gap-2">
                            <select id="tag-selector" onchange="if(this.value) { addTagStamp(this.value); this.value=''; }" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">+ Tambah Tag Lainnya...</option>
                                <option value="{no_surat}">Nomor Surat</option>
                                <option value="{tanggal_surat}">Tanggal Surat</option>
                                <option value="{waktu_ttd}">Waktu TTD (Jam:Menit)</option>
                                <option value="{nama_penandatangan}">Nama Penandatangan</option>
                                <option value="{nip_penandatangan}">NIP Penandatangan</option>
                                <option value="{jabatan_penandatangan}">Jabatan Penandatangan</option>
                            </select>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center mt-4">
                        <i class="fas fa-hourglass-half text-amber-500 text-2xl mb-2 block animate-pulse"></i>
                        <h4 class="font-bold text-amber-700 text-sm mb-1">Menunggu Persetujuan Sebelumnya</h4>
                        <p class="text-xs text-amber-600">Anda belum dapat menambahkan tanda tangan karena masih ada tahap sebelumnya yang belum disetujui.</p>
                    </div>
                @endif

                {{-- Selected Item Properties --}}
                <div id="item-properties" class="hidden space-y-4 border-t border-gray-100 pt-4">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 leading-none">Properti Item</h4>

                    <!-- Page Selector -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Halaman</label>
                        <select id="input-item-page" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <!-- Options populated dynamically -->
                        </select>
                        <p class="text-[9px] text-gray-400 mt-1">Pindahkan elemen ke halaman ini</p>
                    </div>

                    <div id="prop-text-content" class="hidden">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Konten Teks</label>
                        <input type="text" id="input-item-text" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Font & Style</label>
                        <select id="input-item-font" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2">
                            <option value="Arial" style="font-family: Arial, sans-serif">Arial</option>
                            <option value="Times" style="font-family: 'Times New Roman', Times, serif">Times New Roman</option>
                            <option value="Courier" style="font-family: 'Courier New', Courier, monospace">Courier New</option>
                            <option value="Helvetica" style="font-family: Helvetica, Arial, sans-serif">Helvetica</option>
                            <option value="Calibri" style="font-family: Calibri, 'Segoe UI', sans-serif">Calibri</option>
                            <option value="Greatvibes" style="font-family: 'Great Vibes', cursive">Great Vibes</option>
                            <option value="Tahoma" style="font-family: Tahoma, Geneva, sans-serif">Tahoma</option>
                            <option value="Verdana" style="font-family: Verdana, Geneva, sans-serif">Verdana</option>
                            <option value="Geneva" style="font-family: Geneva, Verdana, sans-serif">Geneva</option>
                            <option value="Impact" style="font-family: Impact, sans-serif">Impact</option>
                        </select>
                        <div class="flex gap-1">
                            <button type="button" id="btn-bold" class="flex-1 py-1 px-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100" title="Bold"><i class="fas fa-bold"></i></button>
                            <button type="button" id="btn-italic" class="flex-1 py-1 px-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100" title="Italic"><i class="fas fa-italic"></i></button>
                            <button type="button" id="btn-underline" class="flex-1 py-1 px-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100" title="Underline"><i class="fas fa-underline"></i></button>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ukuran Font</label>
                            <input type="number" id="input-item-fontsize" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" value="10" min="6" max="72">
                        </div>
                        <div class="flex-1">
                             <button type="button" id="btn-delete-item" class="w-full h-[38px] mt-[19px] bg-red-50 text-red-600 rounded-xl hover:bg-red-100 font-bold text-xs flex items-center justify-center gap-1">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <form id="stamp-form" action="{{ route('admin.approval.stamping.process', $approval) }}" method="POST" class="mt-auto">
                    @csrf
                    <input type="hidden" name="x" id="input-x" value="{{ $approval->stamp_x }}">
                    <input type="hidden" name="y" id="input-y" value="{{ $approval->stamp_y }}">
                    <input type="hidden" name="width" id="input-width" value="{{ $approval->stamp_width ?: 120 }}">
                    <input type="hidden" name="height" id="input-height" value="{{ $approval->stamp_height ?: 120 }}">
                    <input type="hidden" name="page" id="input-page" value="{{ $approval->stamp_page ?: 1 }}">
                    <input type="hidden" name="signature_type" value="qr">
                    <input type="hidden" name="additional_stamps" id="input-additional-stamps" value="{{ json_encode($approval->additional_stamps ?? []) }}">
                    <input type="hidden" name="bulk_stamping" value="true">
                    
                    {{-- Hidden input for full bulk data (used as alternative to AJAX) --}}
                    <input type="hidden" name="full_stamps_data" id="input-full-stamps-data">
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    (function() {
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        let pdfDoc = null,
            pageNum = {{ $approval->stamp_page ?: 1 }},
            pageRendering = false,
            pageNumPending = null,
            scale = 1.3,
            canvas = document.getElementById('pdf-render'),
            ctx = canvas.getContext('2d');

        const pdfWrapper = document.getElementById('pdf-wrapper');
        const stampsContainer = document.getElementById('stamps-container');
        const toggleBtn = document.getElementById('toggle-drag');
        const submitBtn = document.getElementById('submit-stamp');
        const addItemsToolbar = document.getElementById('add-items-toolbar');
        const itemProperties = document.getElementById('item-properties');
        const stampForm = document.getElementById('stamp-form');
        
        // Form Inputs
        const inputX = document.getElementById('input-x');
        const inputY = document.getElementById('input-y');
        const inputAdditionalStamps = document.getElementById('input-additional-stamps');

        // Property Inputs
        const inputItemText = document.getElementById('input-item-text');
        const inputItemFont = document.getElementById('input-item-font');
        const inputItemFontSize = document.getElementById('input-item-fontsize');
        const btnDeleteItem = document.getElementById('btn-delete-item');
        const propTextContent = document.getElementById('prop-text-content');
        
        // Style Toggles
        const btnBold = document.getElementById('btn-bold');
        const btnItalic = document.getElementById('btn-italic');
        const btnUnderline = document.getElementById('btn-underline');

        // Map dropdown values to proper CSS font-family strings
        const fontFamilyMap = {
            'Arial': 'Arial, Helvetica, sans-serif',
            'Times': "'Times New Roman', Times, serif",
            'Courier': "'Courier New', Courier, monospace",
            'Helvetica': 'Helvetica, Arial, sans-serif',
            'Calibri': "Calibri, 'Segoe UI', sans-serif",
            'Greatvibes': "'Great Vibes', cursive",
            'Tahoma': 'Tahoma, Geneva, sans-serif',
            'Verdana': 'Verdana, Geneva, sans-serif',
            'Geneva': 'Geneva, Verdana, sans-serif',
            'Impact': 'Impact, sans-serif',
        };

        let dragEnabled = false;
        let activeItem = null;
        let stamps = [];

        // Loading Data - Load all approval QRs as templates
        @php
            $allApprovalsData = $approvals->map(function($app) {
                return [
                    'approval_id' => $app->id,
                    'dosen_id' => $app->dosen_id,
                    'urutan' => $app->urutan,
                    'role_nama' => $app->role_nama ?: ($app->role->nama ?? 'Pejabat'),
                    'x' => $app->stamp_x ?: 50,
                    'y' => $app->stamp_y ?: 50,
                    'width' => $app->stamp_width ?: 120,
                    'height' => $app->stamp_height ?: 120,
                    'page' => $app->stamp_page ?: 1,
                    'is_stamped' => (bool)$app->is_stamped,
                    'additional_stamps' => $app->additional_stamps ?: []
                ];
            });
        @endphp
        const allApprovals = @json($allApprovalsData);

        const currentDosenId = @json(Auth::guard('dosen')->id() ?: (Auth::guard('admin')->check() ? null : Auth::id()));
        const isAdmin = @json($isAdmin);

        // Convert to stamp instances - each can exist on multiple pages
        allApprovals.forEach(app => {
            // Create QR stamp instance if:
            // 1. Already stamped
            // 2. Is the specific level we opened
            // 3. User is an admin (shows all)
            // 4. Current user is the assigned dosen for this role
            const isTargetLevel = (app.approval_id == {{ $approval->id }});
            const isMyLevel = isAdmin || (currentDosenId && app.dosen_id == currentDosenId);
            
            if (app.is_stamped || isTargetLevel || isMyLevel) {
                stamps.push({
                    id: 'qr_' + app.approval_id + '_p' + app.page,
                    approval_id: app.approval_id,
                    type: 'qr',
                    urutan: app.urutan,
                    role_nama: app.role_nama,
                    x: app.x,
                    y: app.y,
                    width: app.width,
                    height: app.height,
                    page: app.page,
                    is_stamped: app.is_stamped
                });
            }

            // Add Additional Stamps for this approval
            const extras = Array.isArray(app.additional_stamps) ? app.additional_stamps : [];
            extras.forEach((s, idx) => {
                stamps.push({
                    ...s,
                    id: s.id || ('extra_' + app.approval_id + '_' + idx),
                    approval_id: app.approval_id,
                    isBold: !!s.isBold,
                    isItalic: !!s.isItalic,
                    isUnderline: !!s.isUnderline
                });
            });
        });

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then((page) => {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                if (pdfWrapper) {
                    pdfWrapper.style.width = viewport.width + 'px';
                    pdfWrapper.style.height = viewport.height + 'px';
                }
                const renderContext = { canvasContext: ctx, viewport: viewport };
                page.render(renderContext).promise.then(() => {
                    pageRendering = false;
                    if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
                    renderStamps();
                });
            });
            document.getElementById('current-page-num').textContent = num;
            document.getElementById('input-page').value = num;
            
            // Render text for "Tambah QR Code" button with current page logic later covered in renderStamps
            const qrBtnPageText = document.getElementById('btn-add-qr-page');
            if (qrBtnPageText) qrBtnPageText.textContent = num;
        }

        function renderStamps() {
            stampsContainer.innerHTML = '';

            // Get current active level
            const select = document.getElementById('select-approval-level');
            const currentLevelId = select ? parseInt(select.value) : {{ $approval->id }};

            // Toggle Add QR Button visibility - check if QR exists on CURRENT PAGE only
            const btnAddQr = document.getElementById('btn-add-qr');
            if (btnAddQr) {
                const hasQrOnCurrentPage = stamps.some(s => s.type === 'qr' && s.approval_id == currentLevelId && s.page === pageNum);
                if (!hasQrOnCurrentPage) {
                    btnAddQr.classList.remove('hidden');
                    btnAddQr.style.display = 'flex';
                } else {
                    btnAddQr.classList.add('hidden');
                    btnAddQr.style.display = 'none';
                }
            }
            
            // Calculate scale factor to convert from stored coordinates (scale 1.3) to current canvas
            const storageScale = 1.3;
            const scaleRatio = scale / storageScale;
            const isAdminUser = {{ $isAdmin ? 'true' : 'false' }};

            stamps.forEach(stamp => {
                if (stamp.page !== pageNum) return;

                const isCurrentLevel = (stamp.approval_id == currentLevelId);
                const isMyLevel = isAdmin || (currentDosenId && stamp.dosen_id == currentDosenId);
                const canEditThis = isCurrentLevel || isMyLevel;

                const el = document.createElement('div');
                el.id = stamp.id;

                // Class calculation
                let baseClass = `absolute z-40 select-none`;
                if (canEditThis) {
                    baseClass += ` cursor-move group hover:ring-1 hover:ring-blue-300 pointer-events-auto`;
                    if (activeItem && activeItem.id === stamp.id) baseClass += ' ring-2 ring-blue-500 z-50';
                } else {
                    // Make inactive items clickable if editor is enabled
                    if (dragEnabled) {
                        baseClass += ` opacity-40 grayscale cursor-pointer hover:opacity-70 transition-opacity pointer-events-auto`;
                        el.title = "Klik untuk beralih ke penandatangan ini";
                    } else {
                        baseClass += ` opacity-40 grayscale pointer-events-none`;
                    }
                }

                el.className = baseClass;
                
                // Convert coordinates from storage scale (1.3) to current canvas scale
                el.style.left = (stamp.x * scaleRatio) + 'px';
                el.style.top = (stamp.y * scaleRatio) + 'px';

                if (stamp.type === 'qr') {
                    // Also scale QR dimensions
                    el.style.width = (stamp.width * scaleRatio) + 'px';
                    el.style.height = (stamp.height * scaleRatio) + 'px';
                    el.style.boxSizing = 'border-box';
                    el.style.padding = '0';
                    el.style.border = 'none';
                    
                    if (dragEnabled && canEditThis) {
                        el.style.outline = '2px dashed #3b82f6';
                        el.style.outlineOffset = '-2px';
                    }
                    
                    el.className += ' bg-blue-100/60 flex items-center justify-center border border-blue-300/50';
                    el.innerHTML = `
                        <i class="fas fa-qrcode text-4xl text-blue-600 opacity-60"></i>
                        <div class="text-[8px] font-bold text-white ${ canEditThis ? 'bg-blue-600' : 'bg-gray-500' } px-2 py-0.5 shadow-sm absolute top-0 left-0 w-full truncate leading-none">
                            ${ canEditThis ? '' : '🔒 ' }TTD ${stamp.urutan} - ${stamp.role_nama}
                        </div>
                        <div class="resize-handle absolute -bottom-2 -right-2 w-5 h-5 bg-blue-600 rounded cursor-nwse-resize shadow ${dragEnabled && canEditThis ? '' : 'hidden'}"></div>
                    `;
                } else {
                    el.style.padding = '0';
                    el.style.boxSizing = 'border-box';
                    if (dragEnabled && canEditThis) {
                        el.style.outline = '1px dashed #3b82f6';
                        el.style.outlineOffset = '2px';
                        el.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
                    }
                    el.style.fontFamily = fontFamilyMap[stamp.font] || stamp.font || 'Arial, sans-serif';
                    el.style.fontSize = (stamp.fontSize || 10) * scaleRatio + 'px';
                    el.style.fontWeight = stamp.isBold ? 'bold' : 'normal';
                    el.style.fontStyle = stamp.isItalic ? 'italic' : 'normal';
                    el.style.textDecoration = stamp.isUnderline ? 'underline' : 'none';
                    el.style.whiteSpace = 'nowrap';
                    el.style.lineHeight = '1';
                    if (stamp.type === 'tag') {
                        const appObj = allApprovals.find(a => a.approval_id == stamp.approval_id);
                        const suffix = appObj ? ` (TTD ${appObj.urutan})` : '';
                        el.textContent = `[${stamp.key}]${suffix}`;
                        el.style.color = '#2563eb';
                    } else {
                        el.textContent = stamp.text || 'Text';
                    }
                }

                if (dragEnabled) {
                    if (canEditThis) {
                        attachDragEvents(el, stamp);
                        el.onclick = (e) => { e.stopPropagation(); selectItem(stamp.id); };
                    } else {
                        // Handle click for inactive items -> Switch Level
                        el.onclick = (e) => {
                            e.stopPropagation();
                            if(select) {
                                select.value = stamp.approval_id;
                                select.dispatchEvent(new Event('change'));
                                setTimeout(() => {
                                    // Try to select the item after re-render
                                    // We need to find the new element ID because renderStamps clears innerHTML
                                    // Ideally selectItem simply works on ID.
                                    selectItem(stamp.id);
                                }, 100); 
                            }
                        };
                    }
                }
                
                stampsContainer.appendChild(el);
            });
        }

        function selectItem(id) {
            activeItem = stamps.find(s => s.id === id);
            
            // Sync level selector if needed
            const select = document.getElementById('select-approval-level');
            if (select && activeItem && select.value != activeItem.approval_id) {
                select.value = activeItem.approval_id;
            }

            Array.from(stampsContainer.children).forEach(c => c.id === id ? c.classList.add('ring-2', 'ring-blue-500', 'z-50') : c.classList.remove('ring-2', 'ring-blue-500', 'z-50'));
            itemProperties.classList.remove('hidden');
            
            // Update Page Selector
            const inputItemPage = document.getElementById('input-item-page');
            if (inputItemPage && pdfDoc) {
                inputItemPage.innerHTML = '';
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Halaman ${i}`;
                    if (activeItem.page === i) option.selected = true;
                    inputItemPage.appendChild(option);
                }
            }
            
            if (activeItem.type === 'text') { propTextContent.classList.remove('hidden'); inputItemText.value = activeItem.text; }
            else { propTextContent.classList.add('hidden'); }
            if (activeItem.type !== 'qr') {
                inputItemFont.value = activeItem.font || 'Arial';
                inputItemFontSize.value = activeItem.fontSize || 10;
                btnBold.parentElement.classList.remove('hidden');
                updateStyleButton(btnBold, activeItem.isBold);
                updateStyleButton(btnItalic, activeItem.isItalic);
                updateStyleButton(btnUnderline, activeItem.isUnderline);
                btnDeleteItem.disabled = false;
                btnDeleteItem.classList.remove('opacity-50');
            } else {
                btnBold.parentElement.classList.add('hidden');
                // Allow delete for QR
                btnDeleteItem.disabled = false;
                btnDeleteItem.classList.remove('opacity-50');
            }
        }

        function updateStyleButton(btn, active) { 
            if(active) btn.classList.add('bg-blue-50', 'text-blue-600', 'border-blue-200');
            else btn.classList.remove('bg-blue-50', 'text-blue-600', 'border-blue-200');
        }

        function attachDragEvents(el, stamp) {
            el.onmousedown = (e) => {
                if(e.target.classList.contains('resize-handle')) { handleResize(e, stamp); return; }
                e.preventDefault(); e.stopPropagation(); selectItem(stamp.id);
                
                // Get current scale ratio
                const storageScale = 1.3;
                const scaleRatio = scale / storageScale;
                
                let startX = e.clientX, startY = e.clientY;
                // Store the current position in display coordinates
                let startDisplayLeft = parseFloat(el.style.left) || 0;
                let startDisplayTop = parseFloat(el.style.top) || 0;
                
                document.onmousemove = (e) => {
                    let wrapperRect = pdfWrapper.getBoundingClientRect();
                    
                    // Calculate mouse movement in screen pixels
                    let shiftX = e.clientX - startX;
                    let shiftY = e.clientY - startY;
                    
                    // Calculate new position in display coordinates
                    let newDisplayX = startDisplayLeft + shiftX;
                    let newDisplayY = startDisplayTop + shiftY;
                    
                    // Bounds checking
                    if(newDisplayX < 0) newDisplayX = 0; if(newDisplayY < 0) newDisplayY = 0;
                    if(newDisplayX + el.offsetWidth > wrapperRect.width) newDisplayX = wrapperRect.width - el.offsetWidth;
                    if(newDisplayY + el.offsetHeight > wrapperRect.height) newDisplayY = wrapperRect.height - el.offsetHeight;
                    
                    // Convert to storage coordinates (scale 1.3)
                    let newStorageX = Math.round(newDisplayX / scaleRatio);
                    let newStorageY = Math.round(newDisplayY / scaleRatio);
                    
                    // Validate: ensure finite and positive values
                    if (!isFinite(newStorageX) || newStorageX < 0) newStorageX = 0;
                    if (!isFinite(newStorageY) || newStorageY < 0) newStorageY = 0;
                    
                    stamp.x = newStorageX;
                    stamp.y = newStorageY;
                    
                    el.style.left = newDisplayX + 'px'; el.style.top = newDisplayY + 'px';
                };
                document.onmouseup = () => {
                    saveStampsData();
                    document.onmousemove = null; document.onmouseup = null;
                };
            };
        }

        function handleResize(e, stamp) {
            e.preventDefault(); e.stopPropagation();
            
            // Get current scale ratio
            const storageScale = 1.3;
            const scaleRatio = scale / storageScale;
            
            let startX = e.clientX, startWidth = Math.max(40, stamp.width) / scaleRatio;
            document.onmousemove = (e) => {
                let newDisplayWidth = startWidth * scaleRatio + (e.clientX - startX);
                if(newDisplayWidth < 40) newDisplayWidth = 40; if(newDisplayWidth > 300) newDisplayWidth = 300;
                
                // Convert back to storage coordinates (scale 1.3) with validation
                let newStorageWidth = Math.round(newDisplayWidth / scaleRatio);
                if (!isFinite(newStorageWidth) || newStorageWidth < 40) newStorageWidth = 40;
                
                stamp.width = newStorageWidth;
                stamp.height = newStorageWidth;
                
                renderStamps(); saveStampsData();
            };
            document.onmouseup = () => { document.onmousemove = null; document.onmouseup = null; };
        }

        function saveStampsData() {
            const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
            
            // Find the MAIN QR for this approval (first stamped one, or first on page 1)
            let mainQr = stamps.find(s => s.type === 'qr' && s.approval_id == currentLevelId && s.is_stamped);
            if (!mainQr) {
                mainQr = stamps.find(s => s.type === 'qr' && s.approval_id == currentLevelId);
            }
            
            // Additional stamps include text, tags, and extra QR instances on other pages
            // Validate and sanitize before saving
            const validateStamp = (val, defaultVal) => {
                if (val === null || val === undefined || !isFinite(val)) return defaultVal;
                return val;
            };
            
            inputAdditionalStamps.value = JSON.stringify(
                stamps.filter(s => s.approval_id == currentLevelId && s.id !== (mainQr ? mainQr.id : null)).map(s => ({
                    ...s,
                    x: validateStamp(s.x, 0),
                    y: validateStamp(s.y, 0),
                    width: Math.max(0, validateStamp(s.width, 0)),
                    height: Math.max(0, validateStamp(s.height, 0)),
                    fontSize: validateStamp(s.fontSize, 10),
                    page: validateStamp(s.page, 1)
                }))
            );

            // Populating full data input for bulk handling via Controller's stamp() method
            // Sending raw array of stamps to avoid double-encoding issues
            document.getElementById('input-full-stamps-data').value = JSON.stringify(stamps);

            // Update main hidden inputs for legacy single-process
            if (mainQr) {
                if(inputX) inputX.value = mainQr.x;
                if(inputY) inputY.value = mainQr.y;
                const inputWidth = document.getElementById('input-width');
                const inputHeight = document.getElementById('input-height');
                if(inputWidth) inputWidth.value = mainQr.width;
                if(inputHeight) inputHeight.value = mainQr.height;
            }
        }

        // Toolbar
        window.addTextStamp = () => { 
            const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
            stamps.push({
                id: 'n'+Date.now(), 
                approval_id: currentLevelId,
                type: 'text', text: 'Teks Baru', x: 50, y: 150, page: pageNum, font: 'Arial', fontSize: 10,
                isBold: false, isItalic: false, isUnderline: false
            }); 
            renderStamps(); 
            selectItem(stamps[stamps.length-1].id); 
            saveStampsData(); 
        };
        window.addTagStamp = (k) => { 
            const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
            stamps.push({
                id: 'n'+Date.now(), 
                approval_id: currentLevelId,
                type: 'tag', key: k, x: 50, y: 180, page: pageNum, font: 'Arial', fontSize: 10,
                isBold: false, isItalic: false, isUnderline: false
            }); 
            renderStamps(); 
            selectItem(stamps[stamps.length-1].id); 
            saveStampsData(); 
        };
        window.addQrStamp = () => {
             const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
             let templateApp = allApprovals.find(a => a.approval_id == currentLevelId);
             
             stamps.push({
                 id: 'qr_' + currentLevelId + '_p' + pageNum,
                 approval_id: currentLevelId,
                 type: 'qr',
                 urutan: templateApp ? templateApp.urutan : 1,
                 role_nama: templateApp ? templateApp.role_nama : 'Pejabat',
                 x: 50,
                 y: 50,
                 width: 120,
                 height: 120,
                 page: pageNum,
                 is_stamped: false
             });
             renderStamps();
             selectItem(stamps[stamps.length-1].id);
             saveStampsData();
        };

        btnBold.onclick = () => { if(activeItem && activeItem.type!=='qr'){ activeItem.isBold = !activeItem.isBold; updateStyleButton(btnBold, activeItem.isBold); renderStamps(); saveStampsData(); } };
        btnItalic.onclick = () => { if(activeItem && activeItem.type!=='qr'){ activeItem.isItalic = !activeItem.isItalic; updateStyleButton(btnItalic, activeItem.isItalic); renderStamps(); saveStampsData(); } };
        btnUnderline.onclick = () => { if(activeItem && activeItem.type!=='qr'){ activeItem.isUnderline = !activeItem.isUnderline; updateStyleButton(btnUnderline, activeItem.isUnderline); renderStamps(); saveStampsData(); } };
        inputItemText.oninput = () => { if(activeItem && activeItem.type==='text'){ activeItem.text = inputItemText.value; renderStamps(); saveStampsData(); } };
        inputItemFont.onchange = () => { if(activeItem){ activeItem.font = inputItemFont.value; renderStamps(); saveStampsData(); } };
        inputItemFontSize.oninput = () => { if(activeItem){ activeItem.fontSize = parseInt(inputItemFontSize.value)||10; renderStamps(); saveStampsData(); } };
        
        // Page Selector Listener
        const inputItemPage = document.getElementById('input-item-page');
        if (inputItemPage) {
            inputItemPage.onchange = () => { if(activeItem){ activeItem.page = parseInt(inputItemPage.value); renderStamps(); saveStampsData(); } };
        }
        
        btnDeleteItem.onclick = () => {
            if (activeItem) {
                stamps = stamps.filter(s => s.id !== activeItem.id);
                activeItem = null;
                itemProperties.classList.add('hidden');
                renderStamps();
                saveStampsData();
            }
        };

        // PDF Load
        pdfjsLib.getDocument("{{ route('uploads.show', $approval->surat->uploaded_pdf_path) }}").promise.then(p => { pdfDoc = p; document.getElementById('total-pages').textContent = p.numPages; renderPage(pageNum); });
        
        document.getElementById('prev-page').onclick = () => { if(pageNum > 1) { pageNum--; renderPage(pageNum); } };
        document.getElementById('next-page').onclick = () => { if(pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } };
        document.getElementById('zoom-in').onclick = () => { scale += 0.2; renderPage(pageNum); document.getElementById('zoom-level').textContent = Math.round(scale*100)+'%'; };
        document.getElementById('zoom-out').onclick = () => { if(scale > 0.6) { scale -= 0.2; renderPage(pageNum); document.getElementById('zoom-level').textContent = Math.round(scale*100)+'%'; } };
        
        // Fullscreen Toggle
        let isFullscreen = false;
        const stampingSection = document.getElementById('stamping-section');
        const canvasContainer = document.getElementById('canvas-container');
        const sidePanel = document.querySelector('.lg\\:w-80.bg-white.border-l');
        const fullscreenBtn = document.getElementById('btn-fullscreen');
        
        if (fullscreenBtn && canvasContainer) {
            fullscreenBtn.onclick = () => {
                isFullscreen = !isFullscreen;
                
                if (isFullscreen) {
                    // Enter fullscreen
                    document.documentElement.classList.add('overflow-hidden');
                    
                    // Make container fullscreen
                    if (stampingSection) {
                        stampingSection.style.position = 'fixed';
                        stampingSection.style.top = '1rem';
                        stampingSection.style.left = '1rem';
                        stampingSection.style.right = '1rem';
                        stampingSection.style.bottom = '1rem';
                        stampingSection.style.zIndex = '9999';
                        stampingSection.style.height = 'calc(100vh - 2rem)';
                    }
                    
                    // Keep side panel visible but make it overlay
                    if (sidePanel) {
                        sidePanel.style.position = 'absolute';
                        sidePanel.style.right = '0';
                        sidePanel.style.top = '0';
                        sidePanel.style.bottom = '0';
                        sidePanel.style.zIndex = '50';
                        sidePanel.style.height = '100%';
                        sidePanel.style.boxShadow = '-4px 0 20px rgba(0,0,0,0.1)';
                    }
                    
                    // Expand canvas container to full width
                    if (canvasContainer.parentElement) {
                        canvasContainer.parentElement.style.display = 'flex';
                        canvasContainer.parentElement.style.flexDirection = 'row';
                    }
                    
                    // Update button icon
                    fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
                    fullscreenBtn.classList.remove('text-blue-600', 'hover:bg-blue-50');
                    fullscreenBtn.classList.add('text-emerald-600', 'hover:bg-emerald-50');
                    fullscreenBtn.title = 'Exit Fullscreen';
                } else {
                    // Exit fullscreen
                    document.documentElement.classList.remove('overflow-hidden');
                    
                    // Restore container
                    if (stampingSection) {
                        stampingSection.style.position = '';
                        stampingSection.style.top = '';
                        stampingSection.style.left = '';
                        stampingSection.style.right = '';
                        stampingSection.style.bottom = '';
                        stampingSection.style.zIndex = '';
                        stampingSection.style.height = '';
                    }
                    
                    // Restore side panel
                    if (sidePanel) {
                        sidePanel.style.position = '';
                        sidePanel.style.right = '';
                        sidePanel.style.top = '';
                        sidePanel.style.bottom = '';
                        sidePanel.style.zIndex = '';
                        sidePanel.style.height = '';
                        sidePanel.style.boxShadow = '';
                    }
                    
                    // Restore canvas container layout
                    if (canvasContainer.parentElement) {
                        canvasContainer.parentElement.style.display = '';
                        canvasContainer.parentElement.style.flexDirection = '';
                    }
                    
                    // Update button icon
                    fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
                    fullscreenBtn.classList.remove('text-emerald-600', 'hover:bg-emerald-50');
                    fullscreenBtn.classList.add('text-blue-600', 'hover:bg-blue-50');
                    fullscreenBtn.title = 'Fullscreen';
                }
                
                // Re-render page to adjust canvas
                setTimeout(() => {
                    renderPage(pageNum);
                }, 100);
            };
        }

        toggleBtn.onclick = () => {
            dragEnabled = !dragEnabled;
            renderStamps();
            if (dragEnabled) {
                toggleBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Kembali';
                toggleBtn.className = toggleBtn.className.replace('bg-blue-50/50', 'bg-red-50').replace('text-blue-700', 'text-red-700').replace('border-blue-200', 'border-red-200');
                submitBtn.disabled = false; addItemsToolbar.classList.remove('hidden');
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-mouse-pointer mr-2"></i> Aktifkan Editor';
                toggleBtn.className = toggleBtn.className.replace('bg-red-50', 'bg-blue-50/50').replace('text-red-700', 'text-blue-700').replace('border-red-200', 'border-blue-200');
                submitBtn.disabled = true; addItemsToolbar.classList.add('hidden'); itemProperties.classList.add('hidden'); activeItem = null;
            }
        };

        // Approval Level Switch
        const selectLevel = document.getElementById('select-approval-level');
        if (selectLevel) {
            selectLevel.addEventListener('change', () => {
                const newLevelId = parseInt(selectLevel.value);
                
                // Deselect active item if it belongs to another level
                if (activeItem) {
                    activeItem = null;
                    itemProperties.classList.add('hidden');
                }
                
                // Find and select the QR stamp for this level if it exists and is not stamped
                const qrForLevel = stamps.find(s => s.type === 'qr' && s.approval_id === newLevelId && !s.is_stamped);
                if (qrForLevel) {
                    // Switch to the page where this QR is located
                    if (qrForLevel.page !== pageNum) {
                        pageNum = qrForLevel.page;
                        renderPage(pageNum);
                    }
                    // Select the QR item
                    setTimeout(() => {
                        selectItem(qrForLevel.id);
                    }, 100);
                }
                
                renderStamps();
            });
        }

        // Bulk Submit
        // Standard Submit (No AJAX)
        if (stampForm) {
            stampForm.addEventListener('submit', (e) => {
                // Ensure data is synced before submit
                saveStampsData();
                
                const btn = document.getElementById('submit-stamp');
                // Don't disable immediately or the form might not submit the button value if needed, 
                // but simpler: just show loading state.
                // We assume the browser handles the POST.
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                // btn.disabled = true; // Careful, disabling might prevent submit if not timed right. 
                // Let the form submit naturally.
            });
        }
    })();
</script>
@endsection
