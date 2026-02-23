@extends('layouts.app')

@section('title', 'Detail Approval - ' . $approval->surat->jenis->nama)

@section('content')
<div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Detail Approval</h1>
            <p class="text-sm text-slate-500">Review dan proses persetujuan surat: <strong class="text-gray-900">{{ $approval->surat->jenis->nama }}</strong></p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.approval.dashboard') }}" class="btn-pill btn-pill-secondary !no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Surat Information --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-alt text-indigo-600"></i>
                    Informasi Surat
                </h2>

                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-32 text-sm font-semibold text-slate-600">Jenis Surat:</div>
                        <div class="flex-1 font-medium text-slate-900">{{ $approval->surat->jenis->nama }}</div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-32 text-sm font-semibold text-slate-600">No. Surat:</div>
                        <div class="flex-1 font-medium text-slate-900">{{ $approval->surat->no_surat ?? '-' }}</div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-32 text-sm font-semibold text-slate-600">Tanggal:</div>
                        <div class="flex-1 font-medium text-slate-900">
                            {{ $approval->surat->tanggal_surat?->format('d F Y') ?? '-' }}
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-32 text-sm font-semibold text-slate-600">Pemohon:</div>
                        <div class="flex-1 font-medium text-slate-900">
                            {{ $approval->surat->pemohonDosen->nama ?? $approval->surat->mahasiswa->nama ?? '-' }}
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-32 text-sm font-semibold text-slate-600">Perihal:</div>
                        <div class="flex-1 font-medium text-slate-900">
                            {{ $approval->surat->perihal ?? '-' }}
                        </div>
                    </div>

                    @if($approval->surat->isi)
                        <div class="flex items-start gap-4">
                            <div class="w-32 text-sm font-semibold text-slate-600">Isi Surat:</div>
                            <div class="flex-1 text-slate-700 bg-slate-50 p-4 rounded-xl">
                                {!! nl2br(e($approval->surat->isi)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Dynamic Data & Persyaratan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Isian Data Permohonan</h2>
                    <div class="h-px flex-1 bg-gray-50"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-8">
                    @php
                        $suratData = $approval->surat->data ?? [];
                        $formFields = $approval->surat->jenis?->form_fields ?? [];
                        $fileFields = collect($formFields)->filter(fn($f) => ($f['type'] ?? '') === 'file');
                        $otherFields = collect($formFields)->filter(fn($f) => ($f['type'] ?? '') !== 'file' && !in_array($f['key'] ?? '', ['no_surat', 'status', 'pemohon', 'auto_no_surat']));
                    @endphp

                    @forelse($otherFields as $field)
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">{{ $field['label'] }}</div>
                            <div class="text-sm font-semibold text-gray-700">
                                @php
                                    $val = $suratData[$field['key']] ?? '-';
                                    if (isset($field['options']) && ($field['type'] === 'select' || $field['type'] === 'radio')) {
                                        $opt = collect($field['options'])->firstWhere('value', $val);
                                        $val = $opt['label'] ?? $val;
                                    }
                                @endphp
                                {{ is_array($val) ? implode(', ', $val) : $val }}
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 text-sm text-gray-400 italic">Tidak ada data tambahan.</div>
                    @endforelse
                </div>

                {{-- Requirement Files --}}
                @php
                    $mainFile = $approval->surat->uploaded_pdf_path;
                    $extraFiles = [];
                    foreach($fileFields as $ff) {
                        if (isset($suratData[$ff['key']])) {
                            $extraFiles[] = [
                                'label' => $ff['label'],
                                'path' => $suratData[$ff['key']]
                            ];
                        }
                    }
                @endphp

                @if($mainFile || count($extraFiles) > 0)
                    <div class="space-y-6">
                        @if($mainFile)
                            <div class="border-t border-gray-50 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                                            <i class="fas fa-file-pdf text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900">Dokumen PDF Utama / Syarat</h3>
                                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Wajib Diperiksa</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ asset('uploads/' . $mainFile) }}" target="_blank" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-all">
                                            <i class="fas fa-expand-alt mr-1"></i> Buka Full
                                        </a>
                                        <a href="{{ asset('uploads/' . $mainFile) }}" download class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-100 transition-all">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                                <div class="bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 h-[600px] shadow-inner">
                                    <iframe src="{{ asset('uploads/' . $mainFile) }}#toolbar=0" class="w-full h-full"></iframe>
                                </div>
                            </div>
                        @endif

                        @foreach($extraFiles as $ef)
                            <div class="border-t border-gray-50 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                            <i class="fas fa-file-pdf text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-900">{{ $ef['label'] }}</h3>
                                            <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Lampiran Syarat</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ asset('uploads/' . $ef['path']) }}" target="_blank" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-all">
                                            <i class="fas fa-expand-alt mr-1"></i> Buka Full
                                        </a>
                                        <a href="{{ asset('uploads/' . $ef['path']) }}" download class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-100 transition-all">
                                            <i class="fas fa-download mr-1"></i> Unduh
                                        </a>
                                    </div>
                                </div>
                                <div class="bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 h-[500px] shadow-inner">
                                    <iframe src="{{ asset('uploads/' . $ef['path']) }}#toolbar=0" class="w-full h-full"></iframe>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-10 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-100 mt-4">
                        <i class="fas fa-file-invoice text-gray-200 text-4xl mb-3"></i>
                        <p class="text-sm text-gray-400 font-medium">Tidak ada lampiran berkas persyaratan.</p>
                    </div>
                @endif
            </div>

            {{-- Diskusi & Catatan --}}
            <div class="mt-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-comments text-orange-600 text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm leading-tight">Diskusi & Catatan</h3>
                                <p class="text-[9px] text-gray-400 uppercase tracking-widest font-bold">Komunikasi dengan pemohon / admin</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <!-- Comments List -->
                        <div class="space-y-4 mb-6 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($approval->surat->comments as $comment)
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
                        <form action="{{ route('admin.surat.comment.store', $approval->surat) }}" method="POST">
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

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Tanda Tangan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-clipboard-check text-indigo-500"></i> Status Tanda Tangan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($approval->surat->approvals->sortBy('urutan') as $app)
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
                                        @if($app->id === $approval->id)
                                            <span class="ml-1 px-1 py-0.5 bg-indigo-100 text-indigo-600 rounded text-[8px] uppercase tracking-tighter">Anda</span>
                                        @endif
                                    </p>
                                    <p class="text-[10px] leading-relaxed">
                                        @if($app->status === 'approved')
                                            <span class="text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Selesai</span>
                                            <span class="block text-gray-400 mt-0.5 font-medium">{{ $app->resolved_signer_name }}</span>
                                        @elseif($app->status === 'rejected')
                                            <span class="text-red-600 font-bold bg-red-50 px-1.5 py-0.5 rounded">Ditolak</span>
                                            <span class="block text-gray-400 mt-0.5 font-medium">{{ $app->resolved_signer_name }}</span>
                                        @else
                                            <span class="text-amber-600 font-bold bg-amber-50 px-1.5 py-0.5 rounded">Menunggu</span>
                                            <span class="block text-gray-400 mt-0.5 font-medium">{{ $app->resolved_signer_name }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Approval Actions (Moved to Sidebar) --}}
            @php
                $currentDosenId = Auth::guard('dosen')->id();
                
                // For approval page, only show roles specifically assigned to the current Dosen
                $myPendingApprovals = $approval->surat->approvals->filter(function($app) use ($currentDosenId) {
                    return $app->isPending() && $app->dosen_id && $app->dosen_id == $currentDosenId;
                });
            @endphp

            @if($myPendingApprovals->isNotEmpty())
                {{-- Personal Actions (Assigned Roles) --}}
                @foreach($myPendingApprovals as $app)
                    <div class="bg-indigo-50/30 rounded-2xl shadow-sm border border-indigo-100 p-6 mb-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 leading-tight">Persetujuan Tugas Anda</h2>
                                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">{{ $app->role_nama }}</p>
                            </div>
                        </div>

                        @if($app->isReady())
                            <form action="{{ route('admin.approval.approve', $app) }}" method="POST" class="approve-form-multi">
                                @csrf
                                <input type="hidden" name="signature_type" value="canvas"> 
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition-all shadow-md text-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    Setujui (Sebagai {{ $app->role_nama ?: 'Peran' }})
                                </button>
                            </form>

                            <div class="mt-3">
                                <button type="button" data-app-id="{{ $app->id }}" class="btn-show-reject w-full px-4 py-1.5 bg-white text-red-600 border border-red-100 rounded-lg font-semibold hover:bg-red-50 transition-all text-xs">
                                    Tolak Permohonan
                                </button>
                                <form id="reject-form-{{ $app->id }}" action="{{ route('admin.approval.reject', $app) }}" method="POST" class="hidden mt-3">
                                    @csrf
                                    <textarea name="reason" rows="2" required class="w-full px-3 py-2 border border-red-200 rounded-lg text-xs mb-2" placeholder="Alasan penolakan..."></textarea>
                                    <div class="flex gap-2">
                                        <button type="submit" class="flex-1 py-1.5 bg-red-600 text-white rounded font-bold text-[10px]">Konfirmasi Tolak</button>
                                        <button type="button" data-app-id="{{ $app->id }}" class="btn-cancel-reject flex-1 py-1.5 bg-slate-100 text-slate-600 rounded font-bold text-[10px]">Batal</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 text-center">
                                <p class="text-[10px] text-amber-700 font-bold leading-tight">Menunggu Giliran Tanda Tangan Tahap Sebelumnya</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                {{-- No pending actions --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center">
                    @if($approval->isApproved())
                        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">Sudah Selesai</h3>
                        <p class="text-xs text-slate-500 mb-6">Tindakan anda terhadap surat ini sebagai <strong>{{ $approval->role_nama }}</strong> telah berhasil diproses.</p>
                        
                        @if($approval->surat->jenis?->is_uploaded && !$isAdminSide)
                            <a href="{{ route('admin.approval.stamping.show', $approval) }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-lg hover:shadow-emerald-200">
                                <i class="fas fa-arrows-alt"></i>
                                Atur Posisi Tanda Tangan
                            </a>
                        @endif
                    @elseif($approval->isRejected())
                        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-times-circle text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">Permohonan Ditolak</h3>
                        <p class="text-xs text-slate-500">Persetujuan untuk surat ini telah dihentikan karena penolakan.</p>
                    @else
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-info-circle text-3xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">Hanya Pratinjau</h3>
                        <p class="text-xs text-slate-500">Anda tidak memiliki peran persetujuan yang tertunda untuk surat ini.</p>
                    @endif
                </div>
            @endif
        </div>
        </div>
    </div>
</div>

{{-- Updated script for multiple forms --}}
<script>
    document.addEventListener('click', function(e) {
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

    // Add confirmation on bulk approve if multiple forms exist
    document.querySelectorAll('.approve-form-multi').forEach(form => {
        form.addEventListener('submit', function(e) {
            this.querySelector('button[type="submit"]').disabled = true;
            this.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        });
    });
</script>
@endsection
