@extends('layouts.app')

@section('title', 'Kelola Jenis Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Kelola Jenis Surat</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar semua jenis layanan surat yang tersedia pada sistem.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.suratjenis.create') }}" class="btn-gradient inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Jenis Surat
                </a>
            </div>
        </div>

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4">
                    <div>
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Jenis Surat</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik untuk mencari (Nama atau Kode)..."
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">{{ $item->nama }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $item->kode }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->templates && $item->templates->where('aktif', true)->count() > 0)
                                    <span class="inline-flex text-xs px-3 py-1 font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex text-xs px-3 py-1 font-semibold rounded-full bg-gray-50 text-gray-600 ring-1 ring-gray-200">
                                        Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->workflowSteps->count() > 0)
                                    <span class="inline-flex text-[10px] px-2 py-0.5 font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-tighter">
                                        {{ $item->workflowSteps->count() }} Langkah
                                    </span>
                                @else
                                    <span class="inline-flex text-[10px] px-2 py-0.5 font-bold rounded-lg bg-gray-50 text-gray-500 border border-gray-200 uppercase tracking-tighter">
                                        Tanpa Alur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <a class="hover:scale-110 transition-transform" href="{{ route('admin.suratjenis.edit', $item) }}" title="Edit" style="color: #f59e0b !important;">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>
                                    <a class="hover:scale-110 transition-transform" href="{{ route('admin.surat-template.index', $item) }}" title="Template" style="color: #6366f1 !important;">
                                        <i class="fas fa-file-word fa-fw"></i>
                                    </a>
                                    <form action="{{ route('admin.suratjenis.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis surat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:scale-110 transition-transform" title="Hapus" style="color: #f43f5e !important; border: none; background: none; padding: 0;">
                                            <i class="fas fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada jenis surat yang ditemukan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
