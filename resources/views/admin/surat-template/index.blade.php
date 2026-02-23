@extends('layouts.app')

@section('title', 'Template Surat: ' . $suratJenis->nama)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Template Surat: {{ $suratJenis->nama }}</h1>
            <p class="text-slate-500 mt-1 flex items-center gap-2">
                <i class="fas fa-file-invoice text-indigo-500"></i> Kelola template output dokumen untuk <strong>{{ $suratJenis->nama }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.suratjenis.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('admin.surat-template.create', $suratJenis) }}" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah Template
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-slate-100 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[11px] font-bold uppercase tracking-widest text-slate-400">Template Information</th>
                        <th class="px-8 py-5 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-center">Status</th>
                        <th class="px-8 py-5 text-[11px] font-bold uppercase tracking-widest text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($templates as $template)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 mb-1 group-hover:text-indigo-600 transition-colors text-lg">{{ $template->nama }}</span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1.5 font-medium uppercase tracking-wider">
                                        <i class="far fa-calendar-alt"></i> Created {{ $template->created_at->format('d M Y') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <form action="{{ route('admin.surat-template.toggle-aktif', [$suratJenis, $template]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="group relative inline-flex items-center gap-2 px-4 py-2 rounded-2xl transition-all {{ $template->aktif ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                                        <div class="w-2 h-2 rounded-full {{ $template->aktif ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></div>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ $template->aktif ? 'Active' : 'Draft' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.surat-template.edit', [$suratJenis, $template]) }}" class="p-2.5 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Edit Template">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.surat-template.destroy', [$suratJenis, $template]) }}" class="inline" onsubmit="return confirm('Hapus template ini? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2.5 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Delete Template">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-file-invoice text-slate-200 text-3xl"></i>
                                    </div>
                                    <p class="text-slate-400 font-medium">Belum ada template yang dibuat.</p>
                                    <a href="{{ route('admin.surat-template.create', $suratJenis) }}" class="mt-4 text-indigo-600 font-bold hover:text-indigo-700 text-sm">Create your first template &rarr;</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
