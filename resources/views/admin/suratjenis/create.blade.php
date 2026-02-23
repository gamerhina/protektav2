@extends('layouts.app')

@section('title', 'Buat Jenis Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Tambah Jenis Surat</h1>
                <p class="text-sm text-slate-500">Kelola jenis surat dan template HTML.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.suratjenis.index') }}" class="btn-pill btn-pill-secondary !no-underline">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.suratjenis.store') }}">
            @csrf
            <input type="hidden" name="workflow_enabled" value="1">

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Surat</label>
                    <input name="nama" value="{{ old('nama') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode (tanpa spasi)</label>
                    <input name="kode" value="{{ old('kode') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Internal Admin)</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Catatan internal untuk admin...">{{ old('keterangan') }}</textarea>
                </div>

                <div>
                    <label for="informasi" class="block text-sm font-medium text-gray-700 mb-1">Informasi / Syarat Surat (Tampil ke Pemohon)</label>
                    <x-tinymce-editor 
                        id="informasi" 
                        name="informasi" 
                        :content="old('informasi')" 
                        placeholder="Tuliskan informasi atau persyaratan tambahan yang tampil saat mahasiswa memilih jenis surat ini..."
                        :has-header="false"
                        height="300"
                    />
                </div>

                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-gray-700">Pemohon :</span>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="target_pemohon[]" value="mahasiswa" id="perm_mhs" checked>
                            <label for="perm_mhs" class="text-sm text-gray-700 font-medium">Mahasiswa</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="target_pemohon[]" value="dosen" id="perm_dosen" checked>
                            <label for="perm_dosen" class="text-sm text-gray-700 font-medium">Dosen</label>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-gray-300"></div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', '1') ? 'checked' : '' }}>
                        <label for="aktif" class="text-sm text-gray-700 font-medium">Aktif</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="allow_download" value="1" id="allow_download" {{ old('allow_download', '1') ? 'checked' : '' }}>
                        <label for="allow_download" class="text-sm text-gray-700 font-medium">Izinkan Pemohon Unduh Surat</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_uploaded" value="1" id="is_uploaded" {{ old('is_uploaded') ? 'checked' : '' }} onchange="toggleMaxUploadSize(this)">
                        <label for="is_uploaded" class="text-sm text-indigo-700 font-bold">Jenis Surat Unggah (Stempel TTD)</label>
                    </div>

                    <div id="max_upload_size_wrap" class="{{ old('is_uploaded') ? '' : 'hidden' }} flex items-center gap-2 ml-4">
                        <label for="upload_max_kb" class="text-xs font-bold text-gray-500 uppercase">Max Size:</label>
                        <input type="number" name="upload_max_kb" id="upload_max_kb" value="{{ old('upload_max_kb', 10240) }}" class="w-24 px-2 py-1 border border-gray-300 rounded text-xs" placeholder="10240">
                        <span class="text-xs text-gray-400">KB</span>
                    </div>
                </div>

                <script>
                    function toggleMaxUploadSize(checkbox) {
                        const wrap = document.getElementById('max_upload_size_wrap');
                        if (wrap) {
                            wrap.classList.toggle('hidden', !checkbox.checked);
                        }
                    }
                </script>

                <div class="pt-6 border-t">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Alur Persetujuan & Penandatangan (Workflow)</h2>
                            <p class="text-sm text-gray-500">Pilih role pejabat dari <a href="{{ route('admin.surat-role.index') }}" class="text-indigo-600 font-semibold hover:underline" target="_blank">Master Role Persetujuan <i class="fas fa-external-link-alt text-xs"></i></a> untuk menentukan alur persetujuan surat ini.</p>
                        </div>
                        <button type="button" id="add-workflow" class="btn-pill btn-pill-info">
                            <i class="fas fa-plus"></i> Tambah Langkah Persetujuan
                        </button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 bg-gray-50 w-16 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">No</th>
                                    <th class="px-4 py-3 bg-gray-50 w-24 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Urutan</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50 tracking-widest">Role Persetujuan</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50 tracking-widest">Pejabat (Dosen)</th>
                                    <th class="px-4 py-3 bg-gray-50 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="workflow-body" class="divide-y divide-gray-100 bg-white">
                                <tr id="no-workflow-row">
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400 italic">
                                        <i class="fas fa-project-diagram text-4xl mb-3 block text-gray-200"></i>
                                        Belum ada alur persetujuan. Klik "Tambah Langkah Persetujuan" untuk memulai.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fas fa-lightbulb text-amber-500"></i>
                        Role dan pejabat penandatangan dikelola dari halaman <a href="{{ route('admin.surat-role.index') }}" class="text-indigo-600 font-semibold hover:underline" target="_blank">Role Persetujuan Surat</a>.
                        Stamp TTD dan QR Code akan mengambil data dari dosen yang terdelegasi di role tersebut.
                    </p>
                </div>

                <div class="pt-6 border-t">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Form Permohonan (Custom Fields)</h2>
                            <p class="text-sm text-gray-500">Tambahkan field sesuai kebutuhan: nama, jenis isian, placeholder, aturan, wajib/tidak.</p>
                        </div>
                        <button type="button" id="add-field" class="btn-pill btn-pill-secondary">
                            <i class="fas fa-plus"></i> Tambah Field
                        </button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
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
                            <tbody id="fields-body" class="divide-y divide-gray-100 bg-white">
                                <tr id="no-fields-row">
                                    <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada field. Klik “Tambah Field”.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Catatan opsi untuk <strong>Select/Radio/Checkbox</strong>: isi per baris format <span class="font-mono">value|label</span> (contoh: <span class="font-mono">mhs|Mahasiswa</span>).
                        Untuk <strong>File</strong>: isi ekstensi dipisah koma (contoh: <span class="font-mono">pdf,jpg,png</span>) dan max size dalam KB.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.suratjenis.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
                <button class="btn-pill btn-pill-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const fieldTypes = [
        { value: 'pemohon', label: 'Pemohon (Pilih Mahasiswa/Dosen)' },
        { value: 'auto_no_surat', label: 'Nomor Surat (Auto)' },
        { value: 'date', label: 'Tanggal (Date)' },
        { value: 'text', label: 'Text' },
        { value: 'textarea', label: 'Textarea' },
        { value: 'email', label: 'Email' },
        { value: 'number', label: 'Number' },
        { value: 'select', label: 'Dropdown (Select)' },
        { value: 'radio', label: 'Radio Button' },
        { value: 'checkbox', label: 'Checklist (Checkbox)' },
        { value: 'file', label: 'File Upload' },
    ];

    function buildTypeOptions(selected) {
        return fieldTypes.map(t => `<option value="${t.value}" ${selected === t.value ? 'selected' : ''}>${t.label}</option>`).join('');
    }

    function buildRow(index, data = {}) {
        const pemohonSources = Array.isArray(data.pemohon_sources)
            ? data.pemohon_sources
            : (Array.isArray(data.sources) ? data.sources : ['mahasiswa','dosen']);
        const label = data.label || '';
        const key = data.key || '';
        const type = data.type || 'text';
        const placeholder = data.placeholder || '';
        const required = data.required ? 'checked' : '';
        const options = Array.isArray(data.options)
            ? data.options.map(o => `${o.value}|${o.label}`).join('\n')
            : (data.options || '');
        const extensions = Array.isArray(data.extensions) ? data.extensions.join(',') : (data.extensions || '');
        const maxKb = data.max_kb || '';

        const rulesCell = `
            <div class="space-y-2">
                <div class="pemohon-wrap ${type === 'pemohon' ? '' : 'hidden'}">
                    <div class="text-xs text-gray-500 mb-1">Sumber pemohon</div>
                    <label class="inline-flex items-center gap-2 text-xs mr-3">
                        <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="mahasiswa" ${pemohonSources.includes('mahasiswa') ? 'checked' : ''}>
                        Mahasiswa
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs">
                        <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="dosen" ${pemohonSources.includes('dosen') ? 'checked' : ''}>
                        Dosen
                    </label>
                </div>
                <div class="options-wrap ${['select','radio','checkbox'].includes(type) ? '' : 'hidden'}">
                    <textarea name="form_fields[${index}][options]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="value|label\nvalue2|label2">${options || ''}</textarea>
                </div>
                <div class="file-wrap ${type === 'file' ? '' : 'hidden'}">
                    <div class="grid grid-cols-1 gap-2">
                        <input name="form_fields[${index}][extensions]" value="${extensions}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg,png">
                        <input type="number" min="0" name="form_fields[${index}][max_kb]" value="${maxKb}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB (contoh 5120)">
                    </div>
                </div>
                <div class="text-xs text-gray-400" data-hint>
                    ${type === 'pemohon' ? 'Akan menghasilkan field Pemohon (pilih mahasiswa/dosen) pada form permohonan.' : ''}
                    ${type === 'auto_no_surat' ? 'Nomor surat otomatis mengikuti jenis surat.' : ''}
                </div>
            </div>
        `;

        return `
            <tr class="field-row">
                <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][label]" value="${label}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Tujuan/Instansi" required>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][key]" value="${key}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="contoh: tujuan" required>
                    <div class="text-xs text-gray-400 mt-1">Gunakan snake_case.</div>
                </td>
                <td class="px-4 py-3 align-top">
                    <select name="form_fields[${index}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        ${buildTypeOptions(type)}
                    </select>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][placeholder]" value="${placeholder}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
                </td>
                <td class="px-4 py-3 align-top">${rulesCell}</td>
                <td class="px-4 py-3 align-top">
                    <input type="checkbox" name="form_fields[${index}][required]" value="1" ${required}>
                </td>
                <td class="px-4 py-3 align-top text-right">
                    <button type="button" class="remove-field text-red-600 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        `;
    }

    function updateRowVisibility(row) {
        const type = row.querySelector('.field-type')?.value;
        const pemohonWrap = row.querySelector('.pemohon-wrap');
        const optionsWrap = row.querySelector('.options-wrap');
        const fileWrap = row.querySelector('.file-wrap');
        const hint = row.querySelector('[data-hint]');

        if (pemohonWrap) {
            pemohonWrap.classList.toggle('hidden', type !== 'pemohon');
        }
        if (optionsWrap) {
            optionsWrap.classList.toggle('hidden', !['select','radio','checkbox'].includes(type));
        }
        if (fileWrap) {
            fileWrap.classList.toggle('hidden', type !== 'file');
        }
        if (hint) {
            hint.textContent = type === 'pemohon'
                ? 'Akan menghasilkan field Pemohon (pilih mahasiswa/dosen) pada form permohonan.'
                : (type === 'auto_no_surat' ? 'Nomor surat otomatis mengikuti jenis surat.' : '');
        }
    }

    function reindexFields() {
        const body = document.getElementById('fields-body');
        const rows = body.querySelectorAll('tr.field-row');
        rows.forEach((row, idx) => {
            row.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/form_fields\[\d+\]/, `form_fields[${idx}]`));
                }
            });
        });
    }

    function initFormFieldsBuilder() {
        const addBtn = document.getElementById('add-field');
        const body = document.getElementById('fields-body');
        const emptyRow = document.getElementById('no-fields-row');
        if (!addBtn || !body) return;

        if (addBtn.dataset.initialized === '1') return;
        addBtn.dataset.initialized = '1';

        // Initialize Sortable
        new Sortable(body, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function() {
                reindexFields();
            }
        });

        function nextIndex() {
            return body.querySelectorAll('tr.field-row').length;
        }

        function ensureEmptyRow() {
            const hasRows = body.querySelectorAll('tr.field-row').length > 0;
            if (emptyRow) {
                emptyRow.style.display = hasRows ? 'none' : '';
            }
        }

        addBtn.addEventListener('click', () => {
            const idx = nextIndex();
            body.insertAdjacentHTML('beforeend', buildRow(idx));
            ensureEmptyRow();
            reindexFields(); // Ensure correct index
        });

        body.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-field');
            if (!btn) return;
            btn.closest('tr')?.remove();
            ensureEmptyRow();
            reindexFields();
        });

        body.addEventListener('change', (e) => {
            if (!e.target.classList.contains('field-type')) return;
            updateRowVisibility(e.target.closest('tr'));
        });

        ensureEmptyRow();
    }

    // Initialize via Protekta to ensure dependencies (Sortable) are ready
    window.Protekta.registerInit(initFormFieldsBuilder);

    // WORKFLOW BUILDER
    function initWorkflowBuilder() {
        const addBtn = document.getElementById('add-workflow');
        const body = document.getElementById('workflow-body');
        const allDosens = @json($allDosens);
        const suratRoles = @json($suratRoles);

        if (!addBtn || !body) return;

        if (addBtn.dataset.workflowInitialized === '1') return;
        addBtn.dataset.workflowInitialized = '1';

        function reindexWorkflow() {
            const rows = body.querySelectorAll('tr.workflow-row');
            rows.forEach((row, idx) => {
                row.querySelector('.step-number').textContent = idx + 1;
                row.querySelectorAll('input, select').forEach(el => {
                    const name = el.getAttribute('name');
                    if (name) {
                        el.setAttribute('name', name.replace(/workflow\[\d+\]/, `workflow[${idx}]`));
                    }
                });
            });
            const noRow = document.getElementById('no-workflow-row');
            if (noRow) noRow.style.display = rows.length > 0 ? 'none' : '';
        }

        function buildRoleOptions(selected) {
            let opts = `<option value="">-- Pilih Role --</option>`;
            opts += `<option value="pembimbing_akademik" ${selected === 'pembimbing_akademik' ? 'selected' : ''}>Pembimbing Akademik (Dinamis)</option>`;
            opts += `<option value="pembimbing_1" ${selected === 'pembimbing_1' ? 'selected' : ''}>Pembimbing 1 (Dinamis)</option>`;
            opts += `<option value="pembimbing_2" ${selected === 'pembimbing_2' ? 'selected' : ''}>Pembimbing 2 (Dinamis)</option>`;
            opts += `<option value="pembahas" ${selected === 'pembahas' ? 'selected' : ''}>Pembahas (Dinamis)</option>`;
            suratRoles.forEach(r => {
                const dosenName = r.delegated_dosen ? r.delegated_dosen.nama : '';
                const dosenNip = r.delegated_dosen ? r.delegated_dosen.nip : '';
                const dosenId = r.dosen_id || '';
                const label = r.nama + (dosenName ? ` → ${dosenName}` : ' (Belum Didelegasikan)');
                opts += `<option value="role_${r.id}" data-dosen-id="${dosenId}" data-role-nama="${r.nama}" data-dosen-nama="${dosenName ? dosenName + ' (' + dosenNip + ')' : ''}" ${selected === 'role_' + r.id ? 'selected' : ''}>${label}</option>`;
            });
            opts += `<option value="custom" ${selected === 'custom' ? 'selected' : ''}>Input Manual (Custom)</option>`;
            return opts;
        }

        function buildDosenOptions(selectedId) {
            let opts = `<option value="">Pilih Dosen...</option>`;
            opts += `<option value="pembimbing_akademik" ${selectedId === 'pembimbing_akademik' ? 'selected' : ''}>-- Otomatis: PA Mahasiswa --</option>`;
            opts += `<option value="pembimbing_1" ${selectedId === 'pembimbing_1' ? 'selected' : ''}>-- Otomatis: Pembimbing 1 --</option>`;
            opts += `<option value="pembimbing_2" ${selectedId === 'pembimbing_2' ? 'selected' : ''}>-- Otomatis: Pembimbing 2 --</option>`;
            opts += `<option value="pembahas" ${selectedId === 'pembahas' ? 'selected' : ''}>-- Otomatis: Pembahas --</option>`;
            allDosens.forEach(d => {
                opts += `<option value="${d.id}" ${String(selectedId) === String(d.id) ? 'selected' : ''}>${d.nama} (${d.nip})</option>`;
            });
            return opts;
        }

        function handleRoleChange(row) {
            const roleSelect = row.querySelector('.workflow-role-select');
            const roleCustom = row.querySelector('.workflow-role-custom');
            const dosenSelect = row.querySelector('.workflow-dosen-select');
            const dosenInfo = row.querySelector('.workflow-dosen-info');
            const dosenDelegated = row.querySelector('.workflow-dosen-delegated');
            const val = roleSelect.value;

            // Reset states
            roleCustom.classList.add('hidden');
            dosenInfo.classList.add('hidden');
            dosenDelegated.classList.add('hidden');
            dosenSelect.disabled = false;

            const dynamicRoles = {
                'pembimbing_akademik': 'Pembimbing Akademik',
                'pembimbing_1': 'Pembimbing 1',
                'pembimbing_2': 'Pembimbing 2',
                'pembahas': 'Pembahas'
            };

            if (dynamicRoles[val]) {
                roleCustom.value = dynamicRoles[val];
                dosenSelect.value = val;
                dosenSelect.disabled = true;
                dosenInfo.classList.remove('hidden');
                dosenInfo.querySelector('span').innerHTML = `<i class="fas fa-info-circle"></i> Dosen ditentukan otomatis (${dynamicRoles[val]})`;
            } else if (val.startsWith('role_')) {
                const opt = roleSelect.selectedOptions[0];
                const dosenId = opt.dataset.dosenId;
                const roleName = opt.dataset.roleNama;
                const dosenNama = opt.dataset.dosenNama;
                roleCustom.value = roleName;

                if (dosenId) {
                    dosenSelect.value = dosenId;
                    dosenDelegated.classList.remove('hidden');
                    dosenDelegated.querySelector('.delegated-text').textContent = `Auto-filled: ${dosenNama}`;
                } else {
                    dosenSelect.value = '';
                }
            } else if (val === 'custom') {
                roleCustom.classList.remove('hidden');
                roleCustom.focus();
            }
        }

        function buildWorkflowRow(index) {
            const dosenOpts = buildDosenOptions('');
            const roleOpts = buildRoleOptions('');
            return `
                <tr class="workflow-row group">
                    <td class="px-4 py-3 text-center align-middle font-bold text-gray-300 step-number">
                        ${index + 1}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="number" name="workflow[${index}][urutan]" value="${index + 1}" min="1" class="w-16 px-2 py-2 border border-gray-300 rounded-xl text-sm text-center focus:ring-4 focus:ring-indigo-50 transition-all" required>
                    </td>
                    <td class="px-4 py-3">
                        <select name="workflow[${index}][role_select]" class="workflow-role-select w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-4 focus:ring-indigo-50 transition-all" required>
                            ${roleOpts}
                        </select>
                        <input name="workflow[${index}][role_nama]" class="workflow-role-custom hidden w-full mt-2 px-4 py-2 border border-dashed border-amber-400 rounded-xl text-sm bg-amber-50 focus:ring-4 focus:ring-amber-100 transition-all" placeholder="Ketik nama role manual...">
                    </td>
                    <td class="px-4 py-3">
                        <select name="workflow[${index}][dosen_id]" class="workflow-dosen-select w-full px-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-4 focus:ring-indigo-50 transition-all" required>
                            ${dosenOpts}
                        </select>
                        <div class="workflow-dosen-info text-xs mt-1 hidden">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium">
                                <i class="fas fa-info-circle"></i> Dosen ditentukan otomatis sesuai PA mahasiswa pemohon
                            </span>
                        </div>
                        <div class="workflow-dosen-delegated text-xs mt-1 hidden">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-medium">
                                <i class="fas fa-check-circle"></i> <span class="delegated-text"></span>
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center align-middle opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button" class="remove-workflow text-red-400 hover:text-red-600 p-2">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        addBtn.addEventListener('click', () => {
            const idx = body.querySelectorAll('tr.workflow-row').length;
            body.insertAdjacentHTML('beforeend', buildWorkflowRow(idx));
            reindexWorkflow();
        });

        body.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-workflow');
            if (btn) {
                btn.closest('tr').remove();
                reindexWorkflow();
            }
        });

        // Handle role dropdown change
        body.addEventListener('change', (e) => {
            if (e.target.classList.contains('workflow-role-select')) {
                handleRoleChange(e.target.closest('tr'));
            }
        });
    }

    window.Protekta.registerInit(initWorkflowBuilder);

    // Before form submit: enable disabled selects and sync role_nama
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                // Re-enable all disabled selects so their values get submitted
                form.querySelectorAll('select[disabled]').forEach(sel => {
                    sel.disabled = false;
                });

                // Ensure role_nama is populated from role_select when using master roles
                form.querySelectorAll('.workflow-role-select').forEach(sel => {
                    const row = sel.closest('tr');
                    const customInput = row.querySelector('.workflow-role-custom');
                    const val = sel.value;

                    const dynamicRoles = {
                        'pembimbing_akademik': 'Pembimbing Akademik',
                        'pembimbing_1': 'Pembimbing 1',
                        'pembimbing_2': 'Pembimbing 2',
                        'pembahas': 'Pembahas'
                    };

                    if (dynamicRoles[val]) {
                        customInput.value = customInput.value || dynamicRoles[val];
                    } else if (val.startsWith('role_')) {
                        const opt = sel.selectedOptions[0];
                        customInput.value = opt.dataset.roleNama || '';
                    }
                });
            });
        }
    });
</script>
@endsection
