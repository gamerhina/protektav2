@extends('layouts.app')

@section('title', 'Riwayat Approval')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Approval Surat</h1>
        <p class="text-slate-500 mt-1">Timeline lengkap proses persetujuan surat</p>
    </div>

    {{-- Approval Timeline --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="space-y-8">
            @foreach($approvals as $index => $approval)
                <div class="relative">
                    {{-- Timeline Line --}}
                    @if(!$loop->last)
                        <div class="absolute left-6 top-14 bottom-0 w-0.5 {{ $approval->isApproved() ? 'bg-green-300' : ($approval->isRejected() ? 'bg-red-300' : 'bg-slate-200') }}"></div>
                    @endif

                    {{-- Timeline Item --}}
                    <div class="flex gap-6">
                        {{-- Icon --}}
                        <div class="relative z-10 flex-shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg
                                {{ $approval->isApproved() ? 'bg-green-500 text-white' : ($approval->isRejected() ? 'bg-red-500 text-white' : ($approval->isPending() ? 'bg-orange-400 text-white' : 'bg-slate-200 text-slate-600')) }}">
                                @if($approval->isApproved())
                                    <i class="fas fa-check"></i>
                                @elseif($approval->isRejected())
                                    <i class="fas fa-times"></i>
                                @else
                                    {{ $approval->urutan }}
                                @endif
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 pb-8">
                            <div class="bg-slate-50 rounded-xl p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900">{{ $approval->role_nama ?: ($approval->role->nama ?? 'Pejabat') }}</h3>
                                        <p class="text-sm text-slate-600 mt-1">
                                            <i class="fas fa-user"></i> {{ $approval->dosen->nama ?? 'Belum ditentukan' }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        @if($approval->isPending())
                                            <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-clock"></i> Menunggu
                                            </span>
                                        @elseif($approval->isApproved())
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-check-circle"></i> Disetujui
                                            </span>
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ $approval->approved_at->format('d M Y, H:i') }}
                                            </p>
                                        @elseif($approval->isRejected())
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                <i class="fas fa-times-circle"></i> Ditolak
                                            </span>
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ $approval->rejected_at->format('d M Y, H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if($approval->catatan)
                                    <div class="mt-3 pt-3 border-t border-slate-200">
                                        <p class="text-sm font-semibold text-slate-700 mb-1">Catatan:</p>
                                        <p class="text-slate-600 text-sm">{{ $approval->catatan }}</p>
                                    </div>
                                @endif

                                @if($approval->signature_path)
                                    <div class="mt-3 pt-3 border-t border-slate-200">
                                        <p class="text-sm font-semibold text-slate-700 mb-2">Tanda Tangan:</p>
                                        <img src="{{ Storage::url($approval->signature_path) }}" 
                                             alt="Signature" 
                                             class="h-16 border border-slate-200 rounded-lg bg-white p-2">
                                    </div>
                                @endif

                                {{-- Signature Type Badge --}}
                                @if($approval->isApproved() && $approval->signature_type)
                                    <div class="mt-2">
                                        <span class="text-xs px-2 py-1 bg-indigo-50 text-indigo-700 rounded">
                                            @if($approval->signature_type === 'canvas')
                                                <i class="fas fa-signature"></i> Digital Signature
                                            @elseif($approval->signature_type === 'qr')
                                                <i class="fas fa-qrcode"></i> QR Code
                                            @else
                                                <i class="fas fa-upload"></i> Upload
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-6">
        <a href="{{ url()->previous() }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 transition-all">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>
</div>
@endsection
