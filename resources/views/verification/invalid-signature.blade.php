<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Tangan Tidak Valid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
            <div class="bg-slate-800 p-12 text-center text-white">
                <div class="w-24 h-24 bg-amber-500/10 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-signature text-4xl"></i>
                    <i class="fas fa-times text-2xl absolute mt-8 ml-8"></i>
                </div>
                <h1 class="text-2xl font-bold mb-2">Verifikasi Gagal</h1>
                <p class="text-slate-400 text-sm italic">{{ $message ?? 'Tanda tangan digital tidak valid atau belum disetujui.' }}</p>
            </div>
            <div class="p-8 text-center bg-white">
                <div class="bg-amber-50 p-4 rounded-2xl mb-8 border border-amber-100">
                    <p class="text-amber-800 text-xs leading-relaxed font-medium">
                        Tanda tangan digital ini mungkin telah ditarik, dialihkan, atau dokumen belum melewati proses persetujuan akhir yang sah.
                    </p>
                </div>
                @if(isset($surat))
                <div class="mb-8 text-left space-y-2">
                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Informasi Dokumen</p>
                    <div class="p-4 bg-slate-50 rounded-2xl text-xs space-y-1">
                        <p><span class="text-slate-500">Nomor:</span> <span class="font-bold text-slate-700">{{ $surat->no_surat }}</span></p>
                        <p><span class="text-slate-500">Jenis:</span> <span class="font-bold text-slate-700">{{ $surat->jenis->nama }}</span></p>
                    </div>
                </div>
                @endif
                <div class="space-y-4">
                    <a href="/" class="block w-full py-3 px-6 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-2xl transition-all shadow-lg">
                        <i class="fas fa-home mr-2 text-indigo-400"></i> Ke Beranda
                    </a>
                </div>
            </div>
            <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                    <i class="fas fa-shield-halved mr-1 text-amber-500"></i> Keamanan Dokumen Protekta
                </p>
            </div>
        </div>
    </div>
</body>
</html>
