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

            {{-- Document Preview --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-file-pdf text-indigo-600"></i>
                         Pratinjau Dokumen
                    </h2>
                     <a href="{{ route('admin.surat.preview', $approval->surat) }}" target="_blank" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">
                        <i class="fas fa-external-link-alt mr-1"></i> Buka Full
                    </a>
                </div>
                <div class="bg-slate-100 rounded-xl overflow-hidden border border-slate-200 h-[800px]">
                    <iframe src="{{ route('admin.surat.preview', ['surat' => $approval->surat, 'mode' => 'stream']) }}" class="w-full h-full"></iframe>
                </div>
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
            {{-- Approval Status --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-slate-400"></i>
                    Status Persetujuan
                </h3>
                
                <div class="relative pl-4 border-l-2 border-slate-100 space-y-6">
                    @foreach($approval->surat->approvals->sortBy('urutan') as $app)
                        <div class="relative">
                            {{-- Dot Indicator --}}
                            <div class="absolute -left-[21px] top-1 w-4 h-4 rounded-full border-2 border-white shadow-sm
                                {{ $app->isApproved() ? 'bg-green-500' : ($app->isRejected() ? 'bg-red-500' : 'bg-slate-200') }}">
                            </div>

                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Tahap {{ $app->urutan }}
                                    @if($app->id === $approval->id)
                                        <span class="ml-2 px-1.5 py-0.5 bg-indigo-100 text-indigo-600 rounded text-[10px] normal-case">Anda</span>
                                    @endif
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                    {{ $app->isApproved() ? 'bg-green-50 text-green-600' : ($app->isRejected() ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $app->status === 'approved' ? 'Disetujui' : ($app->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                                </span>
                            </div>
                            
                            <h4 class="font-bold text-slate-800 text-sm">
                                {{ $app->role_nama ?: ($app->role->nama ?? ($app->dosen->nama ?? 'Pejabat')) }}
                            </h4>
                            
                            @if($app->isApproved())
                                <p class="text-xs text-slate-500 mt-1">
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                    {{ $app->approved_at->format('d M H:i') }}
                                </p>
                            @elseif($app->isRejected())
                                <p class="text-xs text-red-500 mt-1">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    {{ $app->rejected_at->format('d M H:i') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Approval Actions (Moved to Sidebar) --}}
            @if($approval->isPending())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-indigo-600"></i>
                        Aksi Persetujuan
                    </h2>

                    @if($approval->isReady())
                        <form id="approve-form" action="{{ route('admin.approval.approve', $approval) }}" method="POST" class="mb-6">
                            @csrf
                            
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4 flex items-start gap-2">
                                <i class="fas fa-info-circle text-blue-600 mt-0.5 text-xs"></i>
                                <div>
                                    <p class="text-xs text-blue-600">Menyetujui sebagai <strong>{{ $approval->role_nama ?: ($approval->role->nama ?? 'Pejabat') }}</strong>.</p>
                                </div>
                            </div>



                            <input type="hidden" name="signature_type" value="canvas"> 

                            {{-- Submit Button --}}
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition-all shadow-md text-sm flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                Setujui
                            </button>
                        </form>

                        {{-- Reject Form --}}
                        <div class="border-t border-slate-100 pt-4">
                            <button type="button" id="show-reject-form" 
                                    class="w-full px-4 py-2 bg-red-50 text-red-700 rounded-lg font-semibold hover:bg-red-100 transition-all text-sm flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i>
                                Tolak
                            </button>

                            <form id="reject-form" action="{{ route('admin.approval.reject', $approval) }}" method="POST" class="hidden mt-3">
                            @csrf
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                            <textarea name="reason" rows="3" required
                                      class="w-full px-3 py-2 border border-red-200 rounded-lg focus:ring-2 focus:ring-red-50 focus:border-red-500 transition-all mb-2 text-xs"
                                      placeholder="Alasan..."></textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 px-3 py-1.5 bg-red-600 text-white rounded font-semibold hover:bg-red-700 text-xs">
                                    Konfirmasi
                                </button>
                                <button type="button" id="cancel-reject" class="flex-1 px-3 py-1.5 bg-slate-100 text-slate-700 rounded font-semibold hover:bg-slate-200 text-xs">
                                    Kembali
                                </button>
                            </div>
                        </form>
                        </div>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center mt-4">
                            <i class="fas fa-hourglass-half text-amber-500 text-2xl mb-2 block animate-pulse"></i>
                            <h4 class="font-bold text-amber-700 text-sm mb-1">Menunggu Persetujuan Sebelumnya</h4>
                            <p class="text-xs text-amber-600">Anda belum dapat memberikan persetujuan karena masih ada tahap sebelumnya yang belum disetujui (Tahap Urutan ke-{{ $approval->urutan - 1 }} dsb).</p>
                        </div>
                    @endif
                </div>
            @else
                {{-- Already Processed --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="text-center py-4">
                        @if($approval->isApproved())
                            <i class="fas fa-check-circle text-2xl text-green-500 mb-2"></i>
                            <h3 class="text-sm font-bold text-slate-900 mb-1">Sudah Disetujui</h3>
                            <p class="text-[10px] text-slate-600">{{ $approval->approved_at->format('d M Y H:i') }}</p>

                            @if($approval->surat->jenis?->is_uploaded && !$isAdmin)
                                <a href="{{ route('admin.approval.stamping.show', $approval) }}" class="inline-flex items-center gap-2 px-4 py-2 mt-4 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-all shadow-md">
                                    <i class="fas fa-arrows-alt"></i>
                                    Ubah Posisi Tanda Tangan
                                </a>
                            @endif
                        @else
                            <i class="fas fa-times-circle text-2xl text-red-500 mb-2"></i>
                            <h3 class="text-sm font-bold text-slate-900 mb-1">Sudah Ditolak</h3>
                            <p class="text-[10px] text-slate-600">{{ $approval->rejected_at->format('d M Y H:i') }}</p>
                        @endif

                        @if($approval->catatan)
                            <div class="mt-3 p-3 bg-slate-50 rounded-lg text-left">
                                <p class="text-xs font-semibold text-slate-700 mb-0.5">Catatan:</p>
                                <p class="text-xs text-slate-600 italic">"{{ $approval->catatan }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Simple Script for Reject Toggle Only --}}
<script>
    document.getElementById('show-reject-form')?.addEventListener('click', () => {
        document.getElementById('reject-form').classList.remove('hidden');
        document.getElementById('show-reject-form').classList.add('hidden');
    });

    document.getElementById('cancel-reject')?.addEventListener('click', () => {
        document.getElementById('reject-form').classList.add('hidden');
        document.getElementById('show-reject-form').classList.remove('hidden');
    });
</script>
@endsection
