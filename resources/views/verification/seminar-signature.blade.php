<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tanda Tangan Seminar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden my-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-8 text-white text-center">
            @php
                $brandingSettings = \App\Models\LandingPageSetting::first();
                $logoUrl = optional($brandingSettings)->logo_url ?? asset('assets/images/unila-logo.png');
                $appName = optional($brandingSettings)->app_name ?? config('app.name');
            @endphp
            <div class="w-24 h-24 flex items-center justify-center mx-auto mb-4">
                <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-bold">Tanda Tangan Terverifikasi</h1>
        </div>

        <!-- Content -->
        <div class="p-6 md:p-8 space-y-6">
            <!-- Signer Info -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-user-check text-indigo-500"></i>Penandatangan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-slate-500">Nama Lengkap</p>
                        <p class="font-bold text-slate-800 text-base">{{ $evaluatorName }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">NIP</p>
                        <p class="font-semibold text-slate-700">{{ $evaluatorNip }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">Peran</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                            @switch($type)
                                @case('p1') Pembimbing Utama @break
                                @case('p2') Pembimbing Pembantu @break
                                @case('pembahas') Pembahas @break
                                @default {{ ucfirst($type) }}
                            @endswitch
                        </span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">Waktu Tanda Tangan</p>
                        <p class="font-semibold text-slate-800">
                            {{ isset($approvalTime) && $approvalTime ? (\Carbon\Carbon::parse($approvalTime)->translatedFormat('d F Y, H:i') . ' WIB') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Seminar Info -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-indigo-500"></i>Informasi Seminar
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-slate-500">Nama Mahasiswa</p>
                        <p class="font-bold text-slate-800">{{ $seminar->mahasiswa->nama ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">NPM</p>
                        <p class="font-semibold text-slate-700">{{ $seminar->mahasiswa->npm ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-500">Jenis Seminar</p>
                        <p class="font-semibold text-slate-700">{{ $seminar->seminarJenis->nama ?? '-' }}</p>
                    </div>
                    <div class="space-y-1 text-slate-800">
                        <p class="text-slate-500">Status</p>
                        <p class="font-semibold">{{ ucfirst($seminar->status) }}</p>
                    </div>
                </div>
            </div>

            <!-- Preview Dokumen -->
            @if(isset($previewHtml) && $previewHtml)
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-file-alt text-indigo-500"></i>Konten Dokumen Terverifikasi
                </h2>
                <div class="bg-white p-4 md:p-8 rounded-xl border border-slate-200 overflow-auto max-h-[600px] shadow-inner">
                    <div class="flex justify-center scale-[0.55] sm:scale-[0.75] md:scale-[0.9] lg:scale-100 origin-top duration-300">
                        {!! $previewHtml !!}
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-[10px] text-slate-400 italic">
                        Pratinjau ini menunjukkan isi dokumen pada saat tanda tangan digital dibubuhkan.
                    </p>
                </div>
            </div>
            @endif

            <!-- Verification Time -->
            <div class="text-center text-xs text-slate-400">
                <i class="fas fa-clock mr-1"></i>
                Diverifikasi pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 border-t border-slate-100 p-6 text-center">
            <p class="text-xs text-slate-500">
                <i class="fas fa-shield-alt text-indigo-500 mr-1"></i>
                Tanda tangan pada dokumen ini dihasilkan secara resmi oleh sistem
            </p>
            <p class="text-[10px] text-slate-400 mt-2 font-mono">
                Verification ID: SEM-SIG-{{ str_pad($seminar->id, 6, '0', STR_PAD_LEFT) }}
            </p>
        </div>
    </div>
</body>
</html>
