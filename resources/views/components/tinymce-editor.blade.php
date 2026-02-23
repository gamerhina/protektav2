@props([
    'id' => 'tinymce-editor',
    'name' => 'html_template',
    'content' => '',
    'placeholder' => 'Mulai mengetik konten template...',
    'height' => 500,
    'tokens' => [],
    'headerImageUrl' => null,
    'hasHeader' => true,
])

<div class="tinymce-wrapper">
    <!-- Header Image Section -->
    @if($hasHeader)
    <div class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            <i class="fas fa-image mr-2"></i>Header Surat
        </label>
        <div class="flex items-center gap-4">
            <div id="{{ $id }}-header-preview" class="flex-shrink-0 w-48 h-24 bg-white border border-slate-300 rounded-lg overflow-hidden flex items-center justify-center">
                @if($headerImageUrl)
                    <img src="{{ $headerImageUrl }}" alt="Header" class="max-w-full max-h-full object-contain">
                @else
                    <span class="text-slate-400 text-sm">Belum ada header</span>
                @endif
            </div>
            <div class="flex-1">
                <input type="file" 
                       id="{{ $id }}-header-upload" 
                       name="header_image" 
                       accept="image/*"
                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-slate-500 mt-1">PNG, JPG, atau GIF. Maksimal 2MB. Rekomendasi: 800x150px</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Token Insertion Panel -->
    @if(count($tokens) > 0)
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <label class="block text-sm font-semibold text-blue-800 mb-2">
            <i class="fas fa-code mr-2"></i>Insert Token
        </label>
        <div class="flex flex-wrap gap-2" id="{{ $id }}-tokens">
            @foreach($tokens as $token => $label)
                <button type="button" 
                        onclick="insertToken{{ Str::studly($id) }}('{{ $token }}')"
                        class="px-3 py-1.5 text-xs font-medium bg-white border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                    @{{ {{ $token }} }}
                </button>
            @endforeach
        </div>
        <p class="text-xs text-blue-600 mt-2">Klik token untuk menyisipkan ke posisi kursor</p>
    </div>
    @endif

    <!-- TinyMCE Editor -->
    <textarea id="{{ $id }}" name="{{ $name }}" class="hidden">{{ $content }}</textarea>
</div>

@push('scripts')
<script src="{{ config('services.tinymce.key') === 'no-api-key' ? 'https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js' : 'https://cdn.tiny.cloud/1/'.config('services.tinymce.key').'/tinymce/6/tinymce.min.js' }}" referrerpolicy="origin"></script>
<script>
    // Use window property to avoid 'already declared' if script block is injected twice
    if (typeof window['tinyEditor{{ Str::studly($id) }}'] === 'undefined') {
        window['tinyEditor{{ Str::studly($id) }}'] = null;
    }

    function startEditor{{ Str::studly($id) }}() {
        if (!window['tinyEditor{{ Str::studly($id) }}']) {
            initTinyMCE{{ Str::studly($id) }}();
        }
        initHeaderUpload{{ Str::studly($id) }}();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startEditor{{ Str::studly($id) }});
    } else {
        startEditor{{ Str::studly($id) }}();
    }

    function initTinyMCE{{ Str::studly($id) }}() {
        tinymce.init({
            selector: '#{{ $id }}',
            height: {{ $height }},
            menubar: true,
            license_key: 'gpl',
            promotion: false,
            branding: false,
            plugins: [
                'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 
                'image', 'link', 'lists', 'media', 'searchreplace', 'table', 
                'visualblocks', 'wordcount', 'code', 'fullscreen', 'pagebreak',
                'preview', 'insertdatetime'
            ],
            toolbar: 'pagebreak | equation | undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | myCaseChange | bullist numlist outdent indent | table link fullscreen code',
            
            // Table specific settings
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | ' +
                          'tableinsertcolbefore tableinsertcolafter tabledeletecol',
            table_appearance_options: true,
            table_advtab: true,
            table_cell_advtab: true,
            table_row_advtab: true,
            table_resize_bars: true,
            table_column_resizing: 'fixed',
            table_sizing_mode: 'relative',
            object_resizing: 'table',
            font_family_formats: 'Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Bookman Old Style=bookman old style,bookman; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
            font_size_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            table_default_attributes: {
                'width': '100%',
                'border': '1'
            },
            table_default_styles: {
                'border-collapse': 'collapse'
            },
            table_cell_class_list: [
                { title: 'None', value: '' },
                { title: 'Padding Small', value: 'cell-padding-sm' },
                { title: 'Padding Medium', value: 'cell-padding-md' },
                { title: 'Padding Large', value: 'cell-padding-lg' }
            ],

            // Page break
            pagebreak_separator: '<div style="page-break-after: always;"></div>',
            pagebreak_split_block: true,

            // Content styling
            content_style: `
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12pt; 
                    line-height: 1.6;
                    padding: 20px;
                }
                table { 
                    border-collapse: collapse; 
                    margin: 10px 0;
                    border: 1px solid #333;
                    width: 100%;
                }
                table td, table th { 
                    border: 1px solid #333; 
                    padding: 8px; 
                    min-width: 10px;
                }
                .cell-padding-sm td { padding: 4px; }
                .cell-padding-md td { padding: 8px; }
                .cell-padding-lg td { padding: 16px; }
                .mce-pagebreak {
                    border: 1px dashed #999;
                    margin: 20px 0;
                    page-break-after: always;
                }
                @if(count($tokens) > 0)
                /* Token styling */
                span.token {
                    background-color: #e0f2fe;
                    border: 1px solid #0ea5e9;
                    border-radius: 4px;
                    padding: 2px 6px;
                    color: #0369a1;
                    font-family: monospace;
                }
                @endif
                img.equation {
                    vertical-align: middle;
                    margin: 5px;
                }
            `,

            // Setup callback
            setup: function(editor) {
                window['tinyEditor{{ Str::studly($id) }}'] = editor;

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
                                                    <p style="font-weight: bold; font-size: 12px; margin-bottom: 8px; color: #1e293b; display: flex; items-center: center; gap: 5px;">
                                                        <span style="background: #3b82f6; color: white; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px;">?</span>
                                                        Bantuan Pengetikan (Agar Tidak Menyamping):
                                                    </p>
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px;">
                                                        <div style="color: #475569;">📌 <b>Pecahan:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">\\frac{atas}{bawah}</code></div>
                                                        <div style="color: #475569;">📌 <b>Akar:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">\\sqrt{x}</code></div>
                                                        <div style="color: #475569;">📌 <b>Sigma:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">\\sum_{i=1}^{n}</code></div>
                                                        <div style="color: #475569;">📌 <b>Pangkat/Sub:</b> <br><code style="background:#fff; padding:2px 4px; border:1px solid #cbd5e1; border-radius:4px; font-family: monospace;">x^{2}, x_{i}</code></div>
                                                    </div>
                                                    <p style="font-size: 10px; color: #64748b; margin-top: 10px; line-height: 1.4;">
                                                        <i>Catatan: Gunakan kurung kurawal <code>{}</code> untuk mengelompokkan karakter. Pecahan otomatis menjadi atas-bawah.</i>
                                                    </p>
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
                                        // Secara otomatis tambahkan \displaystyle agar pecahan dan sigma terlihat lebih bagus (vertikal/besar)
                                        if (!latex.startsWith('\\displaystyle')) {
                                            latex = '\\displaystyle ' + latex;
                                        }
                                        
                                        const baseUrl = "https://latex.codecogs.com/svg.image?";
                                        const encodedLatex = encodeURIComponent(latex);
                                        const imageUrl = `${baseUrl}${encodedLatex}`;
                                        
                                        // Sisipkan sebagai gambar agar bisa dibaca oleh DomPDF
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

                @if(count($tokens) > 0)
                // Add custom token button
                editor.ui.registry.addMenuButton('inserttoken', {
                    text: 'Token',
                    icon: 'code-sample',
                    fetch: function(callback) {
                        var items = [
                            @foreach($tokens as $token => $label)
                            {
                                type: 'menuitem',
                                text: '{{ $label }}',
                                onAction: function() {
                                    editor.insertContent('<span class="token">@{{ {{ $token }} }}</span>');
                                }
                            },
                            @endforeach
                        ];
                        callback(items);
                    }
                });
                @endif

                editor.on('change', function() {
                    editor.save();
                });
            },

            // Auto-save content
            init_instance_callback: function(editor) {
                editor.on('blur', function() {
                    editor.save();
                });
            }
        });
    }

    function insertToken{{ Str::studly($id) }}(token) {
        const ed = window['tinyEditor{{ Str::studly($id) }}'];
        if (ed) {
            ed.insertContent('<span class="token">@{{ ' + token + ' }}</span>');
        }
    }

    function initHeaderUpload{{ Str::studly($id) }}() {
        const fileInput = document.getElementById('{{ $id }}-header-upload');
        const previewDiv = document.getElementById('{{ $id }}-header-preview');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar. Maksimal 2MB.');
                        e.target.value = '';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewDiv.innerHTML = `<img src="${event.target.result}" alt="Header Preview" class="max-w-full max-h-full object-contain">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        const ed = window['tinyEditor{{ Str::studly($id) }}'];
        if (ed) ed.remove();
    });
</script>
@endpush
