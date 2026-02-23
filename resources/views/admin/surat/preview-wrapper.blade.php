<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Surat - {{ $surat->no_surat ?? 'Draft' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 flex flex-col h-screen overflow-hidden">
    <!-- Toolbar -->
    <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shadow-sm z-10 shrink-0">
        <div class="flex items-center gap-3">
            @php
                $isImpersonating = session()->has('impersonated_by');
                $_guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];
                $_effectiveGuard = null;
                foreach ($_guardPriority as $_g) {
                    if (Auth::guard($_g)->check()) { $_effectiveGuard = $_g; break; }
                }
                $_isAdmin = $_effectiveGuard === 'admin';
            @endphp
            @php
                $backUrl = route('admin.surat.show', $surat);
                if (!$_isAdmin) {
                    // Find the active approval for the current dosen on this surat
                    $dosenApproval = $surat->approvals()
                        ->where('dosen_id', Auth::guard('dosen')->id())
                        ->first();
                    if ($dosenApproval && $surat->jenis?->is_uploaded) {
                        $backUrl = route('admin.approval.stamping.show', $dosenApproval);
                    } else {
                        $backUrl = route('admin.approval.stamping.index');
                    }
                }
            @endphp
            <a href="{{ $backUrl }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-slate-50 hover:text-blue-600 hover:border-blue-300 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <div class="h-8 w-px bg-gray-200 mx-2 hidden sm:block"></div>
            <div class="hidden sm:block">
                <h1 class="text-sm font-bold text-gray-900 leading-none">Preview Dokumen</h1>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
             <button onclick="printPdf()" class="flex items-center gap-2 px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors" title="Cetak / Print">
                <i class="fas fa-print"></i>
            </button>
             <a href="{{ route('admin.surat.preview', ['surat' => $surat, 'mode' => 'stream']) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors" title="Buka di Tab Baru">
                <i class="fas fa-external-link-alt"></i>
            </a>
            @if($_isAdmin)
             <a href="{{ route('admin.surat.download', $surat) }}" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-all shadow-md shadow-blue-200">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Download PDF</span>
            </a>
            @endif
        </div>
    </div>

    <!-- PDF Viewer Iframe -->
    <div class="flex-1 w-full bg-slate-200/50 relative p-4 lg:p-8 overflow-hidden">
         <div class="w-full h-full bg-white rounded-xl shadow-2xl overflow-hidden border border-slate-300">
             <iframe id="pdf-frame" src="{{ route('admin.surat.preview', ['surat' => $surat, 'mode' => 'stream']) }}#toolbar=0" class="w-full h-full" frameborder="0"></iframe>
         </div>
    </div>

    <script>
        function printPdf() {
            const iframe = document.getElementById('pdf-frame');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }
        }
    </script>
</body>
</html>
