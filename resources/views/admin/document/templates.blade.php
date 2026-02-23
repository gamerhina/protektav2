@extends('layouts.app')

@section('title', 'Template Dokumen')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Template Dokumen</h1>
            <a href="{{ route('admin.document.create') }}" class="btn-gradient inline-flex items-center gap-2 justify-center sm:justify-start">
                <i class="fas fa-plus"></i> Tambah Template
            </a>
        </div>
        


        @php
            $defaultSort = 'updated_at';
            $defaultDirection = 'desc';
        @endphp

        <form method="GET" class="mb-6">
            <div class="bg-white/70 backdrop-blur border border-gray-100 rounded-2xl shadow-inner p-4 md:p-5">
                <div class="grid gap-4 md:grid-cols-[1fr_auto]">
                    <div>
                        <label for="search" class="text-sm font-medium text-gray-600">Cari Template</label>
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                                </svg>
                            </span>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama, kode, jenis seminar atau keterangan"
                                   class="w-full rounded-xl border border-gray-200 bg-white pl-9 pr-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        </div>
                    </div>
                    <div class="flex items-end gap-3">
                        <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition">
                            Cari
                        </button>
                        <a href="{{ route('admin.document.templates') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                            Atur Ulang
                        </a>
                    </div>
                </div>
                <input type="hidden" name="sort" value="{{ request('sort', $defaultSort) }}">
                <input type="hidden" name="direction" value="{{ request('direction', $defaultDirection) }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 15) }}">
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <x-sortable-th column="nama" label="Nama" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="kode" label="Kode" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <x-sortable-th column="jenis" label="Jenis Seminar" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tags</th>
                        <x-sortable-th column="status" label="Status" :default-sort="$defaultSort" :default-direction="$defaultDirection" />
                        <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($templates as $template)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $template->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $template->keterangan }}</div>
                            @if(!file_exists(base_path('uploads/' . $template->file_path)))
                                <div class="text-xs text-red-600 mt-1">
                                    ⚠️ File tidak ditemukan! Silakan upload ulang.
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $template->kode }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($template->seminarJenis)
                                <span class="text-gray-900">{{ $template->seminarJenis->nama }}</span>
                            @else
                                <span class="text-gray-400 italic">Semua</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @php
                                $availableTagCount = collect($template->available_tags ?? [])
                                    ->map(fn($tag) => trim((string) $tag))
                                    ->filter()
                                    ->unique(fn($tag) => mb_strtolower($tag))
                                    ->count();

                                $mappedTagCount = collect($template->tag_mappings ?? [])
                                    ->filter(fn($value) => filled($value))
                                    ->count();

                                $mappingBadgeClass = ($availableTagCount > 0 && $mappedTagCount >= $availableTagCount)
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-yellow-100 text-yellow-800';
                            @endphp

                            @if($availableTagCount > 0)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    {{ $availableTagCount }} tag
                                </span>
                                <span class="{{ $mappingBadgeClass }} px-2 py-1 rounded ml-1">
                                    {{ $mappedTagCount }} terpetakan
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full
                                {{ $template->aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $template->aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('admin.document.edit', $template->id) }}"
                                   class="text-blue-600 hover:text-blue-900 font-semibold"
                                   title="Ubah & Pemetaan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('uploads.show', $template->file_path) }}"
                                   class="text-green-600 hover:text-green-900 font-semibold"
                                   download
                                   title="Unduh Template">
                                   <i class="fas fa-download"></i>
                                </a>
                                @if($template->tag_mappings && count($template->tag_mappings) > 0)
                                    <button onclick="showPreviewModal({{ $template->id }})"
                                            class="text-purple-600 hover:text-purple-900 font-semibold"
                                            title="Pratinjau">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                <form action="{{ route('admin.document.delete', $template->id) }}"
                                      method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Hapus template {{ $template->nama }}? File template juga akan dihapus.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900"
                                            title="Hapus Template">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($templates->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada template dokumen yang tersedia.</p>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" class="w-full md:w-auto">
                @include('components.preserve-query', ['exclude' => ['page', 'per_page']])
                <input type="hidden" name="page" value="1">
                @include('components.page-size-selector', ['perPage' => $perPage ?? 15, 'autoSubmit' => true])
            </form>
            <div class="w-full md:w-auto">
                {{ $templates->links('components.pagination') }}
            </div>
        </div>
    </div>
</div>

<!-- Pratinjau Modal -->
<div id="previewModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Pratinjau - Pilih Seminar</h3>
            <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="seminarList" class="max-h-96 overflow-y-auto">
            <p class="text-center text-gray-500">Memuat...</p>
        </div>
    </div>
</div>

<script>
let currentTemplateId = null;

function showPreviewModal(templateId) {
    currentTemplateId = templateId;
    document.getElementById('previewModal').classList.remove('hidden');
    
    // Load seminar list
    fetch('/admin/api/seminars-list')
        .then(response => response.json())
        .then(data => {
            const listHtml = data.seminars.map(seminar => `
                <a href="/admin/documents/${templateId}/preview/${seminar.id}" 
                   class="block p-4 hover:bg-gray-50 border-b border-gray-200">
                    <div class="font-medium text-gray-900">${seminar.mahasiswa_nama}</div>
                    <div class="text-sm text-gray-600">
                        ${seminar.npm} | ${seminar.jenis_seminar} | ${seminar.judul.substring(0, 80)}...
                    </div>
                </a>
            `).join('');
            
            document.getElementById('seminarList').innerHTML = listHtml || '<p class="text-center text-gray-500 p-4">Tidak ada seminar tersedia</p>';
        })
        .catch(error => {
            document.getElementById('seminarList').innerHTML = '<p class="text-center text-red-500 p-4">Error loading seminars</p>';
        });
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    currentTemplateId = null;
}

// Close modal when clicking outside
document.getElementById('previewModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePreviewModal();
    }
});
</script>
@endsection
