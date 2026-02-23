@extends('layouts.app')

@section('title', 'Detail Surat')

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
<div id="surat-show-container" data-id="{{ $surat->id }}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 leading-none">Detail Surat</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Jenis: <strong class="text-gray-900">{{ $surat->jenis->nama ?? '-' }}</strong></p>
        </div>
        <div class="flex items-center gap-2">
            @if($surat->status === 'diajukan')
                <form action="{{ route('admin.surat.approve', $surat) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-success !no-underline" onclick="return confirm('Proses surat ini dan teruskan ke penandatangan?')">
                        Proses
                    </button>
                </form>
            @endif
            @if($surat->status !== 'ditolak' && $surat->status !== 'selesai')
                <form action="{{ route('admin.surat.reject', $surat) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-pill btn-pill-danger !no-underline" onclick="return confirm('Tolak permohonan surat ini?')">
                        Tolak
                    </button>
                </form>
            @endif
            <button type="button" onclick="document.getElementById('main-surat-form').submit()" class="btn-pill btn-pill-primary shadow-lg shadow-blue-100 !no-underline flex items-center gap-2">
                <i class="fas fa-save"></i>
                Simpan
            </button>
            <a href="{{ route('admin.surat.index') }}" class="btn-pill btn-pill-secondary !no-underline">
                Kembali
            </a>
            <form method="POST" action="{{ route('admin.surat.destroy', $surat) }}" onsubmit="return confirm('Hapus permohonan surat ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-10 h-10 rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 flex items-center justify-center transition-colors" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Content -->
        <div class="lg:col-span-2 space-y-6">
            <form id="main-surat-form" method="POST" action="{{ route('admin.surat.update', $surat) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-col md:flex-row gap-6 mb-8 items-start">
                        <!-- Applicant Info (Compact) -->
                        <div class="flex items-center gap-3 bg-gray-50/50 px-4 py-3 rounded-xl border border-gray-100 min-w-[260px]">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                                {{ substr($surat->pemohonDosen->nama ?? $surat->pemohonMahasiswa->nama ?? $surat->pemohonAdmin->nama ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 text-sm leading-tight">
                                    {{ $surat->pemohonDosen->nama ?? $surat->pemohonMahasiswa->nama ?? $surat->pemohonAdmin->nama ?? 'Unknown' }}
                                </div>
                                <div class="text-[9px] uppercase font-bold tracking-widest text-gray-500 mt-0.5">
                                    @if($surat->pemohon_type === 'admin')
                                        Admin
                                    @elseif($surat->pemohon_type === 'mahasiswa')
                                        MHS / {{ $surat->pemohonMahasiswa->npm ?? '-' }}
                                    @elseif($surat->pemohon_type === 'dosen')
                                        DSN / {{ $surat->pemohonDosen->nip ?? '-' }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Basic Fields Row -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">No. Surat</label>
                                <input name="no_surat" id="no_surat" value="{{ old('no_surat', $surat->no_surat) }}" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" placeholder="Otomatis">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tanggal</label>
                                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d')) }}" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Progress</label>
                                <select name="status" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm font-medium focus:ring-1 focus:ring-blue-500" required>
                                    @foreach(['diajukan','diproses','selesai','ditolak'] as $st)
                                        <option value="{{ $st }}" {{ old('status', $surat->status) === $st ? 'selected' : '' }} class="
                                            @if($st === 'selesai') text-emerald-600 font-bold 
                                            @elseif($st === 'ditolak') text-red-600
                                            @endif
                                        ">
                                            {{ ucfirst($st) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-50">
                        <div class="flex items-center gap-3 mb-4">
                            <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Isian Data Permohonan</h2>
                            <div class="h-px flex-1 bg-gray-50"></div>
                        </div>
                        
                        <div id="dynamic-fields-admin" class="space-y-4">
                            <div class="md:col-span-2 py-10 text-center text-gray-400 italic">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi Form...
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-50 mt-4">
                        <div class="text-xs text-gray-400">
                            Sistem Ref: <strong>#{{ $surat->id }}</strong>
                        </div>
                        <button class="btn-pill btn-pill-primary px-10 shadow-lg shadow-blue-100" type="submit">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <!-- Signature Status -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Status Tanda Tangan</h3>
                </div>
                <div class="p-6">
                    @if($surat->approvals->count() > 0)
                        <div class="space-y-4">
                            @foreach($surat->approvals->sortBy('urutan') as $app)
                                <div class="flex items-start gap-3 relative">
                                    {{-- Connector Line --}}
                                    @if(!$loop->last)
                                        <div class="absolute left-[11px] top-7 bottom-[-16px] w-0.5 bg-gray-100"></div>
                                    @endif

                                    <div class="relative z-10 shrink-0">
                                        @if($app->status === 'approved')
                                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[10px] ring-2 ring-white">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        @elseif($app->status === 'rejected')
                                            <div class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[10px] ring-2 ring-white">
                                                <i class="fas fa-times"></i>
                                            </div>
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-[10px] ring-2 ring-white">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="pt-0.5">
                                        <p class="text-xs font-bold text-gray-800 leading-none mb-1">
                                            TTD {{ $app->urutan }}: {{ $app->role_nama ?: 'Pejabat' }}
                                        </p>
                                        <p class="text-[10px] leading-relaxed">
                                            @if($app->status === 'approved')
                                                <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Sudah ditandatangani</span>
                                                <span class="block text-gray-400 mt-0.5">{{ $app->resolved_signer_name }}</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded">Ditolak</span>
                                            @else
                                                <span class="text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded">Menunggu tanda tangan</span>
                                                <span class="block text-gray-400 mt-0.5">
                                                    {{ $app->resolved_signer_name }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>



                    @else
                        {{-- Fallback for no approvals --}}
                        <div class="text-center py-5">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-50 rounded-2xl mb-3 text-gray-300">
                                <i class="fas fa-info-circle text-xl"></i>
                            </div>
                            <p class="text-sm text-gray-500 font-bold">Tidak ada alur tanda tangan</p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold tracking-wider">{{ $surat->workflow_status_text }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @php
                // Show ALL approval stages for Admin overview & control
                $adminActions = $surat->approvals->sortBy('urutan');
            @endphp

            @if($adminActions->isNotEmpty())
                <div class="bg-slate-50 rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-100/50 flex items-center gap-2">
                        <i class="fas fa-shield-alt text-slate-700"></i>
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Log & Kendali Persetujuan</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-2">
                            <p class="text-[10px] text-blue-800 leading-normal">
                                <i class="fas fa-history mr-1 text-blue-600"></i>
                                Berikut adalah riwayat lengkap dan panel kendali untuk setiap tahap persetujuan dokumen ini.
                            </p>
                        </div>
                        
                        @foreach($adminActions as $app)
                             <div class="p-4 bg-white border border-slate-100 rounded-xl shadow-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahap {{ $app->urutan }}: {{ $app->role_nama }}</span>
                                    
                                    @if($app->status === 'approved')
                                        <span class="text-[9px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-bold flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    @elseif($app->status === 'rejected')
                                        <span class="text-[9px] bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-bold flex items-center gap-1">
                                            <i class="fas fa-times-circle"></i> Ditolak
                                        </span>
                                    @elseif(!$app->isReady())
                                        <span class="text-[9px] bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full font-bold">Menunggu Giliran</span>
                                    @else
                                        <span class="text-[9px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold flex items-center gap-1 animate-pulse">
                                            <i class="fas fa-arrow-right"></i> Sedang Diproses
                                        </span>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <p class="text-[10px] text-slate-600 font-medium">Target: <strong>{{ $app->resolved_signer_name }}</strong></p>
                                    
                                    @if($app->status === 'approved')
                                        <p class="text-[9px] text-slate-400 mt-1 italic">
                                            Disetujui pada {{ $app->approved_at?->format('d/m/Y H:i') }}
                                        </p>
                                    @elseif($app->status === 'rejected')
                                        <div class="mt-2 p-2 bg-red-50 border border-red-100 rounded text-[9px] text-red-700 italic">
                                            "{{ $app->catatan ?? 'Tidak ada alasan' }}"
                                        </div>
                                    @endif
                                </div>
                                
                                @if($app->isPending())
                                    @if($app->isReady())
                                        <form action="{{ route('admin.approval.approve', $app) }}" method="POST" class="approve-form-multi mb-2">
                                            @csrf
                                            <input type="hidden" name="signature_type" value="canvas"> 
                                            <button type="submit" class="w-full py-2 bg-slate-800 text-white rounded-lg font-bold hover:bg-slate-900 transition-all text-xs flex items-center justify-center gap-2">
                                                <i class="fas fa-check-double"></i> Validasi Sekarang
                                            </button>
                                        </form>
                                        
                                        <button type="button" data-app-id="{{ $app->id }}" class="btn-show-reject w-full py-2 text-red-600 border border-red-50 rounded-lg font-semibold hover:bg-red-50 transition-all text-[10px]">
                                            Tolak
                                        </button>
                                        <form id="reject-form-{{ $app->id }}" action="{{ route('admin.approval.reject', $app) }}" method="POST" class="hidden mt-3">
                                            @csrf
                                            <textarea name="reason" rows="2" required class="w-full px-3 py-2 border border-red-200 rounded-lg text-xs mb-2" placeholder="Alasan..."></textarea>
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 py-1.5 bg-red-600 text-white rounded font-bold text-[10px]">Ya, Tolak</button>
                                                <button type="button" data-app-id="{{ $app->id }}" class="btn-cancel-reject flex-1 py-1.5 bg-slate-100 text-slate-600 rounded font-bold text-[10px]">Batal</button>
                                            </div>
                                        </form>
                                    @else
                                        <p class="text-[10px] text-slate-400 italic text-center py-1">Menunggu penyelesaian tahap sebelumnya untuk dapat divalidasi manual.</p>
                                    @endif
                                @endif
                             </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Preview dan Cetak Surat</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($surat->status !== 'ditolak')
                        {{-- If it's an uploaded PDF, show main preview for the uploaded/stamped PDF --}}
                        @if($surat->jenis?->is_uploaded)
                            <a href="{{ route('admin.surat.preview', $surat) }}" target="_blank" class="w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all">
                                <i class="fas fa-file-pdf"></i> Pratinjau & Cetak PDF TTD
                            </a>
                        @endif

                        {{-- List all available templates --}}
                        @php
                            $templates = $surat->jenis?->templates()->where('aktif', true)->get() ?? collect();
                        @endphp

                        @forelse($templates as $template)
                            <a href="{{ route('admin.surat.preview-html', ['surat' => $surat, 'template' => $template]) }}" target="_blank" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-2 hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                                <i class="fas fa-eye"></i> Pratinjau & Cetak {{ $template->nama }}
                            </a>
                        @empty
                            @if(!$surat->jenis?->is_uploaded)
                                <a href="{{ route('admin.surat.preview-html', $surat) }}" target="_blank" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-2 hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                                    <i class="fas fa-eye"></i> Pratinjau & Cetak Surat
                                </a>
                            @endif
                        @endforelse
                    @else
                        <div class="p-4 bg-red-50 text-red-600 border border-red-100 rounded-xl text-xs font-medium text-center">
                            <i class="fas fa-ban mr-1"></i> Dokumen dikunci karena ditolak
                        </div>
                    @endif
                </div>
            </div>

            <form id="email-send-form" method="POST" action="{{ route('admin.surat.send-email', $surat) }}" class="hidden">
                 @csrf
                 <input type="hidden" name="recipient" id="hidden-recipient">
                 <input type="hidden" name="subject" id="hidden-email-subject">
                 <textarea name="body" id="hidden-email-body" class="hidden"></textarea>
            </form>
        </div>
    </div> <!-- Close Grid -->

    <!-- Digital Stamping (Full Width Section Below) -->
    @if($surat->jenis?->is_uploaded && $approval)
        <div id="stamping-section" class="mt-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-signature text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 leading-none mb-1">Digital Stamping (QR & Teks)</h3>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Pemosisian Stamp & Atribut Tambahan</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-white p-1 rounded-xl border border-gray-200">
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
                
                <div class="bg-gray-100 relative flex flex-col lg:flex-row" style="height: 85vh;">
                    {{-- PDF Canvas Container --}}
                    <div class="flex-1 p-12 flex justify-center bg-slate-300/50 backdrop-blur-sm shadow-inner overflow-auto relative" id="canvas-container">
                        <div class="relative shadow-[0_25px_60px_rgba(0,0,0,0.2)] bg-white mb-20 transition-transform duration-300 origin-top" id="pdf-wrapper">
                            <canvas id="pdf-render"></canvas>
                            
                            {{-- Container for all draggable items --}}
                            <div id="stamps-container" class="absolute inset-0 z-40 pointer-events-none"></div>
                        </div>
                    </div>
                    
                    {{-- Stamping Controls Side Panel --}}
                    <div class="lg:w-80 bg-white border-l border-gray-100 p-6 flex flex-col gap-6 shadow-2xl relative z-10 overflow-y-auto shrink-0">
                        
                        <!-- Control Buttons (Moved to Top) -->
                        <div class="space-y-3">
                            @if($approvals->count() > 1)
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Pilih Penandatangan (Level)</label>
                                    <select id="select-approval-level" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs font-bold text-gray-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                                        @foreach($approvals as $app)
                                            <option value="{{ $app->id }}" 
                                                {{ $approval->id == $app->id ? 'selected' : '' }}
                                                data-url="{{ route('admin.approval.stamping.process', $app) }}"
                                                data-x="{{ $app->stamp_x }}"
                                                data-y="{{ $app->stamp_y }}"
                                                data-width="{{ $app->stamp_width ?: 120 }}"
                                                data-height="{{ $app->stamp_height ?: 120 }}"
                                                data-page="{{ $app->stamp_page ?: 1 }}"
                                                data-additional="{{ json_encode($app->additional_stamps ?? []) }}">
                                                TTD {{ $app->urutan }} - {{ $app->resolved_signer_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <button type="button" id="toggle-drag" class="w-full py-4 rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/50 text-blue-700 font-bold text-xs hover:bg-blue-100 hover:border-blue-300 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-mouse-pointer"></i> Aktifkan Editor
                            </button>
                            
                            <button type="submit" form="stamp-form" id="submit-stamp" class="w-full py-4 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-xs shadow-xl shadow-blue-200 hover:shadow-2xl hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:grayscale disabled:pointer-events-none" disabled>
                                <i class="fas fa-check-circle mr-2 text-lg"></i> Simpan & Perbarui PDF
                            </button>
                        </div>

                        {{-- Add Items Toolbar --}}
                        <div id="add-items-toolbar" class="hidden space-y-4">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 leading-none">Tambah Elemen</h4>
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Dynamic Signer Selector --}}
                                <div class="col-span-2 space-y-2 mb-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih Penanda Tangan</label>
                                    <select id="signer-selector" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                                        @foreach($approvals as $app)
                                            <option value="{{ $app->id }}">
                                                TTD {{ $app->urutan }} - {{ $app->resolved_signer_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" onclick="addSelectedQrStamp()" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-qrcode"></i> Bubuhkan QR Tanda Tangan
                                    </button>
                                </div>

                                <button type="button" onclick="addSuratQrStamp()" class="col-span-2 py-3 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 hover:border-indigo-300 text-indigo-600 transition-all flex flex-col items-center gap-1 text-xs font-bold">
                                    <i class="fas fa-qrcode text-lg"></i>
                                    <span>QR Validasi Surat</span>
                                </button>

                                <button type="button" onclick="addTextStamp()" class="p-3 bg-gray-50 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 text-gray-600 hover:text-blue-600 transition-all flex flex-col items-center gap-1 text-xs font-bold">
                                    <i class="fas fa-font text-lg"></i>
                                    <span>Teks Custom</span>
                                </button>
                                <button type="button" onclick="addTagStamp('tanggal')" class="p-3 bg-gray-50 border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 text-gray-600 hover:text-blue-600 transition-all flex flex-col items-center gap-1 text-xs font-bold">
                                    <i class="fas fa-calendar-alt text-lg"></i>
                                    <span>Tgl. Validasi</span>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 gap-2">
                                <select onchange="if(this.value){ const data = JSON.parse(this.value); addQrStampByText(data.name, data.nip, data.role); this.value=''; }" class="w-full px-3 py-2 border border-blue-200 rounded-xl text-xs font-bold text-blue-700 bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">+ Bubuhkan QR Penandatangan...</option>
                                    @if(isset($dosens))
                                        <optgroup label="Dosen">
                                            @foreach($dosens as $d)
                                                <option value="{{ json_encode(['name' => $d->nama, 'nip' => $d->nip, 'role' => 'Dosen']) }}">{{ $d->nama }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($admins))
                                        <optgroup label="Admin">
                                            @foreach($admins as $a)
                                                <option value="{{ json_encode(['name' => $a->nama, 'nip' => $a->nip ?? '-', 'role' => 'Admin']) }}">{{ $a->nama }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>

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
                                    <button type="button" id="btn-bold" class="flex-1 py-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors" title="Bold">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" id="btn-italic" class="flex-1 py-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors" title="Italic">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" id="btn-underline" class="flex-1 py-2 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors" title="Underline">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ukuran Font</label>
                                    <input type="number" id="input-item-fontsize" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500" value="10" min="6" max="72">
                                </div>
                                <div class="flex-1">
                                     <button type="button" id="btn-delete-item" class="w-full h-[38px] mt-[19px] bg-red-50 text-red-600 rounded-xl hover:bg-red-100 font-bold text-xs flex items-center justify-center gap-2 px-3">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <form id="stamp-form" action="{{ route('admin.approval.stamping.process', $approval) }}" method="POST" class="space-y-4 mt-auto" onsubmit="saveStampsData()">
                            @csrf
                            <!-- QR Data (Main stored in dedicated columns) -->
                            <input type="hidden" name="x" id="input-x" value="{{ $approval->stamp_x }}">
                            <input type="hidden" name="y" id="input-y" value="{{ $approval->stamp_y }}">
                            <input type="hidden" name="width" id="input-width" value="{{ $approval->stamp_width ?: 120 }}">
                            <input type="hidden" name="height" id="input-height" value="{{ $approval->stamp_height ?: 120 }}">
                            <input type="hidden" name="page" id="input-page" value="{{ $approval->stamp_page ?: 1 }}">
                            <input type="hidden" name="signature_type" value="qr">
                            
                            <!-- Additional Stamps JSON -->
                            <input type="hidden" name="additional_stamps" id="input-additional-stamps" value="{{ json_encode($approval->additional_stamps ?? []) }}">
                            <input type="hidden" name="bulk_stamping" value="true">
                            <input type="hidden" name="full_stamps_data" id="input-full-stamps-data">


                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Comments Section -->
    <div id="discussion" class="mt-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-comments text-orange-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                        <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan pemohon/admin</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4">
                <!-- Comments List -->
                <div class="space-y-4 mb-6 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
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
                        <div class="text-center py-6">
                            <i class="fas fa-comment-slash text-gray-200 text-2xl mb-2"></i>
                            <p class="text-[11px] text-gray-400">Belum ada diskusi.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Comment Form -->
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
    <div class="mt-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane text-indigo-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 leading-none mb-1">Notifikasi & Pengiriman</h3>
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Kirim link dokumen ke email atau WhatsApp pemohon</p>
                    </div>
                </div>
            </div>
            <div class="p-8">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <button type="button" id="btn-show-email-preview" class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all group">
                        <i class="fa-solid fa-paper-plane text-indigo-200 group-hover:text-white transition-colors text-sm"></i> 
                        <span>Kirim Notifikasi Sekarang</span>
                    </button>
                    
                    @if($surat->jenis?->allow_download)
                        <div class="flex items-center px-6 py-4 bg-amber-50 rounded-2xl border border-amber-100 text-[10px] text-amber-700 font-bold max-w-md">
                            <i class="fas fa-info-circle mr-3 text-lg opacity-50"></i>
                            Pemohon dapat mengunduh surat ini langsung dari dashboard mereka jika status sudah disetujui.
                        </div>
                    @endif

                    <input type="hidden" id="input-recipient" value="{{ $surat->penerima_email ?: ($surat->pemohon->email ?? '') }}">
                    <input type="hidden" id="input-wa" value="{{ $surat->pemohon->wa ?: ($surat->pemohon->hp ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Modals & Scripts -->
    <div id="email-preview-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" data-modal-toggle="email-preview-modal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full z-10">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4 border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Kirim WA / Email</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500 btn-close-modal" data-modal-toggle="email-preview-modal"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="modal-loading-state" class="py-10 text-center"><i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-3"></i><p class="text-xs text-gray-400">Menyiapkan pratinjau...</p></div>
                    <div id="modal-editor-state" class="space-y-4 hidden text-sm">
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Penerima</label><div id="preview-recipient" class="font-semibold p-2 bg-gray-50 rounded"></div></div>
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Subjek</label><input type="text" id="preview-subject" class="w-full px-3 py-2 border rounded-lg"></div>
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Isi Pesan</label><textarea id="preview-body" rows="6" class="w-full px-3 py-2 border rounded-lg"></textarea></div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row-reverse gap-3 rounded-b-2xl">
                    <button type="button" id="btn-confirm-send" class="btn-pill btn-pill-info px-8"><i class="fa-solid fa-paper-plane mr-2"></i> Kirim Email</button>
                    <button type="button" id="btn-send-wa" class="btn-pill btn-pill-success px-8"><i class="fa-brands fa-whatsapp mr-2"></i> Kirim WA</button>
                    <button type="button" class="btn-pill btn-pill-secondary px-8 btn-close-modal" data-modal-toggle="email-preview-modal">Kembali</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const suratData = @json($surat->data ?? []);
            const formFields = @json($surat->jenis?->form_fields ?? []);
            const dosens = @json($dosens ?? []);
            const mahasiswas = @json($mahasiswas ?? []);
            const currentPemohonType = @json($surat->pemohon_type ?? '');
            const currentPemohonId = @json($surat->pemohon_type === 'mahasiswa' ? $surat->pemohon_mahasiswa_id : $surat->pemohon_dosen_id);
            const containerId = 'surat-show-container';

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            window.removeTableRow = function(btn) {
                const row = btn.closest('tr');
                const tbody = row.closest('tbody');
                if (tbody.querySelectorAll('tr').length > 1) {
                    row.remove();
                    document.querySelectorAll('[id$="_body"]').forEach(tb => {
                        const tableId = tb.id.replace('_body', '');
                        const reindexFunc = window['reindexTableRows_' + tableId.replace('table_', '')];
                        if (reindexFunc) reindexFunc();
                    });
                } else {
                    alert('Minimal harus ada 1 baris data.');
                }
            };

            function renderField(field) {
                const key = field.key;
                const label = escapeHtml(field.label);
                let value = suratData[key] || '';
                
                if (['no_surat', 'tanggal_surat', 'status'].includes(key)) return '';

                if (field.type === 'pemohon') {
                    const sources = Array.isArray(field.pemohon_sources) && field.pemohon_sources.length ? field.pemohon_sources : ['mahasiswa','dosen'];
                    const dosenOptions = (dosens || []).map(d => `<option value="dosen:${d.id}" ${currentPemohonType === 'dosen' && currentPemohonId == d.id ? 'selected' : ''}>${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                    const mhsOptions = (mahasiswas || []).map(m => `<option value="mahasiswa:${m.id}" ${currentPemohonType === 'mahasiswa' && currentPemohonId == m.id ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                    
                    const pemohonData = (typeof value === 'object' && value !== null) ? value : {};
                    const actualType = pemohonData.type || currentPemohonType;
                    const actualId = pemohonData.id || currentPemohonId;
                    const customNama = pemohonData.custom_nama || '';
                    const customNip = pemohonData.custom_nip || '';
                    const isCustom = actualType === 'custom';
                    
                    return `<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                            </div>
                            <span class="${actualId ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${actualId ? 'TERISI' : 'KOSONG'}</span>
                        </div>
                        <input type="hidden" class="pemohon-type" name="form_data[${escapeHtml(key)}][type]" value="${escapeHtml(actualType)}">
                        <input type="hidden" class="pemohon-id" name="form_data[${escapeHtml(key)}][id]" value="${escapeHtml(actualId)}">
                        <select class="pemohon-select w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                            <option value="">Pilih pemohon</option>
                            ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                            ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                            <optgroup label="Lainnya">
                                <option value="custom:0" ${isCustom ? 'selected' : ''}>Isi Sendiri</option>
                            </optgroup>
                        </select>
                        <div class="pemohon-custom-inputs ${isCustom ? '' : 'hidden'} space-y-3 mt-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="form_data[${escapeHtml(key)}][custom_nama]" value="${escapeHtml(customNama)}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="Masukkan nama lengkap" ${isCustom ? 'required' : ''}>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">NIP/NPM/Identitas</label>
                                <input type="text" name="form_data[${escapeHtml(key)}][custom_nip]" value="${escapeHtml(customNip)}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="Masukkan NIP/NPM" ${isCustom ? 'required' : ''}>
                            </div>
                        </div>
                    </div>`;
                }

                if (field.type === 'textarea') {
                    const hasValue = !!value;
                    return `<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                            </div>
                            <span class="${hasValue ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${hasValue ? 'TERISI' : 'KOSONG'}</span>
                        </div>
                        <textarea name="form_data[${escapeHtml(key)}]" rows="4" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">${escapeHtml(value)}</textarea>
                    </div>`;
                }

                if (field.type === 'date') {
                    const hasValue = !!value;
                    return `<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                            </div>
                            <span class="${hasValue ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${hasValue ? 'TERISI' : 'KOSONG'}</span>
                        </div>
                        <input type="date" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                    </div>`;
                }

                if (field.type === 'file') {
                    const fileUrl = value ? `/uploads/${value}` : '';
                    const ext = value ? value.split('.').pop().toLowerCase() : '';
                    const isPdf = ext === 'pdf';
                    const isImage = ['jpg','jpeg','png','gif','webp','bmp'].includes(ext);
                    const hasValue = !!value;

                    let previewHtml = '';
                    if (value && isPdf) {
                        previewHtml = `
                            <div class="mt-3 rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
                                <iframe src="${fileUrl}" class="w-full" style="height: 550px; border: none;"></iframe>
                            </div>`;
                    } else if (value && isImage) {
                        previewHtml = `
                            <div class="mt-3">
                                <img src="${fileUrl}" alt="${label}" class="max-w-full rounded-xl border border-gray-200 shadow-sm" style="max-height: 400px; object-fit: contain;">
                            </div>`;
                    }

                    return `
                        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center ${isPdf ? 'bg-red-100 text-red-600' : (isImage ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600')}">
                                        <i class="fas ${isPdf ? 'fa-file-pdf' : (isImage ? 'fa-file-image' : 'fa-file')} text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    ${hasValue ? `<a href="${fileUrl}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-[10px] font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                        <i class="fas fa-external-link-alt"></i> Buka full
                                    </a>` : ''}
                                    <span class="${hasValue ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${hasValue ? 'TERISI' : 'KOSONG'}</span>
                                </div>
                            </div>
                            ${previewHtml}
                            <div class="${hasValue ? 'mt-4 pt-3 border-t border-gray-100' : ''}">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1.5">${hasValue ? 'Ganti File' : 'Upload File'}</label>
                                <input type="file" name="form_files[${escapeHtml(key)}]" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                            </div>
                        </div>`;
                }

                if (field.type === 'select' || field.type === 'radio') {
                    const options = Array.isArray(field.options) ? field.options : [];
                    const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}" ${value == o.value ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
                    const hasValue = !!value;
                    return `<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                            </div>
                            <span class="${hasValue ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${hasValue ? 'TERISI' : 'KOSONG'}</span>
                        </div>
                        <select name="form_data[${escapeHtml(key)}]" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                            <option value="">Pilih</option>
                            ${optionsHtml}
                        </select>
                    </div>`;
                }

                if (field.type === 'table') {
                    const columns = Array.isArray(field.columns) ? field.columns : [];
                    if (!columns.length) return '';

                    const tableId = `table_${key}`;
                    const tableData = Array.isArray(value) ? value : [];
                    
                    const renderTableCell = (col, rowIdx, cellValue = '') => {
                        const colKey = col.key;
                        const colLabel = col.label;
                        const colType = col.type || 'text';
                        
                        if (colType === 'pemohon') {
                            const sources = Array.isArray(col.pemohon_sources) && col.pemohon_sources.length ? col.pemohon_sources : ['mahasiswa','dosen'];
                            const pemohonType = (cellValue && cellValue.type) || '';
                            const pemohonId = (cellValue && cellValue.id) || '';
                            const selectedValue = pemohonType && pemohonId ? `${pemohonType}:${pemohonId}` : '';
                            const dosenOptions = (dosens || []).map(d => `<option value="dosen:${d.id}" ${selectedValue === `dosen:${d.id}` ? 'selected' : ''}>${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                            const mhsOptions = (mahasiswas || []).map(m => `<option value="mahasiswa:${m.id}" ${selectedValue === `mahasiswa:${m.id}` ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                            
                            return `<td class="px-3 py-2">
                                <input type="hidden" class="pemohon-type" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}][type]" value="${escapeHtml(pemohonType)}">
                                <input type="hidden" class="pemohon-id" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}][id]" value="${escapeHtml(pemohonId)}">
                                <select class="pemohon-select w-full px-2 py-1 border border-gray-300 rounded text-xs">
                                    <option value="">Pilih</option>
                                    ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                                    ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                                </select>
                            </td>`;
                        }
                        
                        return `<td class="px-3 py-2">
                            <input type="text" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}]" value="${escapeHtml(cellValue)}" class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                        </td>`;
                    };
                    
                    const rowsHtml = tableData.length > 0 ? tableData.map((row, idx) => {
                        return `<tr class="table-row">
                            <td class="px-2 py-2 text-center text-xs text-gray-400 row-number">${idx + 1}</td>
                            ${columns.map(col => renderTableCell(col, idx, row[col.key] || '')).join('')}
                            <td class="px-2 py-2 text-center"><button type="button" onclick="removeTableRow(this)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                    }).join('') : `<tr class="table-row">
                        <td class="px-2 py-2 text-center text-xs text-gray-400 row-number">1</td>
                        ${columns.map(col => renderTableCell(col, 0, '')).join('')}
                        <td class="px-2 py-2 text-center"><button type="button" onclick="removeTableRow(this)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>
                    </tr>`;

                    window[`addTableRow_${key}`] = function() {
                        const tbody = document.getElementById(tableId + '_body');
                        const rowCount = tbody.querySelectorAll('tr').length;
                        const row = document.createElement('tr');
                        row.className = 'table-row';
                        let html = `<td class="px-2 py-2 text-center text-xs text-gray-400 row-number">${rowCount + 1}</td>`;
                        columns.forEach(col => {
                            const ct = col.type || 'text';
                            if (ct === 'pemohon') {
                                html += `<td class="px-3 py-2">
                                    <input type="hidden" class="pemohon-type" name="form_data[${key}][${rowCount}][${escapeHtml(col.key)}][type]" value="">
                                    <input type="hidden" class="pemohon-id" name="form_data[${key}][${rowCount}][${escapeHtml(col.key)}][id]" value="">
                                    <select class="pemohon-select w-full px-2 py-1 border border-gray-300 rounded text-xs"><option value="">Pilih</option>${(dosens||[]).map(d=>`<option value="dosen:${d.id}">${escapeHtml(d.nama)}</option>`).join('')}</select>
                                </td>`;
                            } else {
                                html += `<td class="px-3 py-2"><input type="text" name="form_data[${key}][${rowCount}][${escapeHtml(col.key)}]" class="w-full px-2 py-1 border border-gray-300 rounded text-xs"></td>`;
                            }
                        });
                        html += `<td class="px-2 py-2 text-center"><button type="button" onclick="removeTableRow(this)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>`;
                        row.innerHTML = html;
                        tbody.appendChild(row);
                        window[`reindexTableRows_${key}`]();
                    };

                    window[`reindexTableRows_${key}`] = function() {
                        const rows = document.getElementById(tableId + '_body').querySelectorAll('tr');
                        rows.forEach((row, idx) => {
                            row.querySelector('.row-number').textContent = idx + 1;
                            row.querySelectorAll('input, select').forEach(inp => {
                                const name = inp.getAttribute('name');
                                if (name) inp.setAttribute('name', name.replace(/\[\d+\]/, `[${idx}]`));
                            });
                        });
                    };

                    return `<div class="md:col-span-2 border border-blue-50 bg-blue-50/10 rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">${label}</label>
                            <button type="button" onclick="addTableRow_${key}()" class="btn-pill btn-pill-secondary px-3 py-1 text-[10px] uppercase font-bold tracking-wider">Tambah Baris</button>
                        </div>
                        <div class="overflow-x-auto"><table class="w-full" id="${tableId}"><thead><tr class="bg-slate-50 border-b border-slate-100"><th class="px-2 py-2 w-10"></th>${columns.map(c=>`<th class="px-3 py-2 text-left text-[10px] font-bold text-slate-500 uppercase">${escapeHtml(c.label)}</th>`).join('')}<th class="w-10"></th></tr></thead><tbody id="${tableId}_body">${rowsHtml}</tbody></table></div>
                    </div>`;
                }

                return `<div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">${label}</h4>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mt-0.5">${field.required ? 'WAJIB' : 'OPSIONAL'}</p>
                        </div>
                        <span class="${value ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'} text-[10px] font-bold px-2.5 py-1 rounded-full">${value ? 'TERISI' : 'KOSONG'}</span>
                    </div>
                    <input type="${field.type === 'number' ? 'number' : 'text'}" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-white text-sm">
                </div>`;
            }



            const initDynamicFields = () => {
                const container = document.getElementById('dynamic-fields-admin');
                if (!container || container.dataset.initialized === '1') return;
                container.dataset.initialized = '1';

                // Sort fields: file-type fields first, then others
                const sortedFields = [...(formFields || [])].sort((a, b) => {
                    const aIsFile = a.type === 'file' ? 0 : 1;
                    const bIsFile = b.type === 'file' ? 0 : 1;
                    return aIsFile - bIsFile;
                });

                container.innerHTML = sortedFields.map(renderField).join('');

                container.addEventListener('change', (e) => {
                    if (e.target.classList.contains('pemohon-select')) {
                        const select = e.target;
                        const [type, id] = select.value.split(':');
                        const parent = select.closest('div') || select.closest('td');
                        if (parent.querySelector('.pemohon-type')) parent.querySelector('.pemohon-type').value = type || '';
                        if (parent.querySelector('.pemohon-id')) parent.querySelector('.pemohon-id').value = id || '';
                        
                        const customInputs = parent.querySelector('.pemohon-custom-inputs');
                        if (customInputs) {
                            if (type === 'custom') {
                                customInputs.classList.remove('hidden');
                                customInputs.querySelectorAll('input').forEach(inp => inp.setAttribute('required', 'required'));
                            } else {
                                customInputs.classList.add('hidden');
                                customInputs.querySelectorAll('input').forEach(inp => {
                                    inp.removeAttribute('required');
                                    inp.value = '';
                                });
                            }
                        }
                    }
                });

                // Auto No Surat logic
                const input = document.getElementById('no_surat');
                if (input && (input.value || '').trim() === '' && input.dataset.userEdited !== '1') {
                    input.addEventListener('input', () => { input.dataset.userEdited = '1'; });
                    const jenisId = @json($surat->surat_jenis_id);
                    if (jenisId) {
                        fetch(`{{ route('admin.surat.next-no-surat') }}?surat_jenis_id=${encodeURIComponent(jenisId)}`, { headers: { 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then(data => { if (data && (input.value || '').trim() === '' && input.dataset.userEdited !== '1') input.value = String(data.next_no_surat); });
                    }
                }
            };
            if (window.Protekta && window.Protekta.registerInit) { window.Protekta.registerInit(initDynamicFields); } 
            else { document.addEventListener('DOMContentLoaded', initDynamicFields); setTimeout(initDynamicFields, 300); }
        })();
    </script>
    
    @if($surat->jenis?->is_uploaded && $approval)
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

                // Initial Data Loading (Load all approval QRs as templates)
                @php
                    $allApprovalsData = $approvals->map(function($app) {
                        return [
                            'approval_id' => $app->id,
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

                // Convert to stamp instances - each can exist on multiple pages
                allApprovals.forEach(app => {
                    // Create QR stamp instance ONLY IF already stamped
                    if (app.is_stamped) {
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

                // --- Rendering Logic ---

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
                        const renderTask = page.render(renderContext);
                        renderTask.promise.then(() => {
                            pageRendering = false;
                            if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
                            
                            renderStamps(); // Re-render stamps after page render
                        });
                    });
                    if (document.getElementById('current-page-num')) document.getElementById('current-page-num').textContent = num;
                    if (document.getElementById('input-page')) document.getElementById('input-page').value = num;
                }

                function renderStamps() {
                    stampsContainer.innerHTML = '';

                    // Get current active level
                    const select = document.getElementById('select-approval-level');
                    const currentLevelId = select ? parseInt(select.value) : {{ $approval->id ?? 0 }};

                    // Toggle Add QR Button visibility - check if QR exists on CURRENT PAGE only
                    const btnAddQr = document.getElementById('btn-add-qr');
                    if (btnAddQr) {
                        // Allow multiple QR per approval on different pages
                        // Only hide if QR already exists on this specific page
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
                    // Stored coordinates are at scale 1.3, current canvas is at current scale
                    const storageScale = 1.3;
                    const scaleRatio = scale / storageScale;

                    // Note: In surat/show we display stamps even if not in approval mode, but editing is restricted.
                    // If dragEnabled is false, everything is static anyway.

                    stamps.forEach(stamp => {
                        if (stamp.page !== pageNum) return;

                        const isCurrentLevel = (stamp.approval_id == currentLevelId);
                        const el = document.createElement('div');
                        el.id = stamp.id;

                        // Class calculation
                        let baseClass = `absolute z-40 select-none`;
                        if (isCurrentLevel) {
                            baseClass += ` cursor-move group hover:ring-1 hover:ring-blue-300 pointer-events-auto`; // Added pointer-events-auto
                            if (activeItem && activeItem.id === stamp.id) baseClass += ' ring-2 ring-blue-500 z-50';
                        } else {
                            baseClass += ` opacity-40 grayscale pointer-events-none`;
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
                            
                            // Skeleton/Ghost style if not yet stamped or not active level
                            const isGhost = !stamp.is_stamped;
                            
                            if (dragEnabled && isCurrentLevel) {
                                el.style.outline = '2px dashed #3b82f6';
                                el.style.outlineOffset = '-2px';
                            }
                            
                            el.className += ` ${isGhost ? 'bg-gray-100/50 border border-gray-300/50' : 'bg-blue-100/60 border border-blue-300/50'} backdrop-blur-[1px] flex items-center justify-center`;
                            
                            // If ghost and not current level, make it very faint
                            if (isGhost && !isCurrentLevel) {
                                el.classList.add('opacity-40');
                            } else if (isGhost) {
                                el.classList.add('opacity-70');
                            }
                            
                            el.innerHTML = `
                                 <div class="absolute inset-0 flex items-center justify-center opacity-60 pointer-events-none">
                                    <i class="fas fa-qrcode text-5xl ${isGhost ? 'text-gray-500' : 'text-blue-600'}"></i>
                                </div>
                                <div class="text-[8px] font-bold text-white ${ isCurrentLevel ? 'bg-blue-600' : 'bg-gray-500' } px-2 py-0.5 shadow-sm absolute top-0 left-0 w-full truncate leading-none">${(stamp.urutan && stamp.urutan !== '-') ? `${ isCurrentLevel ? '' : '🔒 ' }TTD ${stamp.urutan} - ${stamp.role_nama}` : (stamp.role_nama || stamp.text || 'QR Code')}</div>
                                <div class="resize-handle absolute -bottom-2 -right-2 w-5 h-5 bg-blue-600 rounded cursor-nwse-resize shadow ${dragEnabled && isCurrentLevel ? '' : 'hidden'}"></div>
                            `;
                        } else {
                            el.style.padding = '0';
                            el.style.boxSizing = 'border-box';
                            if (dragEnabled && isCurrentLevel) {
                                el.style.outline = '1px dashed #3b82f6';
                                el.style.outlineOffset = '2px';
                                el.style.backgroundColor = 'rgba(59, 130, 246, 0.05)';
                            }
                            
                            const fontSize = stamp.fontSize || 10;
                            const fontFamily = stamp.font || 'Arial';
                            el.style.fontFamily = fontFamilyMap[fontFamily] || fontFamily;
                            el.style.fontSize = (fontSize * scaleRatio) + 'px';
                            el.style.color = '#333';
                            el.style.whiteSpace = 'nowrap';
                            el.style.lineHeight = '1';
                            
                            // Style Flags
                            el.style.fontWeight = stamp.isBold ? 'bold' : 'normal';
                            el.style.fontStyle = stamp.isItalic ? 'italic' : 'normal';
                            el.style.textDecoration = stamp.isUnderline ? 'underline' : 'none';
                            
                            let displayText = stamp.text || stamp.key || 'Text';
                            if (stamp.type === 'tag') {
                                const appObj = allApprovals.find(a => a.approval_id == stamp.approval_id);
                                const suffix = appObj ? ` (TTD ${appObj.urutan})` : '';
                                displayText = `[${stamp.key}]${suffix}`;
                                el.style.color = '#2563eb';
                            }
                            
                            el.textContent = displayText;
                        }

                        if (dragEnabled && isCurrentLevel) {
                            attachDragEvents(el, stamp);
                            el.onclick = (e) => { e.stopPropagation(); selectItem(stamp.id); };
                        }
                        
                        stampsContainer.appendChild(el);
                    });
                }
                
                function selectItem(stampId) {
                    const stamp = stamps.find(s => s.id === stampId);
                    if (!stamp) return;

                    activeItem = stamp;

                    // Update Visuals WITHOUT NUKING DOM
                    Array.from(stampsContainer.children).forEach(child => {
                        if (child.id === stampId) {
                            child.classList.add('ring-2', 'ring-blue-500', 'z-50');
                        } else {
                            child.classList.remove('ring-2', 'ring-blue-500', 'z-50');
                        }
                    });

                    // Update Panels
                    if (itemProperties) {
                        itemProperties.classList.remove('hidden');

                        // Update Page Selector
                        const inputItemPage = document.getElementById('input-item-page');
                        if (inputItemPage && pdfDoc) {
                            inputItemPage.innerHTML = '';
                            for (let i = 1; i <= pdfDoc.numPages; i++) {
                                const option = document.createElement('option');
                                option.value = i;
                                option.textContent = `Halaman ${i}`;
                                if (stamp.page === i) option.selected = true;
                                inputItemPage.appendChild(option);
                            }
                        }

                        if (stamp.type === 'text') {
                            propTextContent.classList.remove('hidden');
                            inputItemText.value = stamp.text;
                        } else {
                            propTextContent.classList.add('hidden');
                        }
                        
                        if (stamp.type !== 'qr') {
                            inputItemFont.value = stamp.font || 'Arial';
                            inputItemFontSize.value = stamp.fontSize || 10;
                            inputItemFont.parentElement.classList.remove('hidden');
                            
                            // Show Font Size Wrapper & Row
                            inputItemFontSize.parentElement.classList.remove('hidden');
                            inputItemFontSize.parentElement.parentElement.classList.remove('hidden');
                            
                            // Update Style Buttons logic
                            if (btnBold && btnBold.parentElement) btnBold.parentElement.classList.remove('hidden');
                            updateStyleButton(btnBold, stamp.isBold);
                            updateStyleButton(btnItalic, stamp.isItalic);
                            updateStyleButton(btnUnderline, stamp.isUnderline);
                        } else {
                            inputItemFont.parentElement.classList.add('hidden');
                            
                            // Hide Font Size Wrapper ONLY, Keep Row for Delete Button
                            inputItemFontSize.parentElement.classList.add('hidden');
                            inputItemFontSize.parentElement.parentElement.classList.remove('hidden');
                            
                            if (btnBold && btnBold.parentElement) btnBold.parentElement.classList.add('hidden');
                        }
                        
                        
                        if (stamp.type === 'qr') {
                            // Allow delete for QR
                            btnDeleteItem.disabled = false;
                            btnDeleteItem.classList.remove('opacity-50', 'cursor-not-allowed');
                        } else {
                            btnDeleteItem.disabled = false;
                            btnDeleteItem.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                }

                function updateStyleButton(btn, isActive) {
                    if (!btn) return;
                    if (isActive) {
                        btn.classList.add('bg-blue-50', 'text-blue-600', 'border-blue-200');
                        btn.classList.remove('text-gray-500', 'border-gray-200');
                    } else {
                        btn.classList.remove('bg-blue-50', 'text-blue-600', 'border-blue-200');
                        btn.classList.add('text-gray-500', 'border-gray-200');
                    }
                }
                function attachDragEvents(el, stamp) {
                    el.onmousedown = function(e) {
                         // Check if clicking resize handle
                        if (e.target.classList.contains('resize-handle')) {
                            handleResize(e, stamp);
                            return;
                        }

                        e.preventDefault();
                        e.stopPropagation();
                        selectItem(stamp.id); // Select on drag start

                        // Get current scale ratio
                        const storageScale = 1.3;
                        const scaleRatio = scale / storageScale;

                        let startX = e.clientX;
                        let startY = e.clientY;
                        // Store the current position in display coordinates
                        let startDisplayLeft = parseFloat(el.style.left) || 0;
                        let startDisplayTop = parseFloat(el.style.top) || 0;

                        function doDrag(e) {
                            // Calculate mouse movement in screen pixels
                            let shiftX = e.clientX - startX;
                            let shiftY = e.clientY - startY;

                            // Calculate new position in display coordinates
                            // The shift is in screen pixels, which matches display coordinates
                            let newDisplayX = startDisplayLeft + shiftX;
                            let newDisplayY = startDisplayTop + shiftY;

                            // Bounds checking
                            const wrapperRect = pdfWrapper.getBoundingClientRect();
                            if (newDisplayX < 0) newDisplayX = 0;
                            if (newDisplayY < 0) newDisplayY = 0;
                            if (newDisplayX + el.offsetWidth > wrapperRect.width) newDisplayX = wrapperRect.width - el.offsetWidth;
                            if (newDisplayY + el.offsetHeight > wrapperRect.height) newDisplayY = wrapperRect.height - el.offsetHeight;

                            // Convert to storage coordinates (scale 1.3)
                            let newStorageX = Math.round(newDisplayX / scaleRatio);
                            let newStorageY = Math.round(newDisplayY / scaleRatio);

                            // Validate: ensure finite and positive values
                            if (!isFinite(newStorageX) || newStorageX < 0) newStorageX = 0;
                            if (!isFinite(newStorageY) || newStorageY < 0) newStorageY = 0;

                            stamp.x = newStorageX;
                            stamp.y = newStorageY;

                            // Once moved, it's no longer a ghost
                            stamp.is_stamped = true;

                            el.style.left = newDisplayX + 'px';
                            el.style.top = newDisplayY + 'px';

                            saveStampsData();
                        }

                        function stopDrag() {
                            document.removeEventListener('mousemove', doDrag);
                            document.removeEventListener('mouseup', stopDrag);
                        }

                        document.addEventListener('mousemove', doDrag);
                        document.addEventListener('mouseup', stopDrag);
                    };
                }
                
                function handleResize(e, stamp) {
                     e.preventDefault(); e.stopPropagation();
                     
                     // Get current scale ratio
                     const storageScale = 1.3;
                     const scaleRatio = scale / storageScale;
                     
                     let startX = e.clientX;
                     // Convert from display size back to storage size, ensure positive
                     let startWidth = Math.max(40, stamp.width) / scaleRatio;

                     function doResize(e) {
                         let newDisplayWidth = startWidth * scaleRatio + (e.clientX - startX);
                         if (newDisplayWidth < 40) newDisplayWidth = 40;
                         if (newDisplayWidth > 300) newDisplayWidth = 300; // Max limit

                         // Convert back to storage coordinates (scale 1.3) with validation
                         let newStorageWidth = Math.round(newDisplayWidth / scaleRatio);
                         
                         // Ensure positive and finite value
                         if (!isFinite(newStorageWidth) || newStorageWidth < 40) {
                             newStorageWidth = 40;
                         }
                         
                         stamp.width = newStorageWidth;
                         stamp.height = newStorageWidth; // Keep square

                         renderStamps(); // Re-render to update DOM styles
                         saveStampsData();
                     }

                     function stopResize() {
                         document.removeEventListener('mousemove', doResize);
                         document.removeEventListener('mouseup', stopResize);
                     }

                     document.addEventListener('mousemove', doResize);
                     document.addEventListener('mouseup', stopResize);
                }

                function saveStampsData() {
                    const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");

                    // Find the MAIN QR for this approval (the one with urutan set, or the first one on page 1)
                    // This is used for the legacy single-QR fields (x, y, width, height, page)
                    let mainQr = stamps.find(s => s.type === 'qr' && s.approval_id == currentLevelId && s.urutan !== '-' && s.is_stamped);
                    if (!mainQr) {
                        // Fallback to first QR for this approval
                        mainQr = stamps.find(s => s.type === 'qr' && s.approval_id == currentLevelId);
                    }
                    
                    if (mainQr) {
                        inputX.value = mainQr.x;
                        inputY.value = mainQr.y;
                        if (document.getElementById('input-width')) document.getElementById('input-width').value = mainQr.width;
                        if (document.getElementById('input-height')) document.getElementById('input-height').value = mainQr.height;
                        if (document.getElementById('input-page')) document.getElementById('input-page').value = mainQr.page || 1;
                    } else {
                        // Clear inputs if explicit main QR is gone (deleted)
                        inputX.value = '';
                        inputY.value = '';
                        if (document.getElementById('input-width')) document.getElementById('input-width').value = '';
                        if (document.getElementById('input-height')) document.getElementById('input-height').value = '';
                        if (document.getElementById('input-page')) document.getElementById('input-page').value = 1;
                    }

                    // extras are everything except the main QR for this level
                    // This includes additional stamps (text, tags, extra QRs on other pages)
                    const extras = stamps.filter(s => s.approval_id == currentLevelId && s.id !== (mainQr ? mainQr.id : null)).map(s => {
                        // Validate and sanitize all values before saving
                        const validateStamp = (val, defaultVal) => {
                            if (val === null || val === undefined || !isFinite(val)) return defaultVal;
                            return val;
                        };
                        
                        return {
                            type: s.type,
                            text: s.text,
                            role_nama: s.role_nama,
                            nip: s.nip,
                            custom_role: s.custom_role,
                            key: s.key,
                            x: validateStamp(s.x, 0),
                            y: validateStamp(s.y, 0),
                            width: Math.max(0, validateStamp(s.width, 0)),
                            height: Math.max(0, validateStamp(s.height, 0)),
                            font: s.font || 'Arial',
                            fontSize: validateStamp(s.fontSize, 10),
                            page: validateStamp(s.page, 1),
                            isBold: !!s.isBold,
                            isItalic: !!s.isItalic,
                            isUnderline: !!s.isUnderline
                        };
                    });

                    if (inputAdditionalStamps) inputAdditionalStamps.value = JSON.stringify(extras);

                    // Populate full data for bulk handling - ALL stamps from ALL approvals
                    const inputFull = document.getElementById('input-full-stamps-data');
                    if (inputFull) {
                        inputFull.value = JSON.stringify(stamps);
                    }
                }

                window.addSelectedQrStamp = function() {
                    const selector = document.getElementById('signer-selector');
                    const selectedApprovalId = parseInt(selector.value);
                    if (!selectedApprovalId) return;

                    // Check if QR already exists on THIS PAGE for this signer
                    if (stamps.find(s => s.type === 'qr' && s.approval_id === selectedApprovalId && s.page === pageNum)) {
                        alert('QR Tanda Tangan untuk pejabat ini sudah ada di halaman ' + pageNum + '.');
                        return;
                    }

                    // Find original data
                    const appData = allApprovals.find(a => a.approval_id === selectedApprovalId);
                    if (!appData) return;

                    // Create unique ID for this instance (approval_id + page)
                    const newStamp = {
                        id: 'qr_' + selectedApprovalId + '_p' + pageNum + '_' + Date.now(),
                        approval_id: selectedApprovalId,
                        type: 'qr',
                        urutan: appData.urutan,
                        role_nama: appData.role_nama,
                        x: appData.x || 50,
                        y: appData.y || 50,
                        width: appData.width || 120,
                        height: appData.height || 120,
                        page: pageNum,
                        is_stamped: false
                    };

                    stamps.push(newStamp);
                    renderStamps();
                    selectItem(newStamp.id);
                    saveStampsData();
                };

                // --- Toolbar Functions ---
                window.addTextStamp = function(initialText) {
                    const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
                    const newStamp = {
                        id: 'new_' + Date.now(),
                        approval_id: currentLevelId,
                        type: 'text',
                        text: initialText || 'Teks Baru',
                        x: 50,
                        y: 150, 
                        page: pageNum,
                        font: 'Arial',
                        fontSize: 10,
                        isBold: false, isItalic: false, isUnderline: false
                    };
                    stamps.push(newStamp);
                    renderStamps();
                    selectItem(newStamp.id);
                    saveStampsData();
                };

                window.addSuratQrStamp = function() {
                    const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
                    const newStampId = 'new_qr_surat_' + Date.now();
                    const verifyUrl = "{{ \Illuminate\Support\Facades\URL::signedRoute('verify.surat', ['suratId' => $surat->id]) }}";
                    
                    const newStamp = {
                        id: newStampId,
                        approval_id: currentLevelId,
                        type: 'qr', 
                        text: verifyUrl, 
                        nip: '-',
                        custom_role: 'surat_verification', // Special role for backend
                        role_nama: 'QR Validasi Surat',
                        urutan: '-', 
                        x: 50,
                        y: 190, 
                        width: 100,
                        height: 100,
                        page: pageNum
                    };
                    
                    stamps.push(newStamp);
                    renderStamps();
                    selectItem(newStampId);
                    saveStampsData();
                };

                window.addQrStampByText = function(initialText, nip, role) {
                    const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
                    // Use a unique ID for this new stamp
                    const newStampId = 'new_qr_' + Date.now();
                    
                    const newStamp = {
                        id: newStampId,
                        approval_id: currentLevelId,
                        type: 'qr', 
                        text: initialText, 
                        nip: nip || '-',
                        custom_role: role || 'Penandatangan',
                        role_nama: initialText, // Use text as label on canvas
                        urutan: '-', // No urutan
                        x: 50,
                        y: 190, 
                        width: 100,
                        height: 100,
                        page: pageNum
                    };
                    
                    stamps.push(newStamp);
                    renderStamps();
                    selectItem(newStampId);
                    saveStampsData();
                };

                window.addTagStamp = function(key) {
                    const currentLevelId = parseInt(document.getElementById('select-approval-level')?.value || "{{ $approval->id }}");
                    const newStamp = {
                        id: 'new_' + Date.now(),
                        approval_id: currentLevelId,
                        type: 'tag',
                        key: key,
                        x: 50,
                        y: 180,
                        page: pageNum,
                        font: 'Arial',
                        fontSize: 10,
                        isBold: false, isItalic: false, isUnderline: false
                    };
                    stamps.push(newStamp);
                    renderStamps();
                    selectItem(newStamp.id);
                    saveStampsData();
                };
                
                // Style Toggles Listeners
                const toggleStyle = (prop, btn) => {
                    if (activeItem && activeItem.type !== 'qr') {
                        activeItem[prop] = !activeItem[prop];
                        updateStyleButton(btn, activeItem[prop]);
                        
                        const el = document.getElementById(activeItem.id);
                        if (el) {
                            if (prop === 'isBold') el.style.fontWeight = activeItem[prop] ? 'bold' : 'normal';
                            if (prop === 'isItalic') el.style.fontStyle = activeItem[prop] ? 'italic' : 'normal';
                            if (prop === 'isUnderline') el.style.textDecoration = activeItem[prop] ? 'underline' : 'none';
                        }
                        
                        saveStampsData();
                    }
                };

                if (btnBold) btnBold.addEventListener('click', () => toggleStyle('isBold', btnBold));
                if (btnItalic) btnItalic.addEventListener('click', () => toggleStyle('isItalic', btnItalic));
                if (btnUnderline) btnUnderline.addEventListener('click', () => toggleStyle('isUnderline', btnUnderline));
                
                // Property Change Listeners
                if (inputItemText) inputItemText.addEventListener('input', () => {
                    if (activeItem && activeItem.type === 'text') {
                        activeItem.text = inputItemText.value;
                        renderStamps();
                        saveStampsData();
                    }
                });
                
                if (inputItemFont) inputItemFont.addEventListener('change', () => {
                    if (activeItem) {
                        activeItem.font = inputItemFont.value;
                        renderStamps();
                        saveStampsData();
                    }
                });
                
                if (inputItemFontSize) inputItemFontSize.addEventListener('input', () => {
                    if (activeItem) {
                        activeItem.fontSize = parseInt(inputItemFontSize.value) || 10;
                        renderStamps();
                        saveStampsData();
                    }
                });

                // Page Selector Listener
                const inputItemPage = document.getElementById('input-item-page');
                if (inputItemPage) {
                    inputItemPage.addEventListener('change', () => {
                        if (activeItem) {
                            activeItem.page = parseInt(inputItemPage.value);
                            renderStamps();
                            saveStampsData();
                        }
                    });
                }

                if (btnDeleteItem) btnDeleteItem.addEventListener('click', () => {
                    if (activeItem) {
                        stamps = stamps.filter(s => s.id !== activeItem.id);
                        activeItem = null;
                        itemProperties.classList.add('hidden');
                        renderStamps();
                        saveStampsData();
                    }
                });

                // --- Initialization ---
                
                // PDF Load - Always load ORIGINAL PDF so ghost overlays don't duplicate baked-in stamps
                const finalUrl = "{{ route('uploads.show', $surat->uploaded_pdf_path) }}" + "?t=" + new Date().getTime();
                fetch(finalUrl)
                    .then(res => { if(!res.ok) throw new Error('404'); return res.blob(); })
                    .then(blob => { const url = URL.createObjectURL(blob); return pdfjsLib.getDocument(url).promise; })
                    .then(pdfDoc_ => {
                        pdfDoc = pdfDoc_;
                        if (document.getElementById('total-pages')) document.getElementById('total-pages').textContent = pdfDoc.numPages;
                        renderPage(pageNum);
                    })
                    .catch(err => console.error(err));

                // Page Navigation
                function queueRenderPage(num) {
                    if (pageRendering) pageNumPending = num;
                    else renderPage(num);
                }
                if (document.getElementById('prev-page')) document.getElementById('prev-page').addEventListener('click', () => { if (pageNum <= 1) return; pageNum--; queueRenderPage(pageNum); });
                if (document.getElementById('next-page')) document.getElementById('next-page').addEventListener('click', () => { if (pageNum >= pdfDoc.numPages) return; pageNum++; queueRenderPage(pageNum); });
                if (document.getElementById('zoom-in')) document.getElementById('zoom-in').addEventListener('click', () => { scale += 0.2; renderPage(pageNum); document.getElementById('zoom-level').textContent = Math.round(scale*100)+'%'; });
                if (document.getElementById('zoom-out')) document.getElementById('zoom-out').addEventListener('click', () => { if(scale>0.6) scale -= 0.2; renderPage(pageNum); document.getElementById('zoom-level').textContent = Math.round(scale*100)+'%'; });
                
                // Fullscreen Toggle
                let isFullscreen = false;
                const stampingSection = document.getElementById('stamping-section');
                const canvasContainer = document.getElementById('canvas-container');
                const sidePanel = document.querySelector('.lg\\:w-80.bg-white.border-l');
                const fullscreenBtn = document.getElementById('btn-fullscreen');
                
                if (fullscreenBtn && canvasContainer) {
                    fullscreenBtn.addEventListener('click', () => {
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
                    });
                }

                // Save Data Helper

                // Toggle Drag Mode
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', () => {
                        dragEnabled = !dragEnabled;
                        renderStamps();
                        if (dragEnabled) {
                            toggleBtn.innerHTML = '<i class="fas fa-times mr-2"></i> Kembali';
                            toggleBtn.classList.replace('bg-blue-50/50', 'bg-emerald-50');
                            toggleBtn.classList.replace('text-blue-700', 'text-emerald-700');
                            toggleBtn.classList.replace('border-blue-200', 'border-emerald-200');
                            submitBtn.disabled = false;
                            addItemsToolbar.classList.remove('hidden');
                        } else {
                            toggleBtn.innerHTML = '<i class="fas fa-mouse-pointer mr-2"></i> Aktifkan Editor';
                            toggleBtn.classList.replace('bg-emerald-50', 'bg-blue-50/50');
                            toggleBtn.classList.replace('text-emerald-700', 'text-blue-700');
                            toggleBtn.classList.replace('border-emerald-200', 'border-blue-200');
                            submitBtn.disabled = true;
                            addItemsToolbar.classList.add('hidden');
                            itemProperties.classList.add('hidden');
                            activeItem = null;
                        }
                    });
                }

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

                        // Find QR ghost for this level on CURRENT PAGE first
                        let qrForLevel = stamps.find(s => s.type === 'qr' && s.approval_id === newLevelId && !s.is_stamped && s.page === pageNum);
                        
                        // If not on current page, find any QR ghost for this level
                        if (!qrForLevel) {
                            qrForLevel = stamps.find(s => s.type === 'qr' && s.approval_id === newLevelId && !s.is_stamped);
                        }
                        
                        if (qrForLevel) {
                            // Only switch page if QR is not on current page
                            if (qrForLevel.page !== pageNum) {
                                pageNum = qrForLevel.page;
                                queueRenderPage(pageNum);
                            }
                            // Select the QR item after render
                            setTimeout(() => {
                                selectItem(qrForLevel.id);
                            }, 100);
                        }

                        renderStamps();
                    });
                }

                // --- AJAX Bulk Save ---
                if (stampForm) {
                    stampForm.addEventListener('submit', (e) => {
                        // Ensure data is synced before submit
                        saveStampsData();
                        const btn = document.getElementById('submit-stamp');
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                    });
                }
            })();
        </script>
    @endif

    <script>
        (function() {
            // Communication functions
            document.addEventListener('click', async function(e) {
                if (e.target.closest('#btn-show-email-preview')) {
                    const toField = document.getElementById('input-recipient');
                    const to = toField ? toField.value.trim() : '';
                    
                    const noSurat = document.getElementsByName('no_surat')[0]?.value;
                    const tanggalSurat = document.getElementsByName('tanggal_surat')[0]?.value;
                    const status = document.getElementsByName('status')[0]?.value;
                    
                    // Gather form data overrides
                    const formData = {};
                    document.querySelectorAll('#dynamic-fields-admin [name^="form_data\\["]').forEach(el => {
                        const keyMatch = el.name.match(/form_data\[([^\]]+)\]/);
                        if (keyMatch) formData[keyMatch[1]] = el.value;
                    });

                    window.Protekta.modal.show('email-preview-modal');
                    const loader = document.getElementById('modal-loading-state');
                    const editor = document.getElementById('modal-editor-state');
                    loader.classList.remove('hidden'); editor.classList.add('hidden');

                    try {
                        const res = await fetch(`{{ route('admin.surat.preview-email', $surat) }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ 
                                recipient: to,
                                no_surat: noSurat,
                                tanggal_surat: tanggalSurat,
                                status: status,
                                form_data: formData
                            })
                        });
                        const data = await res.json();
                        if (data.error) {
                             alert(data.error);
                             window.Protekta.modal.hide('email-preview-modal');
                             return;
                        }
                        
                        document.getElementById('preview-recipient').textContent = to || 'Tidak ada alamat email';
                        document.getElementById('preview-recipient').className = to ? 'font-semibold p-2 bg-gray-50 rounded text-gray-700' : 'font-semibold p-2 bg-red-50 rounded text-red-500 italic';
                        
                        document.getElementById('preview-subject').value = data.subject || '';
                        document.getElementById('preview-body').value = data.body || '';
                        loader.classList.add('hidden'); editor.classList.remove('hidden');
                    } catch (err) { alert('Gagal memuat pratinjau.'); window.Protekta.modal.hide('email-preview-modal'); }
                }

                if (e.target.closest('#btn-confirm-send')) {
                    document.getElementById('hidden-recipient').value = document.getElementById('input-recipient').value;
                    document.getElementById('hidden-email-subject').value = document.getElementById('preview-subject').value;
                    document.getElementById('hidden-email-body').value = document.getElementById('preview-body').value;
                    e.target.closest('#btn-confirm-send').disabled = true;
                    document.getElementById('email-send-form').submit();
                }

                if (e.target.closest('#btn-send-wa')) {
                    const bod = document.getElementById('preview-body').value;
                    const wa = document.getElementById('input-wa').value;
                    if (wa) {
                        window.open(`https://wa.me/${window.Protekta.helpers.formatWA(wa)}?text=${encodeURIComponent(bod)}`, '_blank');
                    } else {
                        alert('Nomor WA/HP tidak ditemukan pada profil pemohon.');
                    }
                }

                // Toggle Show Reject
                if (e.target.closest('.btn-show-reject')) {
                    const appId = e.target.closest('.btn-show-reject').dataset.appId;
                    const form = document.getElementById('reject-form-' + appId);
                    const btn = e.target.closest('.btn-show-reject');
                    if (form) {
                        form.classList.remove('hidden');
                        btn.classList.add('hidden');
                    }
                }
                
                // Toggle Cancel Reject
                if (e.target.closest('.btn-cancel-reject')) {
                    const appId = e.target.closest('.btn-cancel-reject').dataset.appId;
                    const form = document.getElementById('reject-form-' + appId);
                    const btn = document.querySelector(`.btn-show-reject[data-app-id="${appId}"]`);
                    if (form) {
                        form.classList.add('hidden');
                        if (btn) btn.classList.remove('hidden');
                    }
                }
            });

            // Handle approval submission
            document.querySelectorAll('.approve-form-multi').forEach(form => {
                form.addEventListener('submit', function(e) {
                    this.querySelector('button[type="submit"]').disabled = true;
                    this.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                });
            });
        })();
    </script>
</div>
@endsection
