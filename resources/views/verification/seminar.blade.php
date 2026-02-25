<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Seminar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Verification Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-8">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6 text-white text-center">
                        @php
                            $brandingSettings = \App\Models\LandingPageSetting::first();
                            $logoUrl = optional($brandingSettings)->logo_url ?? asset('assets/images/unila-logo.png');
                            $appName = optional($brandingSettings)->app_name ?? config('app.name');
                        @endphp
                        <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}" class="w-full h-full object-contain">
                        </div>
                        <h1 class="text-2xl font-bold">Dokumen Terverifikasi</h1>
                        <p class="text-emerald-100 text-sm mt-1">Berkas resmi Universitas Lampung</p>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Seminar Info -->
                        <div class="bg-slate-50 rounded-2xl p-5">
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                                <i class="fas fa-graduation-cap text-indigo-500 mr-2"></i>Informasi Seminar
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Jenis Seminar</span>
                                    <span class="font-semibold text-slate-800">{{ $seminar->seminarJenis->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tanggal</span>
                                    <span class="font-semibold text-slate-800">{{ $seminar->tanggal ? \Carbon\Carbon::parse($seminar->tanggal)->translatedFormat('d F Y') : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($seminar->status === 'selesai') bg-emerald-100 text-emerald-800
                                        @elseif($seminar->status === 'berjalan') bg-blue-100 text-blue-800
                                        @else bg-amber-100 text-amber-800
                                        @endif">
                                        {{ ucfirst($seminar->status ?? '-') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Mahasiswa Info -->
                        <div class="bg-slate-50 rounded-2xl p-5">
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                                <i class="fas fa-user text-indigo-500 mr-2"></i>Mahasiswa
                            </h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Nama</span>
                                    <span class="font-semibold text-slate-800">{{ $seminar->mahasiswa->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">NPM</span>
                                    <span class="font-semibold text-slate-800">{{ $seminar->mahasiswa->npm ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Time -->
                        <div class="text-center text-xs text-slate-400">
                            <i class="fas fa-clock mr-1"></i>
                            Diverifikasi pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 border-t border-slate-100 p-4 text-center">
                        <p class="text-xs text-slate-500">
                            <i class="fas fa-shield-alt text-emerald-500 mr-1"></i>
                            Dokumen ini dihasilkan secara resmi oleh sistem
                        </p>
                    </div>
                </div>
            </div>

            <!-- Document Preview -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-700 to-slate-800 p-4 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-file-alt text-xl text-indigo-400"></i>
                            <div>
                                <h2 class="font-bold">Pratinjau Dokumen</h2>
                                <p class="text-xs text-slate-300">Tampilan berkas yang dikeluarkan</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-slate-600"></div>
                            <div class="w-3 h-3 rounded-full bg-slate-600"></div>
                            <div class="w-3 h-3 rounded-full bg-slate-600"></div>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-slate-100 overflow-auto max-h-[85vh] scrollbar-thin scrollbar-thumb-slate-300">
                        @if(isset($previewHtml) && $previewHtml)
                            <div class="flex justify-center scale-[0.6] sm:scale-[0.8] md:scale-[0.9] lg:scale-100 origin-top duration-300">
                                {!! $previewHtml !!}
                            </div>
                        @else
                            <div class="text-center py-20">
                                <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-file-excel text-3xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-600">Pratinjau Tidak Tersedia</h3>
                                <p class="text-sm text-slate-400 mt-2">Template dokumen tidak ditemukan atau belum dikonfigurasi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
