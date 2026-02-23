@extends('layouts.app')

@section('title', 'Tambah Seminar Baru')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tambah Seminar Baru</h1>

        <form method="POST" action="{{ route('admin.seminar.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="mahasiswa_id" class="block text-sm font-medium text-gray-700 mb-1">Mahasiswa</label>
                    <select name="mahasiswa_id" id="mahasiswa_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('mahasiswa_id') border-red-500 @enderror" required>
                        <option value="">Pilih Mahasiswa</option>
                        @foreach($mahasiswas as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" {{ old('mahasiswa_id') == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} ({{ $mahasiswa->npm }})
                            </option>
                        @endforeach
                    </select>
                    @error('mahasiswa_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select name="seminar_jenis_id" id="seminar_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror" required>
                        <option value="">Pilih Jenis Seminar</option>
                        @foreach($seminarJenis as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('seminar_jenis_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="no_surat" class="block text-sm font-medium text-gray-700 mb-1">No. Surat</label>
                    <input 
                        type="text" 
                        name="no_surat" 
                        id="no_surat" 
                        value="{{ old('no_surat', $defaultNoSurat ?? '') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('no_surat') border-red-500 @enderror"
                        required
                    />
                    @error('no_surat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            value="{{ old('tanggal') }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-500 @enderror"
                            required
                        />
                    </div>
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="waktu_mulai" class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                    <div class="relative">
                        <input 
                            type="time" 
                            name="waktu_mulai" 
                            id="waktu_mulai" 
                            value="{{ old('waktu_mulai') }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('waktu_mulai') border-red-500 @enderror"
                            required
                        />
                    </div>
                    @error('waktu_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input 
                        type="text" 
                        name="lokasi" 
                        id="lokasi" 
                        value="{{ old('lokasi') }}" 
                        placeholder="Contoh: Ruang Rapat Lt. 2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('lokasi') border-red-500 @enderror"
                        required
                    />
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('status') border-red-500 @enderror" required>
                        <option value="diajukan" {{ old('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="disetujui" {{ old('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ old('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="belum_lengkap" {{ old('status') == 'belum_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                        <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1</label>
                    <div class="space-y-2">
                        <select name="p1_dosen_id" id="p1_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror dosen-select-toggle" required>
                            <option value="">Pilih Pembimbing 1</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p1_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p1_dosen_id') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p1_manual_fields" class="{{ old('p1_dosen_id') == 'manual' ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p1_nama" value="{{ old('p1_nama') }}" placeholder="Nama Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p1_nip" value="{{ old('p1_nip') }}" placeholder="NIP Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p1_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p2_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2</label>
                    <div class="space-y-2">
                        <select name="p2_dosen_id" id="p2_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">Pilih Pembimbing 2</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p2_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p2_dosen_id') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p2_manual_fields" class="{{ old('p2_dosen_id') == 'manual' ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p2_nama" value="{{ old('p2_nama') }}" placeholder="Nama Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p2_nip" value="{{ old('p2_nip') }}" placeholder="NIP Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p2_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pembahas_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembahas</label>
                    <div class="space-y-2">
                        <select name="pembahas_dosen_id" id="pembahas_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">Pilih Pembahas</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('pembahas_dosen_id') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="pembahas_manual_fields" class="{{ old('pembahas_dosen_id') == 'manual' ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="pembahas_nama" value="{{ old('pembahas_nama') }}" placeholder="Nama Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="pembahas_nip" value="{{ old('pembahas_nip') }}" placeholder="NIP Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('pembahas_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 space-y-2">
                    <label for="judul" class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-1">Judul Seminar</label>
                    <x-tinymce-editor
                        name="judul"
                        id="judul"
                        :content="old('judul')"
                        placeholder="Masukkan Judul Seminar..."
                        :has-header="false"
                        height="200"
                    />
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="admin-berkas-syarat-dynamic" class="mt-6"></div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.seminar.index') }}" class="btn-pill btn-pill-secondary">
                    Kembali
                </a>
                <button type="submit" class="btn-pill btn-pill-primary">
                    Simpan Seminar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ensure Quill CSS is loaded -->


<!-- Data for JavaScript -->
<script>
    window.seminarJenisMap = {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [
        $j->id => [
            'items' => $j->berkas_syarat_items ?: [],
            'info' => $j->syarat_seminar ?: ''
        ]
    ])->all()) !!};
</script>
@endsection

@section('scripts')
<script>
(function() {
    function initSeminarCreate() {
        const seminarJenisSelect = document.getElementById('seminar_jenis_id');
        const noSuratInput = document.getElementById('no_surat');
        const nextNoSuratUrl = "{{ route('admin.seminar.next-no-surat') }}";
        const editor = document.getElementById('judul-editor');
        const textarea = document.getElementById('judul');

        if (!seminarJenisSelect) return;
        
        // Prevent double-init
        if (seminarJenisSelect.dataset.initialized === 'true') return;
        seminarJenisSelect.dataset.initialized = 'true';

        console.log('[SeminarCreate] Initializing...');

        // 2. Serial Number & Dynamic Berkas Logic
        let manualOverride = false;
        noSuratInput?.addEventListener('input', () => { manualOverride = true; });

        async function updateNoSurat(jenisId) {
            if (!jenisId || !noSuratInput || !nextNoSuratUrl) return;
            manualOverride = false;
            noSuratInput.classList.add('opacity-70');
            try {
                const response = await fetch(`${nextNoSuratUrl}?seminar_jenis_id=${encodeURIComponent(jenisId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!manualOverride && data.next_no_surat) {
                    noSuratInput.value = data.next_no_surat;
                }
            } catch (error) {
                console.error('Failed to fetch nomor surat', error);
            } finally {
                noSuratInput.classList.remove('opacity-70');
            }
        }

        const berkasContainer = document.getElementById('admin-berkas-syarat-dynamic');
        const itemsMap = window.seminarJenisMap || {};

        const renderUploads = () => {
            const id = seminarJenisSelect.value;
            const data = itemsMap[id] || {};
            const items = Array.isArray(data.items) ? data.items : [];
            const info = data.info || '';

            if (!berkasContainer) return;

            if (items.length === 0 && !info) {
                berkasContainer.innerHTML = '';
                berkasContainer.classList.add('hidden');
                return;
            }

            berkasContainer.classList.remove('hidden');
            let html = '<div class="mt-8 pt-8 border-t border-gray-100">';
            
            if (info) {
                html += `
                    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
                        <p class="text-sm font-semibold text-blue-800 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Syarat Seminar
                        </p>
                        <div class="mt-2 text-sm text-blue-900 whitespace-pre-line prose prose-sm max-w-none">
                            ${info}
                        </div>
                    </div>
                `;
            }

            if (items.length > 0) {
                html += `
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-file-contract text-blue-500"></i>
                        Perysaratan & Data Tambahan
                    </h2>
                    <div class="grid grid-cols-1 gap-6" id="berkas-grid-container"></div>
                `;
            }
            
            html += '</div>';
            berkasContainer.innerHTML = html;
            
            const grid = document.getElementById('berkas-grid-container');
            if (grid && items.length > 0) {
                const parseOpts = (optStr) => {
                    return (optStr || '').split('\n').map(line => {
                        const [val, ...lbl] = line.split('|');
                        return { v: (val||'').trim(), l: (lbl.join('|')||val||'').trim() };
                    }).filter(o => o.v);
                };

                items.forEach((it) => {
                    if (!it || !it.key || !it.label) return;
                    
                    const card = document.createElement('div');
                    card.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group';
                    
                    const isRequired = it.required !== false;
                    const reqLabel = isRequired ? '<span class="text-red-500">*</span>' : '<span class="text-gray-400 text-xs">(Opsional)</span>';
                    const fieldName = `berkas_syarat_items[${it.key}]`;
                    let inputHtml = '';

                    switch(it.type) {
                        case 'file': {
                            const exts = Array.isArray(it.extensions) && it.extensions.length ? it.extensions : ['pdf'];
                            const maxKb = it.max_size_kb ? Number(it.max_size_kb) : 5120;
                            const accept = exts.map((e) => '.' + String(e).replace(/^\./, '')).join(',');
                            const mb = Math.round((maxKb / 1024) * 10) / 10;
                            
                            inputHtml = `
                                <div class="relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas (${exts.join(', ').toUpperCase()})</label>
                                    <input type="file" name="${fieldName}" accept="${accept}" ${isRequired ? 'required' : ''}
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maks: <span class="font-bold text-gray-600">${mb}MB</span></p>
                                </div>
                            `;
                            break;
                        }
                        case 'textarea':
                            inputHtml = `
                                <textarea name="${fieldName}" rows="3" ${isRequired ? 'required' : ''} placeholder="${it.placeholder||''}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-shadow"></textarea>
                            `;
                            break;
                        case 'select': {
                            const optsSelect = parseOpts(it.options);
                            const optionsHtml = optsSelect.map(o => `<option value="${o.v}">${o.l}</option>`).join('');
                            inputHtml = `
                                <select name="${fieldName}" ${isRequired ? 'required' : ''} class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">-- Pilih --</option>
                                    ${optionsHtml}
                                </select>
                            `;
                            break;
                        }
                        case 'radio': {
                            const optsRadio = parseOpts(it.options);
                            inputHtml = `<div class="space-y-2 mt-2">`;
                            optsRadio.forEach(o => {
                                inputHtml += `
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="${fieldName}" value="${o.v}" ${isRequired ? 'required' : ''} class="text-blue-600 focus:ring-blue-500 border-gray-300">
                                        <span class="text-sm text-gray-700">${o.l}</span>
                                    </label>
                                `;
                            });
                            inputHtml += `</div>`;
                            break;
                        }
                        case 'checkbox': {
                            const optsCheck = parseOpts(it.options);
                            inputHtml = `<div class="space-y-2 mt-2">`;
                            optsCheck.forEach(o => {
                                inputHtml += `
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" name="${fieldName}[]" value="${o.v}" class="text-blue-600 focus:ring-blue-500 rounded border-gray-300">
                                        <span class="text-sm text-gray-700">${o.l}</span>
                                    </label>
                                `;
                            });
                            inputHtml += `</div>`;
                            break;
                        }
                        default: {
                            const type = ['text','number','email','date'].includes(it.type) ? it.type : 'text';
                            inputHtml = `
                                <input type="${type}" name="${fieldName}" value="" placeholder="${it.placeholder||''}" ${isRequired ? 'required' : ''}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                            `;
                            break;
                        }
                    }

                    card.innerHTML = `
                        <div class="mb-1">
                            <h3 class="text-sm font-bold text-gray-800">${it.label || it.key} ${reqLabel}</h3>
                            ${it.placeholder && it.type !== 'text' && it.type !== 'textarea' ? `<p class="text-xs text-gray-400 mb-2">${it.placeholder}</p>` : ''}
                        </div>
                        ${inputHtml}
                    `;
                    grid.appendChild(card);
                });
            }
        };

        seminarJenisSelect.addEventListener('change', function () {
            manualOverride = false;
            updateNoSurat(this.value);
            renderUploads();
        });

        // Initial trigger
        if (seminarJenisSelect.value) {
            if (!noSuratInput?.value) updateNoSurat(seminarJenisSelect.value);
            renderUploads();
        }

        seminarJenisSelect.dataset.initialized = 'true';
        
        // 3. Manual Dosen Toggle
        document.querySelectorAll('.dosen-select-toggle').forEach(select => {
            select.addEventListener('change', function() {
                const targetId = this.id.replace('_dosen_id', '_manual_fields');
                const target = document.getElementById(targetId);
                if (target) {
                    if (this.value === 'manual') {
                        target.classList.remove('hidden');
                    } else {
                        target.classList.add('hidden');
                    }
                }
            });
        });
    }

    // Standardized Init Pattern
    window.addEventListener('app:init', initSeminarCreate);
    window.addEventListener('page-loaded', initSeminarCreate);
    
    if (document.readyState !== 'loading') {
        initSeminarCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSeminarCreate);
    }
})();
</script>
@endsection
