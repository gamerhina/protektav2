@extends('layouts.app')

@section('title', 'Tambah Jenis Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tambah Jenis Seminar Baru</h1>

        <form action="{{ route('admin.seminarjenis.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Seminar</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        placeholder="Contoh: Seminar Usul, Seminar Hasil, Ujian Skripsi"
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode Jenis (tanpa spasi)</label>
                    <input
                        type="text"
                        name="kode"
                        id="kode"
                        value="{{ old('kode') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('kode') border-red-500 @enderror"
                        placeholder="Contoh: SUSUL, SHAS, UKRP"
                        required
                    >
                    @error('kode')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea
                        name="keterangan"
                        id="keterangan"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('keterangan') border-red-500 @enderror"
                        placeholder="Deskripsi tambahan tentang jenis seminar ini">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Tim Evaluator</h3>
                    <p class="text-xs text-gray-600 mb-3">Centang evaluator yang <span class="font-semibold">wajib</span> mengisi nilai & tanda tangan untuk menyelesaikan seminar.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="p1_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="p1_required" value="1" {{ old('p1_required', 1) ? 'checked' : '' }}>
                                <span>Pembimbing 1</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="p2_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="p2_required" value="1" {{ old('p2_required', 1) ? 'checked' : '' }}>
                                <span>Pembimbing 2</span>
                            </label>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" name="pembahas_required" value="0">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800">
                                <input type="checkbox" name="pembahas_required" value="1" {{ old('pembahas_required', 1) ? 'checked' : '' }}>
                                <span>Pembahas</span>
                            </label>
                        </div>
                    </div>

                    @error('p1_required')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                @if(($syaratReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur syarat seminar belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @else
                    <div>
                        <label for="syarat_seminar" class="block text-sm font-medium text-gray-700 mb-1">Syarat Seminar (Opsional)</label>
                        <x-tinymce-editor
                            name="syarat_seminar"
                            id="syarat_seminar"
                            :content="old('syarat_seminar')"
                            placeholder="Tuliskan syarat seminar yang harus dipenuhi mahasiswa (akan tampil sebelum upload berkas syarat)"
                            :has-header="false"
                            height="400"
                        />
                        @error('syarat_seminar')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if(($berkasItemsReady ?? false) === false)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Fitur upload syarat belum aktif (kolom database belum ada). Jalankan: <span class="font-mono">php artisan migrate</span>
                    </div>
                @endif

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-file-list text-blue-500"></i>
                                Konfigurasi Berkas & Form Persyaratan
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Tambahkan field sesuai kebutuhan (File upload, Text input, dll).</p>
                        </div>
                        <button type="button" id="add-berkas-item" class="btn-pill btn-pill-primary text-xs px-4 py-2">
                            <i class="fas fa-plus mr-1"></i> Tambah Field
                        </button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                        <div class="app-table-wrapper">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-3 bg-gray-50 w-8"></th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Label</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Key</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Placeholder</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aturan</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Wajib</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-16"></th>
                                    </tr>
                                </thead>
                                <tbody id="berkas-items-body" class="divide-y divide-gray-100 bg-white">
                                    <tr id="no-fields-row" style="display: none;">
                                        <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada field. Klik “Tambah Field”.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                     <p class="text-xs text-gray-500 mt-3">
                        Catatan opsi untuk <strong>Select/Radio/Checkbox</strong>: isi per baris format <span class="font-mono">value|label</span> (contoh: <span class="font-mono">mhs|Mahasiswa</span>).
                        Untuk <strong>File</strong>: isi ekstensi dipisah koma (contoh: <span class="font-mono">pdf,jpg,png</span>) dan max size dalam KB.
                    </p>
                </div>

                <!-- Evaluator Weight Percentages -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Bobot Persentase Penilai</h3>
                    <p class="text-sm text-gray-600 mb-4">Tentukan bobot persentase untuk setiap penilai. Total harus 100%.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="p1_weight" class="block text-sm font-medium text-blue-700 mb-1">Pembimbing 1 (P1) %</label>
                            <input
                                type="number"
                                name="p1_weight"
                                id="p1_weight"
                                value="{{ old('p1_weight', 35) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-blue-300 rounded-md focus:border-blue-500 focus:ring-2 focus:ring-blue-200 @error('p1_weight') border-red-500 @enderror"
                                placeholder="35"
                                required
                            >
                            @error('p1_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="p2_weight" class="block text-sm font-medium text-green-700 mb-1">Pembimbing 2 (P2) %</label>
                            <input
                                type="number"
                                name="p2_weight"
                                id="p2_weight"
                                value="{{ old('p2_weight', 35) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-green-300 rounded-md focus:border-green-500 focus:ring-2 focus:ring-green-200 @error('p2_weight') border-red-500 @enderror"
                                placeholder="35"
                                required
                            >
                            @error('p2_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pembahas_weight" class="block text-sm font-medium text-purple-700 mb-1">Pembahas (PMB) %</label>
                            <input
                                type="number"
                                name="pembahas_weight"
                                id="pembahas_weight"
                                value="{{ old('pembahas_weight', 30) }}"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full px-3 py-2 border-2 border-purple-300 rounded-md focus:border-purple-500 focus:ring-2 focus:ring-purple-200 @error('pembahas_weight') border-red-500 @enderror"
                                placeholder="30"
                                required
                            >
                            @error('pembahas_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded-lg border border-indigo-300">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Bobot:</span>
                            <span id="total-weight" class="text-lg font-bold text-indigo-600">100%</span>
                        </div>
                        <p id="weight-warning" class="text-xs text-red-600 mt-1 hidden">Total harus 100%</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.seminarjenis.index') }}" class="btn-pill btn-pill-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Jenis Seminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    function initSeminarJenisCreate() {
        const p1Weight = document.getElementById('p1_weight');
        const p2Weight = document.getElementById('p2_weight');
        const pembahasWeight = document.getElementById('pembahas_weight');
        const addBerkasBtn = document.getElementById('add-berkas-item');
        const container = document.getElementById('berkas-items-body');

        if (!p1Weight || !p2Weight || !pembahasWeight) return;
        if (p1Weight.dataset.initialized === 'true') return;

        // Weight Logic
        function updateTotalWeight() {
            const v1 = parseFloat(p1Weight.value) || 0;
            const v2 = parseFloat(p2Weight.value) || 0;
            const vm = parseFloat(pembahasWeight.value) || 0;
            const total = v1 + v2 + vm;

            const totalEl = document.getElementById('total-weight');
            const warnEl = document.getElementById('weight-warning');
            if (totalEl) {
                totalEl.textContent = total.toFixed(2) + '%';
                if (Math.abs(total - 100) < 0.01) {
                    totalEl.classList.remove('text-red-600');
                    totalEl.classList.add('text-green-600');
                    warnEl?.classList.add('hidden');
                } else {
                    totalEl.classList.remove('text-green-600');
                    totalEl.classList.add('text-red-600');
                    warnEl?.classList.remove('hidden');
                }
            }
        }

        p1Weight.addEventListener('input', updateTotalWeight);
        p2Weight.addEventListener('input', updateTotalWeight);
        pembahasWeight.addEventListener('input', updateTotalWeight);
        updateTotalWeight();

        // Custom Fields Logic
        let fieldIndex = 0;
        
        // Helper to create the type select logic
        function createTypeSelect(idx, selectedValue) {
            const types = [
                {val: 'text', label: 'Text'},
                {val: 'textarea', label: 'Textarea'},
                {val: 'number', label: 'Number'},
                {val: 'email', label: 'Email'},
                {val: 'date', label: 'Tanggal (Date)'},
                {val: 'select', label: 'Dropdown (Select)'},
                {val: 'radio', label: 'Radio Button'},
                {val: 'checkbox', label: 'Checklist (Checkbox)'},
                {val: 'file', label: 'File Upload'},
                //{val: 'pemohon', label: 'Pemohon (Info)'}, // Optional if needed
            ];
            
            let html = `<select name="berkas_syarat_items[${idx}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">`;
            types.forEach(function(t) {
                html += `<option value="${t.val}" ${t.val === selectedValue ? 'selected' : ''}>${t.label}</option>`;
            });
            html += `</select>`;
            return html;
        }

        function addBerkasRow(data = {}) {
            if (!container) return;
            const idx = fieldIndex++;
            const row = document.createElement('tr');
            row.className = 'field-row hover:bg-gray-50 transition-colors';
            
            // Def values
            const label = data.label || '';
            const key = data.key || '';
            const type = data.type || 'text';
            const placeholder = data.placeholder || '';
            const required = data.required !== false; // Default true
            const options = data.options || ''; // for select/radio
            const ext = data.extensions || ''; // for file
            const maxKb = data.max_kb || ''; // for file
            
            row.innerHTML = `
                <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="berkas_syarat_items[${idx}][label]" value="${label}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Surat Pengantar" required>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="berkas_syarat_items[${idx}][key]" value="${key}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="snake_case" required>
                    <div class="text-xs text-gray-400 mt-1">Gunakan huruf kecil & operator _</div>
                </td>
                <td class="px-4 py-3 align-top">
                    ${createTypeSelect(idx, type)}
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="berkas_syarat_items[${idx}][placeholder]" value="${placeholder}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
                </td>
                <td class="px-4 py-3 align-top">
                    <div class="space-y-2 field-config-wrap">
                        <!-- Options for Select/Radio/Checkbox -->
                        <div class="options-wrap hidden">
                            <textarea name="berkas_syarat_items[${idx}][options]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs font-mono" placeholder="value|Label">${options}</textarea>
                        </div>
                        <!-- Config for File -->
                        <div class="file-wrap hidden">
                            <div class="grid grid-cols-1 gap-2">
                                <input name="berkas_syarat_items[${idx}][extensions]" value="${ext}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg">
                                <input type="number" min="0" name="berkas_syarat_items[${idx}][max_kb]" value="${maxKb}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB (cth: 2048)">
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 align-top text-center">
                    <input type="checkbox" name="berkas_syarat_items[${idx}][required]" value="1" ${required ? 'checked' : ''} class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                </td>
                <td class="px-4 py-3 align-top text-center relative">
                    <button type="button" class="remove-field text-gray-400 hover:text-red-600 transition-colors p-2">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <input type="hidden" name="berkas_syarat_items[${idx}][sort_order]" class="sort-order" value="${idx}">
                </td>
            `;

            // Attach event listener for Type change
            const typeSelect = row.querySelector('.field-type');
            const configWrap = row.querySelector('.field-config-wrap');
            const optionsWrap = configWrap.querySelector('.options-wrap');
            const fileWrap = configWrap.querySelector('.file-wrap');

            function updateConfigVisibility() {
                const val = typeSelect.value;
                if (['select', 'radio', 'checkbox'].includes(val)) {
                    optionsWrap.classList.remove('hidden');
                    fileWrap.classList.add('hidden');
                } else if (val === 'file') {
                    optionsWrap.classList.add('hidden');
                    fileWrap.classList.remove('hidden');
                } else {
                    optionsWrap.classList.add('hidden');
                    fileWrap.classList.add('hidden');
                }
            }
            
            typeSelect.addEventListener('change', updateConfigVisibility);
            updateConfigVisibility(); // Initial check

            // Remove button
            row.querySelector('.remove-field').addEventListener('click', function() {
                row.remove();
                if (container.querySelectorAll('.field-row').length === 0) {
                     document.getElementById('no-fields-row').style.display = 'table-row';
                }
            });

            container.appendChild(row);
            document.getElementById('no-fields-row').style.display = 'none';
        }

        if (addBerkasBtn) {
            addBerkasBtn.addEventListener('click', () => addBerkasRow({}));
        }

        if (container.querySelectorAll('.field-row').length === 0) {
            addBerkasRow({});
        } else {
             // If rows existed (e.g. from old input), no manual setup needed for SortableJS
        }

        // Init SortableJS
        if (typeof Sortable !== 'undefined') {
            Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'bg-blue-50'
            });
        }
        
        p1Weight.dataset.initialized = 'true';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endpush

    // Standardized Init Pattern
    if (document.readyState !== 'loading') {
        initSeminarJenisCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSeminarJenisCreate);
    }
    window.addEventListener('app:init', initSeminarJenisCreate);
    window.addEventListener('page-loaded', initSeminarJenisCreate);
})();
</script>
@endsection
