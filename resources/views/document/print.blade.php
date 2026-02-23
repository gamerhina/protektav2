<!DOCTYPE html>
<html>
<head>
    <title>Print Preview - {{ $template->nama }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
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
    <div class="pages-container">
        {!! $previewHtml !!}
    </div>
    <script>
        // Automatic print optionally
        // document.addEventListener('DOMContentLoaded', () => { setTimeout(() => window.print(), 500); });
    </script>
</body>
</html>
