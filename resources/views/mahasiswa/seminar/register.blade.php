@extends('layouts.app')

@section('title', 'Daftar Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Pendaftaran Seminar</h1>

        <form action="{{ route('mahasiswa.seminar.store') }}" method="POST" enctype="multipart/form-data" onsubmit="return handleSeminarSubmit(event, this)">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select
                        name="seminar_jenis_id"
                        id="seminar_jenis_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror"
                        required
                    >
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

                <div class="space-y-2">
                    <label for="judul" class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] ml-1">Judul Seminar</label>
                    <x-tinymce-editor
                        name="judul"
                        id="judul"
                        :content="old('judul')"
                        placeholder="Contoh: ANALISIS PROTEKSI TANAMAN PADA BUDIDAYA PADI..."
                        :has-header="false"
                        height="200"
                    />
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input
                            type="date"
                            name="tanggal"
                            id="tanggal"
                            value="{{ old('tanggal') }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-500 @enderror"
                            required
                        >
                        <p class="text-sm text-gray-500 mt-1">Tanggal harus hari ini atau setelahnya</p>
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu" class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                        <input
                            type="time"
                            name="waktu"
                            id="waktu"
                            value="{{ old('waktu', '09:00') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('waktu') border-red-500 @enderror"
                            required
                        >
                        @error('waktu')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input
                        type="text"
                        name="lokasi"
                        id="lokasi"
                        value="{{ old('lokasi') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('lokasi') border-red-500 @enderror"
                        placeholder="Masukkan Lokasi Seminar"
                        required
                    >
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1 (P1)</label>
                        <div class="space-y-2">
                            <select
                                name="p1_dosen_id"
                                id="p1_dosen_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror dosen-select-toggle"
                            >
                                <option value="">Pilih Dosen</option>
                                @foreach($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('p1_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }} ({{ $dosen->nip }})
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
                        <label for="p2_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2 (P2)</label>
                        <div class="space-y-2">
                            <select
                                name="p2_dosen_id"
                                id="p2_dosen_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror dosen-select-toggle"
                            >
                                <option value="">Pilih Dosen</option>
                                @foreach($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('p2_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }} ({{ $dosen->nip }})
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
                            <select
                                name="pembahas_dosen_id"
                                id="pembahas_dosen_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror dosen-select-toggle"
                            >
                                <option value="">Pilih Dosen</option>
                                @foreach($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }} ({{ $dosen->nip }})
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
                </div>

                <div>
                    <div id="syarat-seminar-box" class="hidden rounded-xl border border-blue-200 bg-blue-50 p-4 mb-3">
                        <p class="text-sm font-semibold text-blue-800">Syarat Seminar</p>
                        <div id="syarat-seminar-text" class="mt-2 text-sm text-blue-900 whitespace-pre-line"></div>
                    </div>

                    <script type="application/json" id="seminar-jenis-syarat-data">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => $j->syarat_seminar])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <script type="application/json" id="seminar-jenis-berkas-rules">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
                            'items' => $j->berkas_syarat_items ?: [],
                        ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <script type="application/json" id="seminar-jenis-evaluator-rules">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
                            'p1_required' => (bool) ($j->p1_required ?? true),
                            'p2_required' => (bool) ($j->p2_required ?? true),
                            'pembahas_required' => (bool) ($j->pembahas_required ?? true),
                        ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <div id="berkas-syarat-dynamic"></div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="btn-pill btn-pill-primary" id="seminar-submit-btn">
                        Daftar Seminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Global function to prevent double-submit on seminar registration
window.handleSeminarSubmit = function(event, form) {
    const submitBtn = form.querySelector('#seminar-submit-btn');
    if (!submitBtn) return true;
    
    // Check if already submitting
    if (submitBtn.disabled) {
        event.preventDefault();
        return false;
    }
    
    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mendaftar...';
    
    // Re-enable after 5 seconds as fallback (in case of network error)
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Daftar Seminar';
    }, 5000);
    
    return true;
};

(function() {
    function initSeminarRegister() {
        const select = document.getElementById('seminar_jenis_id');
        if (!select || select.dataset.initialized === 'true') return;

        // 2. Syarat Seminar Text
        const box = document.getElementById('syarat-seminar-box');
        const text = document.getElementById('syarat-seminar-text');
        const dataEl = document.getElementById('seminar-jenis-syarat-data');
        let requirements = {};
        if (dataEl) {
            try { requirements = JSON.parse(dataEl.textContent || '{}'); } catch(e) {}
        }

        // 3. Berkas Dynamic Fields
        const rulesEl = document.getElementById('seminar-jenis-berkas-rules');
        const berkasContainer = document.getElementById('berkas-syarat-dynamic');
        let berkasRules = {};
        if (rulesEl) {
            try { berkasRules = JSON.parse(rulesEl.textContent || '{}'); } catch(e) {}
        }

        // 4. Evaluator Rules
        const evalRulesEl = document.getElementById('seminar-jenis-evaluator-rules');
        const p1Select = document.getElementById('p1_dosen_id');
        const p2Select = document.getElementById('p2_dosen_id');
        const pembahasSelect = document.getElementById('pembahas_dosen_id');
        let evalRules = {};
        if (evalRulesEl) {
            try { evalRules = JSON.parse(evalRulesEl.textContent || '{}'); } catch(e) {}
        }

        const render = () => {
            const id = select.value;
            
            // Render Syarat Text
            if (box && text) {
                const val = (id && requirements[id]) ? String(requirements[id]).trim() : '';
                if (val) {
                    text.innerHTML = val; // CHANGED: textContent -> innerHTML
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }
            }

            // Render Berkas / Fields
            if (berkasContainer) {
                const r = (id && berkasRules[id]) ? berkasRules[id] : null;
                const items = (r && Array.isArray(r.items)) ? r.items : [];
                berkasContainer.innerHTML = '';
                
                items.forEach(it => {
                    if (!it.key || !it.label) return;
                    
                    const div = document.createElement('div');
                    div.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group mb-4';
                    
                    const isRequired = it.required !== false;
                    const reqLabel = isRequired ? '<span class="text-red-500">*</span>' : '<span class="text-gray-400 text-xs">(Opsional)</span>';
                    
                    let inputHtml = '';
                    const fieldName = `berkas_syarat_items[${it.key}]`;
                    
                    // Parse options for select/radio/checkbox
                    const parseOpts = (optStr) => {
                        return (optStr || '').split('\n').map(line => {
                            const [val, ...lbl] = line.split('|');
                            return { v: (val||'').trim(), l: (lbl.join('|')||val||'').trim() };
                        }).filter(o => o.v);
                    };

                    switch(it.type) {
                        case 'file':
                            const exts = (Array.isArray(it.extensions) && it.extensions.length) ? it.extensions : ['pdf'];
                            const maxKb = it.max_size_kb ? Number(it.max_size_kb) : 5120;
                            const accept = exts.map(e => '.' + String(e).replace(/^\./, '')).join(',');
                            const mb = Math.round(maxKb / 102.4) / 10;
                            
                            inputHtml = `
                                <div class="relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas (${exts.join(', ').toUpperCase()})</label>
                                    <input type="file" name="${fieldName}" accept="${accept}" ${isRequired ? 'required' : ''}
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maks: <span class="font-bold text-gray-600">${mb}MB</span></p>
                                </div>
                            `;
                            break;

                        case 'textarea':
                            inputHtml = `
                                <textarea name="${fieldName}" rows="3" ${isRequired ? 'required' : ''} placeholder="${it.placeholder||''}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-shadow"></textarea>
                            `;
                            break;

                        case 'select':
                            const optsSelect = parseOpts(it.options);
                            const optionsHtml = optsSelect.map(o => `<option value="${o.v}">${o.l}</option>`).join('');
                            inputHtml = `
                                <select name="${fieldName}" ${isRequired ? 'required' : ''} class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">-- Pilih --</option>
                                    ${optionsHtml}
                                </select>
                            `;
                            break;

                        case 'radio':
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

                        case 'checkbox':
                        // Checkbox might be multiple values, so name needs []
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

                        default: // text, number, email, date
                            const type = ['text','number','email','date'].includes(it.type) ? it.type : 'text';
                            inputHtml = `
                                <input type="${type}" name="${fieldName}" value="" placeholder="${it.placeholder||''}" ${isRequired ? 'required' : ''}
                                    class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                            `;
                            break;
                    }

                    div.innerHTML = `
                        <div class="mb-1">
                            <h3 class="text-sm font-bold text-gray-800">${it.label || it.key} ${reqLabel}</h3>
                            ${it.placeholder && it.type !== 'text' && it.type !== 'textarea' ? `<p class="text-xs text-gray-400 mb-2">${it.placeholder}</p>` : ''}
                        </div>
                        ${inputHtml}
                    `;
                    
                    berkasContainer.appendChild(div);
                });
            }

            // Apply Evaluator Rules
            if (p1Select && p2Select && pembahasSelect) {
                const r = (id && evalRules[id]) ? evalRules[id] : null;
                const setReq = (el, req) => req ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                setReq(p1Select, r ? !!r.p1_required : true);
                setReq(p2Select, r ? !!r.p2_required : true);
                setReq(pembahasSelect, r ? !!r.pembahas_required : true);
            }
        };

        select.addEventListener('change', render);
        render();

        // Manual Dosen Toggle
        document.querySelectorAll('.dosen-select-toggle').forEach(selectEl => {
            selectEl.addEventListener('change', function() {
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

        select.dataset.initialized = 'true';
    }

    // Standardized Init Pattern
    window.addEventListener('app:init', initSeminarRegister);
    window.addEventListener('page-loaded', initSeminarRegister);
    
    // Fallback/Direct
    if (document.readyState !== 'loading') initSeminarRegister();
    else document.addEventListener('DOMContentLoaded', initSeminarRegister);
})();
</script>
@vite('resources/js/signature-pad.js')
@endsection
