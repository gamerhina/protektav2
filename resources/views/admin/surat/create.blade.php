@extends('layouts.app')

@section('title', 'Buat Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Buat Surat</h1>
                <p class="text-sm text-gray-500">Membuat permohonan surat dari sisi admin.</p>
            </div>
            <a href="{{ route('admin.surat.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.surat.store') }}" enctype="multipart/form-data" onsubmit="handleFormSubmit(event, this)">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5" id="top-grid">
                    <div id="jenis_wrap" class="md:col-span-2 bg-white border border-gray-200 rounded-xl p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Surat</label>
                        <select name="surat_jenis_id" id="surat_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="">Pilih jenis surat</option>
                            @foreach($jenisList as $j)
                                <option value="{{ $j->id }}" {{ old('surat_jenis_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>

                        <div id="information-container" class="mt-4 hidden p-5 bg-blue-50 rounded-2xl border-2 border-blue-100 shadow-sm transition-all duration-500">
                            <div class="flex items-center gap-2 mb-3 text-blue-800 font-bold text-xs uppercase tracking-wider">
                                <i class="fas fa-info-circle"></i> Informasi & Persyaratan
                            </div>
                            <div id="information-content" class="rich-text-content text-gray-900">
                                {{-- Content dynamic --}}
                            </div>
                        </div>
                    </div>

                    <div id="no_surat_wrap" class="md:col-span-2">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-left">
                            <div class="text-sm font-medium text-slate-800 mb-1">Nomor Surat</div>
                            <div class="flex items-center gap-3">
                                <input name="no_surat" id="no_surat" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white" placeholder="001">
                            </div>
                            <div class="text-xs text-slate-500 mt-2 italic">Default otomatis mengikuti jenis surat, tapi admin bisa edit manual.</div>
                        </div>
                    </div>

                    <div id="pemohon_wrap"></div>
                    <div id="tanggal_wrap"></div>
                </div>

                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-800">Form Permohonan</h2>
                    <p class="text-sm text-gray-500">Field akan muncul sesuai konfigurasi pada Jenis Surat.</p>
                </div>

                <div id="dynamic-fields" class="md:col-span-2 space-y-4"></div>
            </div>

            <div class="mt-8 flex justify-end">
                <button class="btn-pill btn-pill-primary" type="submit" id="submit-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection



@section('scripts')
<script>
    (function() {
        // Data loaded with limits to prevent truncation
        const jenisList = @json($jenisListPayload);
        const dosens = @json($dosens);
        const mahasiswas = @json($mahasiswas);
        const admins = @json($admins ?? []);

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    // Global function to prevent double-submit
    window.handleFormSubmit = function(event, form) {
        const submitBtn = form.querySelector('#submit-btn');
        if (!submitBtn) return true;
        
        // Check if already submitting
        if (submitBtn.disabled) {
            event.preventDefault();
            return false;
        }
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        
        // Re-enable after 5 seconds as fallback (in case of network error)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Simpan';
        }, 5000);
        
        return true;
    };

    // Global function for removing table rows
    window.removeTableRow = function(btn) {
        const row = btn.closest('tr');
        const tbody = row.closest('tbody');
        if (tbody.querySelectorAll('tr').length > 1) {
            row.remove();
            // Reindex all table rows in the document
            document.querySelectorAll('[id$="_body"]').forEach(tb => {
                const tableId = tb.id.replace('_body', '');
                const reindexFunc = window['reindexTableRows_' + tableId.replace('table_', '')];
                if (reindexFunc) reindexFunc();
            });
        } else {
            alert('Minimal harus ada 1 baris data.');
        }
    };

    async function refreshNoSurat() {
        const jenisId = document.getElementById('surat_jenis_id')?.value;
        const input = document.getElementById('no_surat');
        if (!input) return;
        if (!jenisId) {
            input.value = '';
            return;
        }

        try {
            const url = `{{ route('admin.surat.next-no-surat') }}?surat_jenis_id=${encodeURIComponent(jenisId)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (!input.dataset.userEdited || input.dataset.userEdited !== '1') {
                input.value = data?.next_no_surat || '';
            }
        } catch (e) {
            // ignore
        }
    }

    function renderPemohonField(field) {
        const sources = Array.isArray(field.pemohon_sources) && field.pemohon_sources.length
            ? field.pemohon_sources
            : ['mahasiswa','dosen'];

        const dosenOptions = dosens.map(d => `<option value="dosen:${d.id}">${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
        const mhsOptions = mahasiswas.map(m => `<option value="mahasiswa:${m.id}">${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
        const adminOptions = admins.map(a => `<option value="admin:${a.id}">${escapeHtml(a.nama)}</option>`).join('');

        const optionsHtml = `
            ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
            ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
            <optgroup label="Admin">
                <option value="admin:{{ auth('admin')->id() }}">Saya Sendiri (Admin)</option>
                ${adminOptions}
            </optgroup>
            <optgroup label="Lainnya">
                <option value="custom:0">Isi Sendiri</option>
            </optgroup>
        `;

        const fieldKey = escapeHtml(field.key);
        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">${escapeHtml(field.label)}</label>
                <input type="hidden" class="pemohon-type" name="form_data[${fieldKey}][type]" value="">
                <input type="hidden" class="pemohon-id" name="form_data[${fieldKey}][id]" value="">
                <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md mb-3">
                    <option value="">Pilih pemohon</option>
                    ${optionsHtml}
                </select>
                <div class="pemohon-custom-inputs hidden space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" name="form_data[${fieldKey}][custom_nama]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Masukkan nama lengkap">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NIP/NPM/Identitas</label>
                        <input type="text" name="form_data[${fieldKey}][custom_nip]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Masukkan NIP/NPM">
                    </div>
                </div>
            </div>
        `;
    }

    function renderField(field) {
        const key = escapeHtml(field.key);
        const label = escapeHtml(field.label);
        const requiredAttr = field.required ? 'required' : '';
        const placeholderAttr = escapeHtml(field.placeholder || '');

        if (field.type === 'pemohon') {
            return renderPemohonField(field);
        }
        if (field.type === 'auto_no_surat') {
            return '';
        }

        if (field.type === 'date') {
            const today = `{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}`;
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <input type="date" name="form_data[${key}]" value="${today}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        ${requiredAttr}
                        oninput="this.nextElementSibling.textContent = this.value ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(this.value)) : ''"
                    >
                    <p class="text-xs text-blue-600 font-semibold mt-1">${today ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(today)) : ''}</p>
                </div>
            `;
        }

        if (field.type === 'textarea') {
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <textarea name="form_data[${key}]" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="${placeholderAttr}" ${requiredAttr}></textarea>
                </div>
            `;
        }

        if (field.type === 'file') {
            const accept = Array.isArray(field.extensions) && field.extensions.length ? field.extensions.map(e => `.${e.trim().replace(/^\./, '')}`).join(',') : '';
            const exts = Array.isArray(field.extensions) ? field.extensions : [];
            const formatLabel = exts.length ? escapeHtml(exts.join(', ').toUpperCase()) : 'FILE';
            const sizeLabel = field.max_kb ? `${Math.round(field.max_kb / 1024 * 10) / 10}MB` : '5MB';

            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4 group hover:border-blue-200 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">${label}</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                ${field.required ? 'WAJIB' : 'OPSIONAL'} • ${formatLabel}
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                    </div>
                    <div class="relative group/input">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas</label>
                        <input 
                            type="file" 
                            name="form_files[${key}]" 
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all" 
                            ${accept ? `accept="${accept}"` : ''} 
                            ${requiredAttr}
                        >
                        <div class="text-[10px] text-gray-400 mt-2 italic flex gap-3 px-1">
                            <span>Maks ukuran: <span class="font-bold text-gray-600">${sizeLabel}</span></span>
                        </div>
                    </div>
                </div>
            `;
        }

        if (field.type === 'select' || field.type === 'radio') {
            const options = Array.isArray(field.options) ? field.options : [];
            const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}">${escapeHtml(o.label)}</option>`).join('');
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md" ${requiredAttr}>
                        <option value="">Pilih</option>
                        ${optionsHtml}
                    </select>
                </div>
            `;
        }

        if (field.type === 'table') {
            const columns = Array.isArray(field.columns) ? field.columns : [];
            if (!columns.length) {
                return `
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <div class="text-sm text-amber-800">Tabel "${label}" belum dikonfigurasi. Silakan tambahkan kolom di pengaturan jenis surat.</div>
                    </div>
                `;
            }

            const tableId = `table_${key}`;
            
            // Helper function to render table cell based on column type
            const renderTableCell = (col, rowIndex) => {
                const colKey = escapeHtml(col.key);
                const colLabel = escapeHtml(col.label);
                const colType = col.type || 'text';
                
                if (colType === 'pemohon') {
                    const sources = Array.isArray(col.pemohon_sources) && col.pemohon_sources.length
                        ? col.pemohon_sources
                        : ['mahasiswa','dosen'];
                    
                    const dosenOptions = dosens.map(d => `<option value="dosen:${d.id}">${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                    const mhsOptions = mahasiswas.map(m => `<option value="mahasiswa:${m.id}">${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                    const adminOptions = admins.map(a => `<option value="admin:${a.id}">${escapeHtml(a.nama)}</option>`).join('');
                    const optionsHtml = `
                        ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                        ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                        <optgroup label="Admin">
                            <option value="admin:{{ Auth::id() }}">Saya Sendiri (Admin)</option>
                            ${adminOptions}
                        </optgroup>
                        <optgroup label="Lainnya">
                            <option value="custom:0">Isi Sendiri</option>
                        </optgroup>
                    `;
                    
                    return `
                        <td class="px-4 py-2">
                            <input type="hidden" class="pemohon-type" name="form_data[${key}][${rowIndex}][${colKey}][type]" value="">
                            <input type="hidden" class="pemohon-id" name="form_data[${key}][${rowIndex}][${colKey}][id]" value="">
                            <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">Pilih ${colLabel}</option>
                                ${optionsHtml}
                            </select>
                        </td>
                    `;
                }
                
                // Default: text input
                return `
                    <td class="px-4 py-2">
                        <input type="text" name="form_data[${key}][${rowIndex}][${colKey}]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="${colLabel}">
                    </td>
                `;
            };
            
            const headerCells = '<th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 bg-gray-50 w-12 text-center">No</th>' + 
                                columns.map(col => `<th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 bg-gray-50">${escapeHtml(col.label)}</th>`).join('');
            const firstRowCells = '<td class="px-4 py-2 text-center text-sm text-gray-500 row-number">1</td>' + 
                                  columns.map(col => renderTableCell(col, 0)).join('');

            // Define table functions dynamically - use closure to access columns
            window[`addTableRow_${key}`] = (function(cols, fieldKey, tableBodyId) {
                return function() {
                    const tbody = document.getElementById(tableBodyId);
                    const rowCount = tbody.querySelectorAll('tr').length;
                    const newRow = document.createElement('tr');
                    newRow.className = 'table-row';
                    
                    let cellsHtml = '<td class="px-4 py-2 text-center text-sm text-gray-500 row-number">' + (rowCount + 1) + '</td>';
                    cols.forEach(col => {
                        const colKey = col.key;
                        const colLabel = col.label;
                        const colType = col.type || 'text';
                        
                        if (colType === 'pemohon') {
                            const sources = Array.isArray(col.pemohon_sources) && col.pemohon_sources.length
                                ? col.pemohon_sources
                                : ['mahasiswa','dosen'];
                            
                            const dosenOptions = dosens.map(d => '<option value="dosen:' + d.id + '">' + escapeHtml(d.nama) + ' (' + escapeHtml(d.nip) + ')</option>').join('');
                            const mhsOptions = mahasiswas.map(m => '<option value="mahasiswa:' + m.id + '">' + escapeHtml(m.nama) + ' (' + escapeHtml(m.npm) + ')</option>').join('');
                            const adminOptions = admins.map(a => '<option value="admin:' + a.id + '">' + escapeHtml(a.nama) + '</option>').join('');
                            
                            let optionsHtml = '';
                            if (sources.includes('mahasiswa')) {
                                optionsHtml += '<optgroup label="Mahasiswa">' + mhsOptions + '</optgroup>';
                            }
                            if (sources.includes('dosen')) {
                                optionsHtml += '<optgroup label="Dosen">' + dosenOptions + '</optgroup>';
                            }
                            optionsHtml += '<optgroup label="Admin"><option value="admin:{{ Auth::id() }}">Saya Sendiri (Admin)</option>' + adminOptions + '</optgroup>';
                            optionsHtml += '<optgroup label="Lainnya"><option value="custom:0">Isi Sendiri</option></optgroup>';
                            
                            cellsHtml += '<td class="px-4 py-2">' +
                                '<input type="hidden" class="pemohon-type" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][type]" value="">' +
                                '<input type="hidden" class="pemohon-id" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][id]" value="">' +
                                '<select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                                    '<option value="">Pilih ' + colLabel + '</option>' +
                                    optionsHtml +
                                '</select>' +
                            '</td>';
                        } else {
                            cellsHtml += '<td class="px-4 py-2">' +
                                '<input type="text" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + ']" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="' + colLabel + '">' +
                            '</td>';
                        }
                    });
                    
                    cellsHtml += '<td class="px-4 py-2 text-center">' +
                        '<button type="button" onclick="removeTableRow(this)" class="text-red-600 hover:text-red-800" title="Hapus Baris">' +
                            '<i class="fas fa-trash"></i>' +
                        '</button>' +
                    '</td>';
                    
                    newRow.innerHTML = cellsHtml;
                    tbody.appendChild(newRow);
                    
                    // Wire pemohon selects in the new row
                    newRow.querySelectorAll('.pemohon-select').forEach((select) => {
                        const cell = select.closest('td');
                        const typeInput = cell?.querySelector('.pemohon-type');
                        const idInput = cell?.querySelector('.pemohon-id');
                        if (!typeInput || !idInput) return;

                        function sync() {
                            const v = select.value || '';
                            const [t, id] = v.split(':');
                            typeInput.value = t || '';
                            idInput.value = id || '';
                        }

                        select.addEventListener('change', sync);
                        sync();
                    });
                    
                    const reindexFunc = window['reindexTableRows_' + fieldKey];
                    if (reindexFunc) reindexFunc();
                };
            })(columns, key, tableId + '_body');

            window[`reindexTableRows_${key}`] = (function(tableBodyId) {
                return function() {
                    const tbody = document.getElementById(tableBodyId);
                    if (!tbody) return;
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach((row, idx) => {
                        // Update row number
                        const numCell = row.querySelector('.row-number');
                        if (numCell) numCell.textContent = idx + 1;

                        row.querySelectorAll('input, select').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name) {
                                input.setAttribute('name', name.replace(/(\[[^\]]+\])\[\d+\]/, '$1[' + idx + ']'));
                            }
                        });
                    });
                };
            })(tableId + '_body');

            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">${label}</label>
                        <button type="button" onclick="addTableRow_${key}()" class="btn-pill btn-pill-secondary text-xs px-3 py-1">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200" id="${tableId}">
                            <thead>
                                <tr>
                                    ${headerCells}
                                    <th class="px-4 py-2 w-16 bg-gray-50"></th>
                                </tr>
                            </thead>
                            <tbody id="${tableId}_body" class="divide-y divide-gray-100">
                                <tr class="table-row">
                                    ${firstRowCells}
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" onclick="removeTableRow(this)" class="text-red-600 hover:text-red-800" title="Hapus Baris">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        if (field.type === 'checkbox') {
            const options = Array.isArray(field.options) ? field.options : [];
            if (options.length) {
                const items = options.map((o, idx) => `
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="form_data[${key}][]" value="${escapeHtml(o.value)}">
                        ${escapeHtml(o.label)}
                    </label>
                `).join('');
                return `
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <div class="text-sm font-medium text-gray-700 mb-2">${label}</div>
                        <div class="space-y-2">${items}</div>
                    </div>
                `;
            }

            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="form_data[${key}]" value="1">
                        ${label}
                    </label>
                </div>
            `;
        }

        const typeMap = {
            text: 'text',
            email: 'email',
            number: 'number',
        };
        const inputType = typeMap[field.type] || 'text';

        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                <input type="${inputType}" name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="${placeholderAttr}" ${requiredAttr}>
            </div>
        `;
    }

    function wirePemohonDynamic(container) {
        if (!container) return;
        container.querySelectorAll('.pemohon-select').forEach((select) => {
            const wrapper = select.closest('td') || select.closest('.bg-white') || select.closest('.p-4');
            const typeInput = wrapper?.querySelector('.pemohon-type');
            const idInput = wrapper?.querySelector('.pemohon-id');
            const customInputs = wrapper?.querySelector('.pemohon-custom-inputs');
            if (!typeInput || !idInput) return;

            function sync() {
                const v = select.value || '';
                const [t, id] = v.split(':');
                typeInput.value = t || '';
                idInput.value = id || '';
                
                // Toggle custom inputs visibility
                if (customInputs) {
                    if (t === 'custom') {
                        customInputs.classList.remove('hidden');
                        // Make custom inputs required when visible
                        customInputs.querySelectorAll('input').forEach(inp => inp.setAttribute('required', 'required'));
                    } else {
                        customInputs.classList.add('hidden');
                        // Remove required when hidden
                        customInputs.querySelectorAll('input').forEach(inp => {
                            inp.removeAttribute('required');
                            inp.value = ''; // Clear values
                        });
                    }
                }
            }

            select.addEventListener('change', sync);
            sync();
        });
    }

    function renderDynamicFields() {
        const jenisId = document.getElementById('surat_jenis_id')?.value;
        const container = document.getElementById('dynamic-fields');
        const pemohonWrap = document.getElementById('pemohon_wrap');
        const noSuratWrap = document.getElementById('no_surat_wrap');
        const infoContainer = document.getElementById('information-container');
        const infoContent = document.getElementById('information-content');

        if (!container) return;

        if (!jenisId) {
            container.innerHTML = `<div class="text-sm text-gray-500">Pilih jenis surat untuk menampilkan form.</div>`;
            if (pemohonWrap) pemohonWrap.innerHTML = '';
            const tanggalWrap = document.getElementById('tanggal_wrap');
            if (tanggalWrap) tanggalWrap.innerHTML = '';
            if (infoContainer) infoContainer.classList.add('hidden');
            return;
        }

        const jenis = jenisList.find(j => String(j.id) === String(jenisId));

        // Handle Information Display
        if (infoContainer && infoContent) {
            const infoText = (jenis && jenis.informasi) ? jenis.informasi.trim() : '';
            if (infoText && infoText !== '' && infoText !== '<p>&nbsp;</p>') {
                infoContent.innerHTML = infoText;
                infoContainer.classList.remove('hidden');
            } else {
                infoContainer.classList.add('hidden');
                infoContent.innerHTML = '';
            }
        }

        const fields = Array.isArray(jenis?.form_fields) ? jenis.form_fields : [];

        const pemohonField = fields.find((f) => f && typeof f === 'object' && f.type === 'pemohon');
        const tanggalField = fields.find((f) => f && typeof f === 'object' && f.type === 'date');
        const otherFields = fields.filter((f) => {
            if (!f || typeof f !== 'object') return false;
            if (f.type === 'pemohon') return false;
            if (f.type === 'auto_no_surat') return false;
            if (tanggalField && f.key === tanggalField.key && f.type === 'date') return false;
            return true;
        });

        if (pemohonWrap) {
            if (pemohonField) {
                pemohonWrap.innerHTML = renderPemohonField(pemohonField);
                pemohonWrap.classList.remove('hidden');
                wirePemohonDynamic(pemohonWrap);
            } else {
                pemohonWrap.innerHTML = '';
                pemohonWrap.classList.add('hidden');
            }
        }

        const tanggalWrap = document.getElementById('tanggal_wrap');
        if (tanggalWrap) {
            if (tanggalField) {
                tanggalWrap.innerHTML = renderField(tanggalField);
                tanggalWrap.classList.remove('hidden');
            } else {
                tanggalWrap.innerHTML = '';
                tanggalWrap.classList.add('hidden');
            }
        }

        let htmlContent = '';
        if (jenis.is_uploaded) {
            htmlContent += `
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white">
                                <i class="fas fa-file-pdf text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">Berkas PDF Utama</h3>
                                <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Wajib Diunggah</p>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <input 
                            type="file" 
                            name="uploaded_pdf" 
                            id="uploaded_pdf"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer border border-blue-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all" 
                            accept=".pdf" 
                            required
                        >
                        <p class="mt-2 text-[10px] text-gray-500 italic">Format berkas: <span class="font-bold text-blue-600">PDF</span>. Maksimal: <span class="font-bold text-blue-600">${Math.round((jenis.upload_max_kb || 10240) / 1024)} MB</span>. Berkas ini adalah dokumen yang akan diberikan tanda tangan digital.</p>
                    </div>
                </div>
            `;
        }

        htmlContent += otherFields.map(renderField).join('') || (jenis.is_uploaded ? '' : `<div class="text-sm text-gray-500">Jenis surat ini belum memiliki konfigurasi field.</div>`);
        container.innerHTML = htmlContent;
        
        // Wire all pemohon selects (in tables and other fields)
        wirePemohonDynamic(container);
    }

    function initSuratCreate() {
        const jenisSelect = document.getElementById('surat_jenis_id');
        if (!jenisSelect) return;
        if (jenisSelect.dataset.initialized === '1') return;
        jenisSelect.dataset.initialized = '1';

        const noSuratInput = document.getElementById('no_surat');
        if (noSuratInput) {
            noSuratInput.addEventListener('input', () => {
                noSuratInput.dataset.userEdited = '1';
            });
        }

        jenisSelect.addEventListener('change', () => {
            refreshNoSurat();
            renderDynamicFields();
        });

        refreshNoSurat();
        renderDynamicFields();
    }

    // Standardized Init Pattern
    if (document.readyState !== 'loading') {
        initSuratCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSuratCreate);
    }
    window.addEventListener('page-loaded', initSuratCreate);
    })();
</script>
@endsection
