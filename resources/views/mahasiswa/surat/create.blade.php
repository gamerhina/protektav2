@extends('layouts.app')

@section('title', 'Buat Permohonan Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Buat Permohonan Surat</h1>
                <p class="text-sm text-gray-500">Lengkapi data di bawah ini untuk mengajukan permohonan surat.</p>
            </div>
            <a href="{{ route('mahasiswa.surat.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('mahasiswa.surat.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div id="jenis_wrap" class="md:col-span-2 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-5 shadow-sm hover:border-blue-300 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-200">
                            <i class="fas fa-file-alt text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <label class="block text-base font-black text-blue-900 leading-tight">JENIS SURAT</label>
                            <p class="text-[10px] text-blue-600 font-medium mt-0.5">Pilih jenis surat yang ingin Anda ajukan</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="bg-blue-600 text-white text-[9px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest shadow-sm">WAJIB</span>
                        </div>
                    </div>
                    <select name="surat_jenis_id" id="surat_jenis_id" class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition-all text-sm font-medium bg-white hover:border-blue-300" required>
                        <option value="">-- Pilih Jenis Surat --</option>
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

                <div id="tanggal_wrap" class="md:col-span-2">
                    {{-- Dynamically injected or stay empty --}}
                </div>
                
                {{-- Hidden Pemohon Wrap --}}
                <div id="pemohon_wrap" class="hidden"></div>

                <div class="md:col-span-2 mt-4">
                    <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Rincian Data</h2>
                </div>

                <div id="dynamic-fields" class="md:col-span-2 grid grid-cols-1 gap-5">
                    <div class="text-sm text-gray-500 italic py-8 border border-dashed border-gray-200 rounded-xl text-center">
                        Silakan pilih jenis surat untuk menampilkan formulir.
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t flex justify-end">
                <button class="btn-pill btn-pill-primary px-10 py-2.5" type="submit">
                    Kirim Permohonan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const currentMahasiswa = @json($currentMahasiswaPayload);
        const jenisList = @json($jenisListPayload).map(j => {
            // Find in full model list to get is_uploaded status
            const original = (@json($jenisList)).find(oj => oj.id == j.id);
            return { 
                ...j, 
                is_uploaded: original ? (!!original.is_uploaded) : false 
            };
        });

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderPemohonField(field) {
            const key = escapeHtml(field.key);
            return `
                <input type="hidden" name="form_data[${key}][type]" value="mahasiswa">
                <input type="hidden" name="form_data[${key}][id]" value="${escapeHtml(currentMahasiswa.id)}">
            `;
        }

        function renderField(field, isTop = false) {
            const key = escapeHtml(field.key);
            const label = escapeHtml(field.label);
            const requiredAttr = field.required ? 'required' : '';
            const placeholderAttr = escapeHtml(field.placeholder || '');

            if (field.type === 'pemohon' || field.type === 'auto_no_surat') return '';

            // Layout classes - All fields are full width (md:col-span-2)
            const wrapperClass = isTop ? 'bg-white border border-gray-200 rounded-xl p-4 md:col-span-2' :
                                'bg-white border border-gray-200 rounded-xl p-4 md:col-span-2';
            
            let html = `<div class="${wrapperClass}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>`;

            if (field.type === 'date') {
                const today = `{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}`;
                html += `<input type="date" name="form_data[${key}]" value="${today}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none"
                    ${requiredAttr}
                    oninput="this.nextElementSibling.textContent = this.value ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(this.value)) : ''"
                >
                <p class="text-xs text-blue-600 font-semibold mt-1">${today ? new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'long',year:'numeric'}).format(new Date(today)) : ''}</p>`;
            } else if (field.type === 'textarea') {
                html += `<textarea name="form_data[${key}]" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" placeholder="${placeholderAttr}" ${requiredAttr}></textarea>`;
            } else if (field.type === 'file') {
                const exts = Array.isArray(field.extensions) ? field.extensions : [];
                const accept = exts.length ? exts.map(e => `.${e.trim().replace(/^\./, '')}`).join(',') : '';
                const formatLabel = exts.length ? `Format: ${escapeHtml(exts.join(', ').toUpperCase())}` : 'FILE';
                const sizeLabel = field.max_kb ? `${Math.round(field.max_kb / 1024 * 10) / 10}MB` : '5MB';
                
                // Card style wrapper override
                html = `<div class="${wrapperClass} group hover:border-blue-200 transition-all">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-gray-800 truncate">${label}</h3>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                    ${field.required ? 'WAJIB' : 'OPSIONAL'} • ${escapeHtml(exts.join(', ').toUpperCase())}
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
                        </div>`;
            } else if (field.type === 'select' || field.type === 'radio') {
                const options = Array.isArray(field.options) ? field.options : [];
                const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}">${escapeHtml(o.label)}</option>`).join('');
                html += `<select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" ${requiredAttr}>
                            <option value="">Pilih</option>
                            ${optionsHtml}
                        </select>`;
            } else if (field.type === 'checkbox') {
                html += `<label class="flex items-center gap-2 text-sm text-gray-700 mt-1 cursor-pointer">
                            <input type="checkbox" name="form_data[${key}]" value="1" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300 transition-all">
                            <span>${label}</span>
                        </label>`;
            } else {
                const inputType = field.type === 'number' ? 'number' : 'text';
                html += `<input type="${inputType}" name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none transition-all" placeholder="${placeholderAttr}" ${requiredAttr}>`;
            }

            html += `</div>`;
            return html;
        }

        function renderDynamicFields() {
            const jenisId = document.getElementById('surat_jenis_id')?.value;
            const container = document.getElementById('dynamic-fields');
            const pemohonWrap = document.getElementById('pemohon_wrap');
            const tanggalWrap = document.getElementById('tanggal_wrap');
            const infoContainer = document.getElementById('information-container');
            const infoContent = document.getElementById('information-content');
            
            if (!container) return;

            if (!jenisId) {
                container.innerHTML = `<div class="md:col-span-2 text-sm text-gray-500 italic py-8 border border-dashed border-gray-200 rounded-xl text-center">Silakan pilih jenis surat untuk menampilkan formulir.</div>`;
                if (pemohonWrap) pemohonWrap.innerHTML = '';
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
                pemohonWrap.innerHTML = pemohonField ? renderPemohonField(pemohonField) : '';
            }

            if (tanggalWrap) {
                tanggalWrap.innerHTML = tanggalField ? renderField(tanggalField, true) : '';
                tanggalWrap.style.display = tanggalField ? 'block' : 'none';
            }

            let dynamicHtml = otherFields.map(f => renderField(f)).join('');

            // Add PDF Upload field if this is an "uploaded" type
            if (jenis && jenis.is_uploaded) {
                const pdfUploadHtml = `
                    <div class="md:col-span-2 bg-gradient-to-br from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-2xl p-6 shadow-sm group hover:border-indigo-400 transition-all duration-300">
                        <div class="flex items-start gap-5">
                            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-indigo-200">
                                <i class="fas fa-file-pdf text-2xl text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-lg font-black text-indigo-900">DOKUMEN PDF UTAMA</h3>
                                    <span class="bg-indigo-600 text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-widest shadow-sm">WAJIB PDF</span>
                                </div>
                                <p class="text-xs text-indigo-700 font-medium leading-relaxed">
                                    Silakan unggah dokumen PDF yang akan Anda ajukan. Dokumen ini nantinya akan dibubuhi stempel/tanda tangan digital secara sistem oleh para penyetuju.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-cloud-upload-alt text-indigo-400"></i>
                            </div>
                            <input 
                                type="file" 
                                name="uploaded_pdf" 
                                accept=".pdf"
                                required
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-8 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer border-2 border-dashed border-indigo-200 rounded-2xl bg-white/50 focus:outline-none focus:border-indigo-400 transition-all pl-12 pr-4 py-2" 
                            >
                        </div>
                        
                        <div class="mt-3 flex items-center gap-4 px-2">
                            <div class="flex items-center gap-1.5 text-[10px] text-indigo-600 font-bold uppercase tracking-wider">
                                <i class="fas fa-info-circle"></i> Max 10MB
                            </div>
                            <div class="w-1 h-1 bg-indigo-200 rounded-full"></div>
                            <div class="flex items-center gap-1.5 text-[10px] text-indigo-600 font-bold uppercase tracking-wider">
                                <i class="fas fa-file-code"></i> PDF ONLY
                            </div>
                        </div>
                    </div>
                `;
                dynamicHtml = pdfUploadHtml + dynamicHtml;
            }

            const placeholder = `<div class="md:col-span-2 text-sm text-gray-500 italic py-8 border border-dashed border-gray-200 rounded-xl text-center">Jenis surat ini tidak memiliki kolom tambahan lainnya.</div>`;
            container.innerHTML = dynamicHtml || placeholder;
        }

        function initSuratMahasiswaCreate() {
            const jenisSelect = document.getElementById('surat_jenis_id');
            if (!jenisSelect) return;
            if (jenisSelect.dataset.initialized === '1') return;
            jenisSelect.dataset.initialized = '1';

            jenisSelect.addEventListener('change', renderDynamicFields);
            renderDynamicFields();
        }

        // Standardized Init Pattern
        if (document.readyState !== 'loading') {
            initSuratMahasiswaCreate();
        } else {
            document.addEventListener('DOMContentLoaded', initSuratMahasiswaCreate);
        }
        window.addEventListener('page-loaded', initSuratMahasiswaCreate);
    })();
</script>
@endsection
