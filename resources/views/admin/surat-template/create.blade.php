@extends('layouts.app')

@section('title', 'Tambah Template: ' . $suratJenis->nama)

@section('content')
<div class="max-w-[98%] mx-auto px-2 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Tambah Template: {{ $suratJenis->nama }}</h1>
            <p class="text-slate-500 mt-1 flex items-center gap-2">
                <i class="fas fa-file-signature text-indigo-500"></i> Modern HTML Template Designer for <strong>{{ $suratJenis->nama }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.surat-template.index', $suratJenis) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="button" id="btn-save-master" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Buat Template
            </button>
        </div>
    </div>

    <form id="master-form" method="POST" action="{{ route('admin.surat-template.store', $suratJenis) }}" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-8">
            <!-- Top Config (1 column grid) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="form-group lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-signature text-indigo-500"></i> Nama Template
                        </label>
                        <input name="nama" value="{{ old('nama') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all text-sm" placeholder="Contoh: Template Resmi v1" required>
                    </div>

                    <div class="form-group lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-image text-indigo-500"></i> Kop Surat (Header)
                        </label>
                        <div class="relative group">
                            <input type="file" name="header_image" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all border border-slate-100 rounded-2xl p-1 bg-slate-50/50">
                        </div>
                    </div>

                    <div class="form-group lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-redo text-indigo-500"></i> Repeat Header?
                        </label>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="header_repeat" value="1" {{ old('header_repeat') ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                            <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Ulangi di tiap hal.</span>
                        </div>
                    </div>

                    <div class="form-group lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-eye text-indigo-500"></i> Muncul di Halaman
                        </label>
                        <select name="header_visibility" id="header_visibility" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all text-xs font-semibold bg-white cursor-pointer appearance-none shadow-sm">
                            <option value="all" {{ old('header_visibility', 'all') == 'all' ? 'selected' : '' }}>Semua Halaman</option>
                            <option value="first_only" {{ old('header_visibility') == 'first_only' ? 'selected' : '' }}>Hanya Halaman 1</option>
                            <option value="except_first" {{ old('header_visibility') == 'except_first' ? 'selected' : '' }}>Semua Kecuali Hal. 1</option>
                            <option value="custom" {{ old('header_visibility') == 'custom' ? 'selected' : '' }}>Halaman Kustom (1,3,4)</option>
                        </select>
                    </div>

                    <div id="custom_pages_container" class="form-group lg:col-span-1 {{ old('header_visibility') == 'custom' ? '' : 'hidden' }}">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-list-ol text-indigo-500"></i> No. Halaman
                        </label>
                        <input name="header_custom_pages" value="{{ old('header_custom_pages') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all text-sm" placeholder="Misal: 1,3,4">
                    </div>

                    <div class="form-group lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="fas fa-pen-nib text-indigo-500"></i> Metode Tanda Tangan
                        </label>
                        <select name="signature_method" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-50 focus:border-indigo-500 transition-all text-xs font-semibold bg-white cursor-pointer appearance-none shadow-sm">
                            <option value="qr_code" {{ old('signature_method') == 'qr_code' ? 'selected' : '' }}>QR Code (Otomatis)</option>
                            <option value="manual" {{ old('signature_method') == 'manual' ? 'selected' : '' }}>Manual (Canvas)</option>
                        </select>
                    </div>
                </div>
            </div>

            </div>

            <!-- Main Editor -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-100 border border-slate-100 overflow-hidden">
                <div class="p-5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase flex items-center gap-2">
                        <i class="fas fa-magic text-indigo-400"></i> Design Canvas
                    </span>
                    <div class="flex gap-2">
                    </div>
                </div>
                <div class="p-1">
                    <textarea id="template_html" name="template_html">{{ str_replace('<!-- pagebreak -->', '<div class="page-break"></div>', old('template_html')) }}</textarea>
                </div>
            </div>

            <!-- Variable Library (Accordion UI) -->
            <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-800 overflow-hidden">
                <!-- Header -->
                <div class="p-8 border-b border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-gradient-to-br from-slate-900 to-slate-950">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                            <i class="fas fa-database text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white tracking-tight">Variable Library</h3>
                            <p class="text-slate-400 text-[10px] mt-1 flex flex-col gap-1.5">
                                <span class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-indigo-500 animate-pulse"></span>
                                    Klik untuk menyalin &lt;&lt;tag&gt;&gt; ke posisi kursor editor
                                </span>
                                <span class="flex items-center gap-2 text-indigo-400/80">
                                    <i class="fas fa-info-circle text-[9px]"></i>
                                    Tip: Gunakan &lt;&lt;tag:lebar:tinggi&gt;&gt; untuk atur ukuran gambar (misal: &lt;&lt;qr_ttd:60:60&gt;&gt;)
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="relative group">
                        <input type="text" id="tag-search" 
                               placeholder="Cari variabel..." 
                               class="w-full lg:w-96 bg-slate-800/50 border border-slate-700 rounded-2xl px-6 py-4 text-sm text-slate-100 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500/50 transition-all placeholder-slate-500 backdrop-blur-sm">
                        <i class="fas fa-search absolute right-6 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors"></i>
                    </div>
                </div>

                <!-- Accordion Container -->
                <div class="p-4 bg-slate-900/50">
                    <div id="tags-accordion" class="space-y-3">
                        @foreach($availableFields as $group => $tags)
                            <div class="tag-group border border-slate-800/50 rounded-2xl bg-slate-800/20 transition-all hover:bg-slate-800/30 overflow-hidden" data-group="{{ $group }}">
                                <!-- Accordion Trigger -->
                                <button type="button" 
                                        class="accordion-trigger w-full px-6 py-4 flex items-center justify-between text-left group transition-all"
                                        onclick="toggleAccordion(this)">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                                            <i class="fas {{ 
                                                $group == 'Mahasiswa' ? 'fa-user-graduate' : 
                                                (Str::contains($group, 'Seminar') ? 'fa-calendar-check' : 
                                                (Str::contains($group, 'Dosen') ? 'fa-user-tie' : 
                                                (Str::contains($group, 'Nilai') ? 'fa-star' : 
                                                (Str::contains($group, 'Persetujuan') ? 'fa-check-double' : 
                                                (Str::contains($group, 'Surat') ? 'fa-file-alt' : 'fa-layer-group'))))) 
                                            }} text-xs"></i>
                                        </div>
                                        <span class="text-sm font-bold text-slate-200 group-hover:text-white">{{ $group }}</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-500 font-bold border border-slate-700/50 group-hover:bg-slate-700 transition-colors">{{ count($tags) }} tags</span>
                                    </div>
                                    <i class="fas fa-chevron-down text-xs text-slate-600 transition-transform duration-300"></i>
                                </button>

                                <!-- Accordion Content -->
                                <div class="accordion-content max-h-0 opacity-0 overflow-hidden transition-all duration-300 ease-in-out">
                                    <div class="px-6 pb-6 pt-2">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                            @foreach($tags as $key => $label)
                                                <button type="button" 
                                                        class="tag-item group relative overflow-hidden text-left p-4 rounded-xl bg-slate-900/50 border border-slate-700/50 hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all flex flex-col gap-1.5 cursor-pointer"
                                                        onclick="insertTag('{{ $key }}')">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-[11px] font-mono font-bold text-indigo-400 group-hover:text-indigo-300">&lt;&lt;{{ $key }}&gt;&gt;</span>
                                                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <i class="fas fa-plus-circle text-indigo-500 text-[10px]"></i>
                                                        </div>
                                                    </div>
                                                    <span class="text-[10px] text-slate-500 group-hover:text-slate-300 line-clamp-1 font-medium">{{ $label }}</span>
                                                    
                                                    <!-- Hover Effect Gradient -->
                                                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 via-indigo-500/0 to-indigo-500/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
    
    .tox-tinymce { border: none !important; border-radius: 0 0 24px 24px !important; }
    .tox-editor-header { border-top: none !important; border-left: none !important; border-right: none !important; background-color: #f8fafc !important; }
</style>
@endsection

@section('scripts')
<script>
    // Helper function to decode HTML entities
    function decodeHtmlEntities(str) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;
        return textarea.value;
    }

    // TABLE styling
    window.applyCellStyle = function(editor, prop, value) {
        if (!editor) return;
        const cell = editor.dom.getParent(editor.selection.getNode(), 'td,th');
        if (cell) {
            editor.dom.setStyle(cell, prop, value);
            editor.focus();
        } else {
            alert('Silakan klik di dalam tabel terlebih dahulu.');
        }
    };

    window.adjustPadding = function(editor, delta) {
        if (!editor) return;
        const cell = editor.dom.getParent(editor.selection.getNode(), 'td,th');
        if (cell) {
            let current = parseInt(editor.dom.getStyle(cell, 'padding')) || 8;
            let newVal = Math.max(0, current + delta);
            editor.dom.setStyle(cell, 'padding', newVal + 'px');
            editor.focus();
        } else {
            alert('Silakan klik di dalam tabel terlebih dahulu.');
        }
    };

    window.applyTableStyle = function(editor, prop, value) {
        if (!editor) return;
        const table = editor.dom.getParent(editor.selection.getNode(), 'table');
        if (table) {
            editor.dom.setStyle(table, prop, value);
            editor.focus();
        } else {
            alert('Silakan klik di dalam tabel terlebih dahulu.');
        }
    };

    window.cleanCellSpacing = function(editor) {
        if (!editor) return;
        const cell = editor.dom.getParent(editor.selection.getNode(), 'td,th');
        if (cell) {
            editor.dom.setStyle(cell, 'vertical-align', 'top');
            const paragraphs = editor.dom.select('p', cell);
            paragraphs.forEach(p => {
                editor.dom.setStyle(p, 'margin', '0');
                editor.dom.setStyle(p, 'padding', '0');
            });
            editor.focus();
        } else {
            alert('Silakan klik di dalam tabel terlebih dahulu.');
        }
    };

    // SIMPLE TAG INSERTION
    function insertTag(tagName) {
        console.log('Inserting tag:', tagName);
        
        if (window.editorInstance && window.editorInstance.execCommand) {
            // Insert as HTML entities to prevent TinyMCE from stripping
            const encodedTag = `&lt;&lt;${tagName}&gt;&gt;`;
            window.editorInstance.execCommand('mceInsertContent', false, encodedTag);
            console.log('✅ Tag inserted as:', encodedTag);
        } else {
            console.error('❌ Editor not ready!');
            alert('Editor belum siap. Tunggu 2-3 detik lalu coba lagi.');
        }
    }

    function initDocumentDesigner() {
        console.log('[Designer] Initializing...');
        
        // Handle Submit Button
        const saveBtn = document.getElementById('btn-save-master');
        if (saveBtn) {
            const newSaveBtn = saveBtn.cloneNode(true);
            saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
            newSaveBtn.addEventListener('click', () => {
                const form = document.getElementById('master-form');
                if (form) form.submit();
            });
        }

        // Header Visibility Toggle
        const hvSelect = document.getElementById('header_visibility');
        const cpContainer = document.getElementById('custom_pages_container');
        if (hvSelect && cpContainer) {
            hvSelect.addEventListener('change', () => {
                cpContainer.classList.toggle('hidden', hvSelect.value !== 'custom');
            });
        }

        // Accordion Management
        window.toggleAccordion = function(trigger) {
            const content = trigger.nextElementSibling;
            const icon = trigger.querySelector('.fa-chevron-down');
            const parent = trigger.parentElement;
            
            // Toggle Content
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                content.style.opacity = '0';
                icon.style.transform = 'rotate(0deg)';
                parent.classList.remove('bg-slate-800/50', 'border-indigo-500/30');
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                content.style.opacity = '1';
                icon.style.transform = 'rotate(180deg)';
                parent.classList.add('bg-slate-800/50', 'border-indigo-500/30');
            }
        };

        // Tag search with auto-expand
        const searchInput = document.getElementById('tag-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const groups = document.querySelectorAll('.tag-group');
                
                groups.forEach(group => {
                    let hasMatch = false;
                    const items = group.querySelectorAll('.tag-item');
                    const trigger = group.querySelector('.accordion-trigger');
                    
                    items.forEach(item => {
                        const text = item.textContent.toLowerCase();
                        const matches = text.includes(query);
                        item.classList.toggle('hidden', !matches);
                        if (matches) hasMatch = true;
                    });
                    
                    // Show/Hide group based on search
                    group.classList.toggle('hidden', query !== '' && !hasMatch);
                    
                    // Auto-expand if matching and searching
                    if (query !== '' && hasMatch) {
                        const content = group.querySelector('.accordion-content');
                        if (!content.style.maxHeight) {
                            toggleAccordion(trigger);
                        }
                    }
                });
            });
        }

        // TinyMCE Initialization
        const initMCE = () => {
            if (typeof tinymce === 'undefined') {
                console.warn('[Designer] TinyMCE not found, retrying...');
                setTimeout(initMCE, 500);
                return;
            }

            console.log('[Designer] TinyMCE found, starting init...');
            tinymce.remove('#template_html');

            tinymce.init({
                selector: '#template_html',
                plugins: 'advlist autolink lists link image charmap preview anchor translate pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
                toolbar: 'pagebreak | equation | undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | myCaseChange | bullist numlist outdent indent | tablestyle table link fullscreen code',
                height: 'calc(100vh - 350px)',
                min_height: 500,
                branding: false,
                promotion: false,
                skin: 'oxide',
                table_appearance_options: true,
                table_advtab: true,
                table_cell_advtab: true,
                table_row_advtab: true,
                table_resize_bars: true,
                table_column_resizing: 'fixed',
                table_sizing_mode: 'relative',
                object_resizing: 'table',
                font_family_formats: 'Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Bookman Old Style=bookman old style,bookman; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
                font_size_formats: '8pt 10pt 11pt 12pt 14pt 18pt 24pt 36pt',
                table_default_attributes: {
                    'width': '100%',
                    'border': '1'
                },
                table_default_styles: {
                    'border-collapse': 'collapse'
                },
                pagebreak_separator: '<div class="page-break"></div>',
                content_style: `
                    body { font-family:Inter,system-ui,sans-serif; font-size:14px; line-height: 1.6; padding: 40px; color: #1e293b; background: white; } 
                    p { margin-bottom: 1em; } 
                    table { border-collapse: collapse; border: 1px solid #ddd; width: 100%; } 
                    table td, table th { border: 1px solid #ddd; padding: 8px; min-width: 10px; vertical-align: middle; }
                    table p { margin: 0 !important; padding: 0 !important; }
                    img.equation { vertical-align: middle; margin: 5px; }
                    .page-break { 
                        border-top: 3px dashed #6366f1 !important; 
                        margin: 40px -40px !important; 
                        position: relative; 
                        height: 16px !important; 
                        background: #f8fafc !important; 
                        display: block !important;
                        clear: both;
                    }
                    .page-break::after { 
                        content: '--- PAGE BREAK ---'; 
                        position: absolute; 
                        top: -10px; 
                        left: 50%; 
                        transform: translateX(-50%); 
                        background: #6366f1; 
                        color: white; 
                        padding: 2px 15px; 
                        font-size: 10px; 
                        font-weight: bold; 
                        border-radius: 99px;
                        z-index: 10;
                    }
                `,
                setup: function(editor) {
                    window.editorInstance = editor;

                    // Menggunakan API Gratis CodeCogs untuk merender menjadi SVG (Bagus untuk PDF)
                    editor.ui.registry.addButton('equation', {
                        text: '√x Rumus',
                        tooltip: 'Sisipkan Rumus Matematika (LaTeX)',
                        onAction: function () {
                            editor.windowManager.open({
                                title: 'Masukkan Rumus (LaTeX)',
                                body: {
                                    type: 'panel',
                                    items: [
                                        {
                                            type: 'textarea',
                                            name: 'latex',
                                            label: 'Tulis kode LaTeX',
                                            placeholder: '\\frac{a}{b} atau \\sum_{i=1}^{n}'
                                        },
                                        {
                                            type: 'htmlpanel',
                                            html: `
                                                <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px;">
                                                    <p style="font-weight: bold; font-size: 11px; margin-bottom: 8px; color: #1e293b; display: flex; items-center: center; gap: 5px;">
                                                        <span style="background: #3b82f6; color: white; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px;">?</span>
                                                        Bantuan Pengetikan (Agar Tidak Menyamping):
                                                    </p>
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px;">
                                                        <div style="color: #475569;">📌 <b>Pecahan:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">\\frac{a}{b}</code></div>
                                                        <div style="color: #475569;">📌 <b>Sigma:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">\\sum_{i=1}^{n}</code></div>
                                                    </div>
                                                </div>
                                            `
                                        }
                                    ]
                                },
                                buttons: [
                                    { type: 'cancel', text: 'Kembali' },
                                    { type: 'submit', text: 'Simpan Rumus', primary: true }
                                ],
                                onSubmit: function (api) {
                                    const data = api.getData();
                                    if (data.latex) {
                                        let latex = data.latex.trim();
                                        if (!latex.startsWith('\\displaystyle')) {
                                            latex = '\\displaystyle ' + latex;
                                        }
                                        const baseUrl = "https://latex.codecogs.com/svg.image?";
                                        const encodedLatex = encodeURIComponent(latex);
                                        const imageUrl = `${baseUrl}${encodedLatex}`;
                                        editor.insertContent(`<img src="${imageUrl}" class="equation" data-latex="${data.latex}" alt="${data.latex}" />`);
                                    }
                                    api.close();
                                }
                            });
                        }
                    });

                    // Custom Checkbox for Case Change (CSS Version - Optimized)
                    editor.on('init', function() {
                        editor.formatter.register('my_upper', { inline: 'span', styles: { 'text-transform': 'uppercase' } });
                        editor.formatter.register('my_lower', { inline: 'span', styles: { 'text-transform': 'lowercase' } });
                        editor.formatter.register('my_title', { inline: 'span', styles: { 'text-transform': 'capitalize' } });
                    });

                    editor.ui.registry.addMenuButton('myCaseChange', {
                        text: 'Aa',
                        tooltip: 'Ubah Kapitalisasi (Case)',
                        fetch: function (callback) {
                            var items = [
                                {
                                    type: 'menuitem',
                                    text: 'UPPERCASE',
                                    onAction: function () {
                                        editor.focus();
                                        editor.undoManager.transact(function() {
                                            editor.formatter.remove('my_lower');
                                            editor.formatter.remove('my_title');
                                            editor.formatter.apply('my_upper');
                                        });
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'lowercase',
                                    onAction: function () {
                                        editor.focus();
                                        editor.undoManager.transact(function() {
                                            editor.formatter.remove('my_upper');
                                            editor.formatter.remove('my_title');
                                            editor.formatter.apply('my_lower');
                                        });
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Capitalize Each Word',
                                    onAction: function () {
                                        editor.focus();
                                        editor.undoManager.transact(function() {
                                            editor.formatter.remove('my_upper');
                                            editor.formatter.remove('my_lower');
                                            editor.formatter.apply('my_title');
                                        });
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Normal (Clear Case)',
                                    onAction: function () {
                                        editor.focus();
                                        editor.undoManager.transact(function() {
                                            editor.formatter.remove('my_upper');
                                            editor.formatter.remove('my_lower');
                                            editor.formatter.remove('my_title');
                                        });
                                    }
                                }
                            ];
                            callback(items);
                        }
                    });

                    editor.ui.registry.addMenuButton('tablestyle', {
                        text: 'Cell Formatting',
                        icon: 'table',
                        tooltip: 'Format Table & Cells',
                        fetch: function (callback) {
                            var items = [
                                {
                                    type: 'menuitem',
                                    text: 'Vertical: Top',
                                    icon: 'align-left',
                                    onAction: function () { window.applyCellStyle(editor, 'vertical-align', 'top'); }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Vertical: Middle',
                                    icon: 'align-center',
                                    onAction: function () { window.applyCellStyle(editor, 'vertical-align', 'middle'); }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Vertical: Bottom',
                                    icon: 'align-right',
                                    onAction: function () { window.applyCellStyle(editor, 'vertical-align', 'bottom'); }
                                },
                                { type: 'separator' },
                                {
                                    type: 'menuitem',
                                    text: 'Set Cell Height',
                                    onAction: function () { 
                                        const h = prompt('Masukkan tinggi cell (contoh: 100px atau 2cm):', '100px');
                                        if (h) window.applyCellStyle(editor, 'height', h); 
                                    }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Auto Height',
                                    onAction: function () { window.applyCellStyle(editor, 'height', 'auto'); }
                                },
                                { type: 'separator' },
                                {
                                    type: 'menuitem',
                                    text: 'Add Padding (+)',
                                    onAction: function () { window.adjustPadding(editor, 2); }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Reduce Padding (-)',
                                    onAction: function () { window.adjustPadding(editor, -2); }
                                },
                                { type: 'separator' },
                                {
                                    type: 'menuitem',
                                    text: 'Cleanup (Mepet Atas)',
                                    onAction: function () { window.cleanCellSpacing(editor); }
                                },
                                { type: 'separator' },
                                {
                                    type: 'menuitem',
                                    text: 'Make Full Width (100%)',
                                    onAction: function () { window.applyTableStyle(editor, 'width', '100%'); }
                                },
                                {
                                    type: 'menuitem',
                                    text: 'Collapse Borders',
                                    onAction: function () { window.applyTableStyle(editor, 'border-collapse', 'collapse'); }
                                }
                            ];
                            callback(items);
                        }
                    });

                    // Sync UI with selection
                    editor.on('NodeChange', function(e) {
                        const cell = editor.dom.getParent(editor.selection.getNode(), 'td,th');
                        if (cell) {
                            const pad = editor.dom.getStyle(cell, 'padding') || '8px';
                            const h = editor.dom.getStyle(cell, 'height') || 'auto';
                            const padDisplay = document.getElementById('padding-display');
                            const hInput = document.getElementById('height-input');
                            
                            if (padDisplay) padDisplay.innerText = pad;
                            if (hInput) hInput.value = h.replace('px', '');
                        }
                    });

                    editor.on('init', function() {
                        editor.setContent(`@html_entity_decode(old('template_html', ''))`);
                    });
                }
            });
        };

        initMCE();
    }

    // Load TinyMCE if not exists
    if (typeof tinymce === 'undefined') {
        const script = document.createElement('script');
        script.src = "{{ config('services.tinymce.key') === 'no-api-key' ? 'https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js' : 'https://cdn.tiny.cloud/1/'.config('services.tinymce.key').'/tinymce/6/tinymce.min.js' }}";
        script.referrerPolicy = 'origin';
        script.onload = initDocumentDesigner;
        document.head.appendChild(script);
    } else {
        initDocumentDesigner();
    }

    // Also listen to app:init for AJAX navigation
    window.addEventListener('app:init', function() {
        if (document.getElementById('template_html')) {
            initDocumentDesigner();
        }
    });
</script>
@endsection
