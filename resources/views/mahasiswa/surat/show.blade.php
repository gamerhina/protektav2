@extends('layouts.app')

@section('title', 'Detail Permohonan Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Top Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 leading-none">Detail Permohonan Surat</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Jenis: <strong class="text-gray-900">{{ $surat->jenis->nama ?? '-' }}</strong></p>
        </div>
        <div class="flex items-center gap-2">
            @if(($surat->jenis->allow_download ?? true) && $surat->status === 'selesai')
                <a href="{{ route('mahasiswa.surat.download', $surat) }}" download data-no-ajax class="btn-pill btn-pill-success !no-underline flex items-center gap-2 shadow-lg shadow-green-100">
                    <i class="fas fa-file-download"></i> Unduh PDF TTD
                </a>
            @endif
            
            <a href="{{ route('mahasiswa.surat.index') }}" class="btn-pill btn-pill-secondary !no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <!-- Status & Meta Row -->
                <div class="flex flex-col md:flex-row gap-6 mb-8 items-start">
                    <!-- Applicant Info -->
                    <div class="flex items-center gap-3 bg-gray-50/50 px-4 py-3 rounded-xl border border-gray-100 min-w-[260px]">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                            {{ substr($surat->pemohon->nama ?? $surat->mahasiswa->nama ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm leading-tight">
                                {{ $surat->pemohon->nama ?? $surat->mahasiswa->nama ?? 'Unknown' }}
                            </div>
                            <div class="text-[9px] uppercase font-bold tracking-widest text-gray-500 mt-0.5">
                                Mahasiswa / {{ $surat->mahasiswa->npm ?? ($surat->pemohon->npm ?? '-') }}
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information Grid -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</div>
                            <div>
                                <span class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full 
                                    @if($surat->status == 'diajukan') bg-yellow-100 text-yellow-800
                                    @elseif($surat->status == 'diproses') bg-blue-100 text-blue-800
                                    @elseif($surat->status == 'dikirim') bg-green-100 text-green-800
                                    @elseif($surat->status == 'selesai') bg-emerald-100 text-emerald-800
                                    @elseif($surat->status == 'ditolak') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($surat->status) }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nomor Surat</div>
                            <div class="text-sm font-semibold text-gray-700 font-mono">{{ $surat->no_surat ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tgl Diajukan</div>
                            <div class="text-xs font-semibold text-gray-700">{{ $surat->created_at->translatedFormat('d F Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Data Section -->
                <div class="pt-6 border-t border-gray-50">
                    <div class="flex items-center gap-3 mb-6">
                        <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Rincian Data Permohonan</h2>
                        <div class="h-px flex-1 bg-gray-50"></div>
                    </div>
                    
                    <div id="dynamic-fields-mahasiswa" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Dynamic fields will be injected here -->
                    </div>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div id="discussion" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                            <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan Admin</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <!-- Comments List -->
                    <div class="space-y-4 mb-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
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
                            <div class="text-center py-10">
                                <i class="fas fa-comment-slash text-gray-200 text-3xl mb-3"></i>
                                <p class="text-[11px] text-gray-400">Belum ada diskusi.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Comment Form -->
                    <form action="{{ route('mahasiswa.surat.comment.store', $surat) }}" method="POST">
                        @csrf
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <textarea name="message" rows="1" required
                                    class="w-full pl-3 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none resize-none"
                                    placeholder="Tulis pesan..."></textarea>
                            </div>
                            <button type="submit" class="w-10 h-10 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                                                <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Selesai</span>
                                                <span class="block text-gray-400 mt-0.5 font-medium">{{ $app->dosen ? $app->dosen->nama : 'Admin' }}</span>
                                            @elseif($app->status === 'rejected')
                                                <span class="text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded">Ditolak</span>
                                            @else
                                                <span class="text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded">Menunggu</span>
                                                <span class="block text-gray-400 mt-0.5 font-medium">{{ $app->dosen ? $app->dosen->nama : 'Admin' }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-info-circle text-gray-300 text-xl mb-2"></i>
                            <p class="text-xs text-gray-500 font-bold italic">Belum ada alur TTD</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Preview Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Preview & Cetak</h3>
                </div>
                <div class="p-6 space-y-4">
                    @php
                        $templates = $surat->jenis?->templates()->where('aktif', true)->get() ?? collect();
                    @endphp
                    
                    @if($surat->status === 'selesai' || ($surat->status === 'dikirim' && $surat->jenis?->is_uploaded))
                        @if($surat->jenis?->is_uploaded)
                             <a href="{{ route('mahasiswa.surat.download', $surat) }}" download data-no-ajax class="w-full py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all">
                                <i class="fas fa-file-pdf"></i> Unduh PDF TTD
                            </a>
                        @endif

                        @foreach($templates as $template)
                             <a href="{{ route('mahasiswa.surat.preview-html', ['surat' => $surat, 'template' => $template]) }}" target="_blank" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center gap-2 hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                                <i class="fas fa-eye"></i> Pratinjau {{ $template->nama }}
                            </a>
                        @endforeach
                    @else
                        <div class="p-4 bg-gray-50 text-gray-500 border border-gray-100 rounded-xl text-[10px] font-medium text-center">
                            <i class="fas fa-lock mr-2"></i> Tersedia setelah status "Selesai"
                        </div>
                    @endif
                </div>
            </div>
            

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const suratData = @json($surat->data ?? []);
        const formFields = @json($surat->jenis?->form_fields ?? []);

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatIndonesianDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr;
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                return new Intl.DateTimeFormat('id-ID', options).format(date);
            } catch (e) {
                return dateStr;
            }
        }

        function renderField(field) {
            const key = field.key;
            const label = escapeHtml(field.label);
            let value = suratData[key];
            const uploadedPdfPath = @json($surat->uploaded_pdf_path);

            if (value === undefined || value === null || value === '') {
                if (key === 'tujuan') value = @json($surat->tujuan);
                if (key === 'perihal') value = @json($surat->perihal);
                if (key === 'isi') value = @json($surat->isi);
                if (key === 'penerima_email') value = @json($surat->penerima_email);
                if (key === 'tanggal_surat') value = @json($surat->tanggal_surat?->translatedFormat('d F Y'));
            }

            if (['no_surat', 'status'].includes(key)) return '';
            if (field.type === 'pemohon') return '';

            let displayValue = value || '-';

            if (['select', 'radio', 'checkbox'].includes(field.type) && field.options) {
                const options = Array.isArray(field.options) ? field.options : [];
                if (Array.isArray(value)) {
                    displayValue = value.map(val => {
                        const opt = options.find(o => String(o.value) === String(val));
                        return opt ? opt.label : val;
                    }).join(', ');
                } else {
                    const opt = options.find(o => String(o.value) === String(value));
                    displayValue = opt ? opt.label : value;
                }
            }

            if (field.type === 'file' && (value || uploadedPdfPath)) {
                const filePath = uploadedPdfPath || (value.startsWith('documents/') ? value : `documents/surat/${value}`);
                const fileUrl = `/uploads/${filePath}`;
                const fileExt = filePath.split('.').pop()?.toUpperCase() || 'PDF';
                const fileName = filePath.split('/').pop() || 'Document.pdf';
                
                return `
                    <div class="md:col-span-2 mb-4">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">${label}</div>
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 group hover:border-blue-100 transition-all">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-50">
                                    <i class="fas fa-file-pdf text-lg text-white"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-xs font-bold text-gray-900 truncate">${fileName}</h3>
                                        <span class="bg-blue-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider">${fileExt}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="${fileUrl}" download class="text-[10px] font-bold text-blue-600 hover:text-blue-700">Unduh</a>
                                        <span class="text-gray-300">•</span>
                                        <a href="${fileUrl}" target="_blank" class="text-[10px] font-bold text-blue-600 hover:text-blue-700">Lihat Full</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                `;
            } else if (field.type === 'date' && value) {
                displayValue = formatIndonesianDate(value);
            } else if (field.type === 'checkbox' && (!field.options || field.options.length === 0)) {
                displayValue = value ? '<span class="text-emerald-600 font-bold">Ya</span>' : 'Tidak';
            }

            return `
                <div class="md:col-span-2">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">${label}</div>
                    <div class="text-sm p-3 bg-gray-50 rounded-xl border border-gray-200/50 text-gray-700 ${field.type === 'textarea' ? 'whitespace-pre-wrap' : 'font-semibold'}">
                        ${displayValue}
                    </div>
                </div>
            `;
        }

        function initMahasiswaSuratShow() {
            const container = document.getElementById('dynamic-fields-mahasiswa');
            if (!container) return;

            const fileFields = formFields.filter(f => f.type === 'file');
            const otherFields = formFields.filter(f => f.type !== 'file');
            
            const fileHtml = fileFields.map(renderField).filter(h => h !== '').join('');
            const otherHtml = otherFields.map(renderField).filter(h => h !== '').join('');
            
            container.innerHTML = fileHtml + otherHtml || '<p class="text-sm text-gray-500 italic">Tidak ada rincian data tambahan.</p>';
        }

        function handleAnchorScroll() {
            if (window.location.hash) {
                setTimeout(() => {
                    const element = document.querySelector(window.location.hash);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 500);
            }
        }

        if (document.readyState !== 'loading') { 
            initMahasiswaSuratShow(); 
            handleAnchorScroll();
        } else { 
            document.addEventListener('DOMContentLoaded', () => {
                initMahasiswaSuratShow();
                handleAnchorScroll();
            }); 
        }
        window.addEventListener('page-loaded', () => {
            initMahasiswaSuratShow();
            handleAnchorScroll();
        });
    })();
</script>
@endsection
