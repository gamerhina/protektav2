@extends('layouts.app')

@section('title', 'Pembubuhan Tanda Tangan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-bold text-slate-900 leading-none">Pembubuhan Tanda Tangan Digital</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                @if(isset($isAdmin) && $isAdmin)
                    <span class="inline-flex items-center gap-2 text-indigo-600 font-medium">
                        <i class="fas fa-user-shield"></i>
                        Mode Super Admin - Lihat semua permohonan pembubuhan TTD
                    </span>
                @else
                    Bubuhkan tanda tangan Anda pada dokumen PDF yang diajukan
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(!isset($isAdmin) || !$isAdmin)
            <a href="{{ route('dosen.surat.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:from-emerald-600 hover:to-teal-700 hover:-translate-y-0.5 transition-all duration-200 !no-underline border border-transparent">
                <i class="fas fa-history"></i>
                Riwayat Pembubuhan / Kelola Surat
            </a>
            @endif
        </div>
    </div>

    {{-- Info Card --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-6 flex items-start gap-3 shadow-sm">
        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-info-circle text-indigo-600 text-lg"></i>
        </div>
        <div>
            <h3 class="font-bold text-indigo-900 text-sm mb-0.5">Apa itu fitur Pembubuhan TTD?</h3>
            <p class="text-indigo-800 text-xs leading-relaxed">
                Tempatkan tanda tangan digital (QR/Canvas) pada dokumen PDF dengan presisi. Pilih dokumen, posisikan tanda tangan, lalu simpan.
            </p>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Pending Stampings Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-900">
                Dokumen Menunggu Pembubuhan TTD
            </h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($pendingStampings as $approval)
                <div class="p-6 hover:bg-slate-50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex-1 flex gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $approval->surat->jenis->nama ?? 'Surat Upload' }}</h3>
                                <p class="text-sm text-slate-500">Pemohon: {{ $approval->surat->pemohonDosen->nama ?? $approval->surat->pemohonMahasiswa->nama ?? '-' }}</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase tracking-wider">
                                        {{ $approval->role_nama ?: ($approval->role->nama ?? 'Pejabat') }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded text-[10px] font-bold uppercase tracking-wider">
                                        Perlu Pembubuhan
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.approval.stamping.show', $approval) }}" 
                               class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all font-semibold shadow-sm hover:shadow-md">
                                <i class="fas fa-pencil-alt"></i> Bubuhkan TTD
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center text-slate-500">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-signature text-3xl text-slate-300"></i>
                    </div>
                    <p class="text-lg font-medium text-slate-400">Tidak ada dokumen yang menunggu pembubuhan tanda tangan</p>
                </div>
            @endforelse
        </div>
        
        @if($pendingStampings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $pendingStampings->appends(['completed_page' => request('completed_page')])->links() }}
            </div>
        @endif
    </div>

    @if(isset($isAdmin) && $isAdmin)
    {{-- Completed Stampings Section (History / Re-edit) --}}
    <div class="mt-12 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50/50 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-600"></i>
                Selesai Dibubuhkan
            </h2>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Riwayat & Edit Posisi</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($completedStampings ?? [] as $approval)
                <div class="p-6 hover:bg-slate-50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex-1 flex gap-4">
                            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0 border border-emerald-100">
                                <i class="fas fa-file-pdf text-emerald-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $approval->surat->jenis->nama ?? 'Surat Upload' }}</h3>
                                <p class="text-sm text-slate-500">Pemohon: {{ $approval->surat->pemohonDosen->nama ?? $approval->surat->pemohonMahasiswa->nama ?? '-' }}</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold uppercase tracking-wider">
                                        {{ $approval->role_nama ?: ($approval->role->nama ?? 'Pejabat') }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold uppercase tracking-wider">
                                        Sudah Dibubuhkan
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.approval.stamping.show', $approval) }}" 
                               class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-100 text-slate-700 rounded-xl hover:bg-indigo-50 hover:text-indigo-700 border border-transparent hover:border-indigo-100 transition-all font-semibold shadow-sm">
                                <i class="fas fa-arrows-alt"></i> Ubah Posisi
                            </a>
                            <a href="{{ route('admin.surat.preview', $approval->surat) }}" target="_blank"
                               class="p-2.5 text-slate-400 hover:text-blue-600 transition-colors" title="Lihat Hasil PDF">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-slate-400">
                    <p class="text-sm italic">Belum ada riwayat pembubuhan tanda tangan.</p>
                </div>
            @endforelse
        </div>
        
        @if(isset($completedStampings) && $completedStampings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $completedStampings->appends(['pending_page' => request('pending_page')])->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
