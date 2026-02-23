@extends('layouts.app')

@section('title', 'Pratinjau & Kirim Dokumen')

@section('content')
<div class="max-w-[98%] mx-auto px-2 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Preview dan Cetak Surat</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Template: <strong>{{ $template->nama }}</strong> | 
                    Seminar: <strong>{{ $seminar->mahasiswa->nama }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.seminar.show', $seminar->id) }}" class="btn-pill btn-pill-secondary">
                Kembali
            </a>
        </div>





        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dokumen</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Template:</dt>
                        <dd class="font-medium text-gray-900">{{ $template->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Kode:</dt>
                        <dd class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $template->kode }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Mahasiswa:</dt>
                        <dd class="font-medium text-gray-900">{{ $seminar->mahasiswa->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">NPM:</dt>
                        <dd class="text-gray-900">{{ $seminar->mahasiswa->npm }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Jenis Seminar:</dt>
                        <dd class="text-gray-900">{{ $seminar->seminarJenis->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Tanggal:</dt>
                        <dd class="text-gray-900">{{ $seminar->tanggal ? $seminar->tanggal->format('d F Y') : '-' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Pratinjau Cetak</h2>
                    <p class="text-sm text-gray-600 mb-4">Lihat hasil cetakan dengan pagebreak yang sesuai sebelum dicetak.</p>
                </div>
                <div>
                    <button type="button" 
                       onclick="printPreview()"
                       class="btn-pill btn-pill-primary w-full flex items-center justify-center gap-2">
                        <i class="fas fa-print mr-1"></i>
                        Pratinjau & Cetak PDF
                    </button>
                </div>
            </div>
        </div>



        <div class="mt-8">
            <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-1">Live Preview (Render Hasil)</h2>
                        <p class="text-sm text-gray-600">Pratinjau tampilan dokumen dengan data yang sudah diisi.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="saveCustomContent()" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 hover:bg-emerald-700 shadow-sm transition-all active:scale-95">
                            <i class="fas fa-save text-sm"></i> Simpan Perubahan
                        </button>
                        <button type="button" onclick="resetToTemplate()" class="px-5 py-2 bg-amber-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 hover:bg-amber-600 shadow-sm transition-all active:scale-95">
                            <i class="fas fa-undo text-sm"></i> Reset Template
                        </button>
                        <span class="px-4 py-2 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider rounded-xl border border-blue-100 flex items-center gap-2">
                            <i class="fas fa-check-circle text-[10px]"></i> Tags Replaced
                        </span>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-300 rounded-xl shadow-inner overflow-hidden">
                    <textarea id="live_preview_editor">{!! $previewHtml !!}</textarea>
                </div>
            </div>
        </div>


    </div>
</div>




<script src="{{ config('services.tinymce.key') === 'no-api-key' ? 'https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js' : 'https://cdn.tiny.cloud/1/'.config('services.tinymce.key').'/tinymce/6/tinymce.min.js' }}" referrerpolicy="origin"></script>

<script>
function safeInitTinyMCE() {
    if (!document.getElementById('live_preview_editor')) return;

    if (typeof tinymce === 'undefined') {
        let checks = 0;
        const waiter = setInterval(() => {
            checks++;
            if (typeof tinymce !== 'undefined') {
                clearInterval(waiter);
                initTinyMCE();
            } else if (checks > 100) {
                clearInterval(waiter);
                console.error("TinyMCE failed to load.");
            }
        }, 100);
        return;
    }
    initTinyMCE();
}

function initTinyMCE() {
    tinymce.init({
        selector: '#live_preview_editor',
        height: 800,
        menubar: 'file edit view insert format tools table help',
        plugins: 'advlist autolink lists link image charmap preview anchor translate pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
        toolbar: 'pagebreak | equation | undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | myCaseChange | bullist numlist outdent indent | table link fullscreen code',
        branding: false,
        promotion: false,
        readonly: false,
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
            'width': '100%',
            'border-collapse': 'collapse'
        },
        pagebreak_separator: '<div class="page-break"></div>',
        content_style: `
            html {
                background: #f1f5f9;
                padding: 20px 0;
            }
            body { 
                font-family: 'Times New Roman', Times, serif; 
                font-size: 12pt; 
                background: white; 
                margin: 0 auto; 
                width: 21cm;
                min-height: 29.7cm;
                padding: 1.5cm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                box-sizing: border-box;
            }
            table { border-collapse: collapse; border: 1px solid #000; width: 100%; }
            table td, table th { border: 1px solid #000; padding: 8px; min-width: 10px; vertical-align: middle; }
            table p { margin: 0 !important; padding: 0 !important; }
            .pages-container {
                background: transparent;
                padding: 0;
                margin: 0;
            }
            .document-preview {
                background: transparent;
                margin: 0;
                width: 100%;
                padding: 0;
                box-shadow: none;
                border: none;
                box-sizing: border-box;
                display: block;
                position: relative;
            }
            .page-break { 
                page-break-after: always; 
                margin: 10px 0 !important;
                position: relative;
                height: 1px !important;
                border-top: 1px dashed #cbd5e1 !important;
                display: block !important;
                clear: both;
            }
            body > *:first-child { margin-top: 0 !important; }
            .page-break + * { margin-top: 0 !important; }
            .document-header-repeated + * { margin-top: 0 !important; }
            .document-header + * { margin-top: 0 !important; }
            .page-break::after {
                content: 'BATAS HALAMAN';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                color: #94a3b8;
                padding: 2px 12px;
                font-size: 10px;
                font-weight: bold;
                border: 1px solid #e2e8f0;
                border-radius: 20px;
                white-space: nowrap;
            }
        `,
        setup: function(editor) {
            window.livePreviewEditor = editor;

            // Equation Editor
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
                                                Tips Agar Tidak Menyamping:
                                            </p>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 10px;">
                                                <div style="color: #475569;">📌 <b>Pecahan:</b> <br><code>\\frac{a}{b}</code></div>
                                                <div style="color: #475569;">📌 <b>Sigma:</b> <br><code>\\sum_{i=1}^{n}</code></div>
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
            
            // Clean up empty paragraphs after page breaks and headers in the editor DOM
            editor.on('SetContent', function() {
                const markers = editor.dom.select('div.page-break, div.document-header, div.document-header-repeated');
                markers.forEach(marker => {
                    let next = marker.nextSibling;
                    while (next) {
                        // Skip whitespace text nodes
                        if (next.nodeType === 3 && !/\S/.test(next.nodeValue)) {
                            let toRemove = next;
                            next = next.nextSibling;
                            editor.dom.remove(toRemove);
                            continue;
                        }
                        
                        // If it's an empty or junk paragraph, remove and continue to next
                        const isJunk = next.nodeName === 'P' && 
                            (next.innerHTML === '&nbsp;' || 
                             next.innerHTML.trim() === '' || 
                             next.innerHTML === '<br>' || 
                             next.innerHTML === '<br data-mce-bogus="1">');
                             
                        if (isJunk) {
                            let toRemove = next;
                            next = next.nextSibling;
                            editor.dom.remove(toRemove);
                        } else {
                            // Stop if we hit real content or another block
                            break;
                        }
                    }
                });
            });
        }
    });
}

// Attach TinyMCE init to same robust events as Quill
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', safeInitTinyMCE);
} else {
    safeInitTinyMCE();
}
window.addEventListener('page-loaded', safeInitTinyMCE);
window.addEventListener('app:init', safeInitTinyMCE);
window.addEventListener('turbo:load', safeInitTinyMCE);

function saveCustomContent() {
    if (!window.livePreviewEditor) return;
    
    const content = window.livePreviewEditor.getContent();
    const saveBtn = event.currentTarget;
    const originalText = saveBtn.innerHTML;
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    fetch(`{{ route('admin.document.save-custom', $seminar->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ custom_html: content })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert('Gagal menyimpan perubahan.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat menyimpan.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

function resetToTemplate() {
    if (!confirm('Apakah Anda yakin ingin menghapus semua perubahan dan kembali ke template asli? Perubahan yang belum disimpan akan hilang.')) {
        return;
    }
    
    const resetBtn = event.currentTarget;
    const originalText = resetBtn.innerHTML;
    
    resetBtn.disabled = true;
    resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Meriset...';
    
    fetch(`{{ route('admin.document.reset-custom', $seminar->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Gagal meriset dokumen.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan saat meriset.');
    })
    .finally(() => {
        resetBtn.disabled = false;
        resetBtn.innerHTML = originalText;
    });
}

function printPreview() {
    if (!window.livePreviewEditor) return;
    
    const content = window.livePreviewEditor.getContent();
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>Print Preview</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                <style>
                    @page {
                        size: A4;
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        background: #f4f4f4;
                        font-family: 'Times New Roman', Times, serif;
                    }
                    .document-preview {
                        background: white !important;
                        box-shadow: none !important;
                        border: none !important;
                        margin: 0 auto !important;
                        width: 21cm !important;
                        min-height: 29.7cm !important;
                        padding: 0 1.5cm 1.5cm 1.5cm !important;
                        box-sizing: border-box;
                        position: relative;
                        display: block;
                    }
                    @media print {
                        body { 
                            background: white !important; 
                            padding: 0 !important; 
                        }
                        .no-print { 
                            display: none !important; 
                        }
                        .document-preview {
                            width: 100% !important;
                            padding: 0 1.5cm 1.5cm 1.5cm !important;
                            margin: 0 !important;
                            box-shadow: none !important;
                            page-break-after: always;
                            break-after: page;
                            display: block;
                        }
                        .document-preview:last-child {
                            page-break-after: avoid !important;
                            break-after: avoid !important;
                        }
                        .page-separator { 
                            display: none !important; 
                        }
                        .page-break {
                            display: none !important;
                        }
                    }
                    .page-break { 
                        page-break-after: always; 
                        break-after: page;
                        border-top: 1px dashed #ccc; 
                        margin: 20px 0;
                        height: 0;
                    }
                    .page-separator {
                        height: 60px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: #f4f4f4;
                        width: 100%;
                        margin: 0;
                        position: relative;
                    }
                    .page-separator::after {
                        content: 'PAGE BREAK';
                        color: #94a3b8;
                        font-size: 10px;
                        font-weight: bold;
                        letter-spacing: 2px;
                        padding: 4px 15px;
                        border-radius: 20px;
                        background: #f8fafc;
                    }
                    /* Fix User Alignment Issues */
                    table { border-collapse: collapse; width: 100%; }
                    td, th { 
                        vertical-align: top; 
                        padding: 2px 4px !important; 
                        line-height: 1.15 !important; 
                        height: auto !important;
                    }
                    tr { 
                        height: auto !important; 
                    }
                    /* Aggressive Reset */
                    p, li, div, h1, h2, h3, h4, h5, h6 { 
                        margin: 0 !important; 
                        padding: 0 !important;
                        line-height: 1.15 !important; 
                    }
                    p:empty { display: none; }
                    ul, ol { margin: 0; padding-left: 1.5em !important; }
                </style>
            </head>
            <body>
                <div class="no-print" style="padding: 30px 20px; text-align: center; background: #f8fafc; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px; font-family: sans-serif;">
                    <button onclick="window.print()" style="margin-bottom: 15px; padding: 12px 24px; font-size: 16px; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white; border: none; border-radius: 9999px; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-print"></i>
                        CETAK DOKUMEN PDF
                    </button>
                    <div style="margin-top: 15px; margin-bottom: 10px; font-size: 13px; color: #64748b; font-weight: 500;">
                        <span style="display: inline-block; padding: 6px 14px; background: #e2e8f0; border-radius: 8px;">Tips: Tekan Ctrl + P untuk shortcut</span>
                    </div>
                </div>
                ${content}
            </body>
        </html>
    `);
    printWindow.document.close();
}

</script>
@endsection
