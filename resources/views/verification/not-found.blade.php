<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Tidak Ditemukan</title>
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
            <div class="bg-slate-800 p-12 text-center">
                <div class="w-24 h-24 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-file-circle-xmark text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Oops! Tidak Ditemukan</h1>
                <p class="text-slate-400 text-sm italic">{{ $message ?? 'Dokumen yang Anda cari tidak terdaftar dalam sistem kami.' }}</p>
            </div>
            <div class="p-8 text-center bg-white">
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">
                    Mohon pastikan QR Code yang Anda scan berasal dari dokumen resmi yang dikeluarkan oleh fakultas. Jika Anda merasa ini adalah kesalahan, silakan hubungi bagian administrasi.
                </p>
                <div class="space-y-4">
                    <a href="/" class="block w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl transition-all shadow-lg hover:shadow-indigo-200">
                        <i class="fas fa-home mr-2"></i> Ke Beranda
                    </a>
                </div>
            </div>
            <div class="bg-slate-50 p-4 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                    <i class="fas fa-shield-halved mr-1"></i> Sistem Protekta Unila
                </p>
            </div>
        </div>
    </div>
</body>
</html>
