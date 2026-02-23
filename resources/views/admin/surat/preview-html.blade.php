@extends('layouts.app')

@section('title', 'Pratinjau Dokumen')

@section('content')
<div class="max-w-[98%] mx-auto px-2 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Preview Hasil Template</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Jenis: <strong>{{ $surat->jenis->nama }}</strong> | 
                    Nomor: <strong>{{ $surat->no_surat ?? '-' }}</strong>
                </p>
            </div>
            <a href="{{ $backUrl }}" class="btn-pill btn-pill-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dokumen</h2>
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between border-b border-gray-50 pb-2 text-wrap gap-4">
                        <dt class="text-gray-600 font-medium shrink-0">Pemohon:</dt>
                        <dd class="font-bold text-gray-900 text-right">{{ $surat->pemohon->nama ?? $surat->mahasiswa->nama ?? '-' }}</dd>
                    </div>
                    @if($surat->mahasiswa || $surat->pemohon_type !== 'custom')
                    <div class="flex justify-between border-b border-gray-50 pb-2 gap-4">
                        <dt class="text-gray-600 font-medium shrink-0">NPM / NIP:</dt>
                        <dd class="text-gray-900 text-right">{{ $surat->mahasiswa->npm ?? $surat->pemohon->nip ?? ($surat->pemohon->npm ?? '-') }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between border-b border-gray-50 pb-2 gap-4">
                        <dt class="text-gray-600 font-medium shrink-0">Tanggal Surat:</dt>
                        <dd class="text-gray-900 text-right font-semibold">{{ $surat->tanggal_surat ? $surat->tanggal_surat->translatedFormat('d F Y') : '-' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Pratinjau Cetak</h2>
                    <p class="text-sm text-gray-600 mb-4">Lihat hasil cetakan dengan tata letak yang sesuai sebelum diunduh.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" onclick="printPreview()" class="btn-pill btn-pill-primary w-full flex items-center justify-center gap-2">
                        <i class="fas fa-print mr-1"></i>
                        Pratinjau &amp; Cetak PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm w-full p-2 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 px-2 gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-1">
                            @if(auth('admin')->check())
                                Live Editor (Render Hasil)
                            @else
                                Live Preview (Render Hasil)
                            @endif
                        </h2>
                        <p class="text-sm text-gray-600">Pratinjau tampilan dokumen dengan data dan header yang sudah diproses.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if(auth('admin')->check())
                            <button type="button" onclick="saveCustomContent()" id="btn-save" class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 hover:bg-emerald-700 shadow-sm transition-all active:scale-95">
                                <i class="fas fa-save text-sm"></i> Simpan Perubahan
                            </button>
                            <button type="button" onclick="resetToTemplate()" id="btn-reset" class="px-5 py-2 bg-amber-500 text-white text-xs font-bold uppercase tracking-wider rounded-xl flex items-center gap-2 hover:bg-amber-600 shadow-sm transition-all active:scale-95">
                                <i class="fas fa-undo text-sm"></i> Reset Template
                            </button>
                        @endif
                        <span class="px-4 py-2 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider rounded-xl border border-blue-100 flex items-center gap-2">
                            <i class="fas fa-check-circle text-[10px]"></i> Tags & Header Synced
                        </span>
                    </div>
                </div>
                
                <div class="bg-gray-200 border border-gray-300 rounded-xl shadow-inner overflow-hidden flex justify-center p-4 sm:p-8">
                    @if(auth('admin')->check())
                        <div class="w-full">
                            <textarea id="live_preview_editor">{!! $previewHtml !!}</textarea>
                        </div>
                    @else
                        <div class="bg-white shadow-2xl w-full max-w-[21cm] min-h-[29.7cm] overflow-hidden">
                            <iframe id="preview-frame" class="w-full h-[1200px]" frameborder="0"></iframe>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth('admin')->check())
<script src="{{ config('services.tinymce.key') === 'no-api-key' ? 'https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js' : 'https://cdn.tiny.cloud/1/'.config('services.tinymce.key').'/tinymce/6/tinymce.min.js' }}" referrerpolicy="origin"></script>
@endif

<script>
@if(auth('admin')->check())
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof tinymce === 'undefined') return;
        
        tinymce.init({
            selector: '#live_preview_editor',
            height: 1000,
            menubar: 'file edit view insert format tools table help',
            plugins: 'advlist autolink lists link image charmap preview anchor translate pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help',
            toolbar: 'pagebreak | undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table link fullscreen code',
            branding: false,
            promotion: false,
            table_appearance_options: true,
            table_advtab: true,
            table_cell_advtab: true,
            table_row_advtab: true,
            font_size_formats: '8pt 10pt 11pt 12pt 14pt 18pt 24pt 36pt',
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
                .page-separator {
                    display: none;
                }
            `
        });
    });

    function saveCustomContent() {
        const editor = tinymce.get('live_preview_editor');
        if (!editor) return;

        const btn = document.getElementById('btn-save');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

        fetch('{{ route('admin.surat.save-html', $surat) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                html_content: editor.getContent()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Perubahan berhasil disimpan.');
            } else {
                alert('Gagal menyimpan perubahan.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function resetToTemplate() {
        if (!confirm('Apakah Anda yakin ingin meriset konten ke template asli? Perubahan manual akan hilang.')) return;

        const btn = document.getElementById('btn-reset');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Meriset...';

        fetch('{{ route('admin.surat.reset-html', $surat) }}', {
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
                alert('Gagal meriset konten.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat meriset.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
@endif

    function printPreview() {
        const editor = typeof tinymce !== 'undefined' ? tinymce.get('live_preview_editor') : null;
        let content = '';
        
        if (editor) {
            content = editor.getContent();
        } else {
            const frame = document.getElementById('preview-frame');
            if (frame && frame.contentWindow) {
                // Try to get content from iframe if not in editor mode
                content = frame.contentWindow.document.body.innerHTML;
            }
        }

        if (!content) return;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Print Preview - {{ $template->nama ?? 'Surat' }}</title>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
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
                        CETAK SURAT SEKARANG
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

    @if(!auth('admin')->check())
    document.addEventListener('DOMContentLoaded', function() {
        const previewHtml = @json($previewHtml);
        const frame = document.getElementById('preview-frame');
        if (!frame) return;

        const doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write(previewHtml);
        doc.close();
    });
    @endif
</script>
@endsection
