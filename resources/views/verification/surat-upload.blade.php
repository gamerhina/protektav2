<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Arsip Surat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar: Archive Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-8">
                    <!-- Header with Logo (Archive Style) -->
                    <div class="bg-gradient-to-r from-slate-700 to-slate-900 p-6 text-white text-center">
                        @php
                            $brandingSettings = \App\Models\LandingPageSetting::first();
                            $logoUrl = optional($brandingSettings)->logo_url ?? asset('assets/images/unila-logo.png');
                            $appName = optional($brandingSettings)->app_name ?? config('app.name');
                        @endphp
                        <div class="w-20 h-20 bg-white p-2 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}" class="w-full h-full object-contain">
                        </div>
                        <h1 class="text-xl font-bold uppercase tracking-tight">Verifikasi Arsip</h1>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Verification Status Banner -->
                        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center gap-4 group hover:bg-emerald-100 transition-colors">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100 group-hover:scale-110 transition-transform">
                                <i class="fas fa-check-double text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xs font-black text-emerald-900 uppercase tracking-tight">Data Valid</h2>
                                <p class="text-[10px] text-emerald-600 font-bold italic">Terdapat dalam database</p>
                            </div>
                        </div>

                        <!-- Archive Info -->
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100 space-y-4">
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-info-circle text-indigo-500 text-[10px]"></i> Informasi Arsip
                            </h2>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[9px] text-slate-400 uppercase font-black block mb-0.5">Nomor Surat</label>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ $surat->no_surat ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] text-slate-400 uppercase font-black block mb-0.5">Jenis Dokumen</label>
                                    <p class="text-xs font-bold text-slate-700">{{ $surat->jenis->nama ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-[9px] text-slate-400 uppercase font-black block mb-0.5">Tanggal Terbit</label>
                                    <p class="text-xs font-bold text-slate-700">{{ $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : '-' }}</p>
                                </div>
                                @if($surat->pemohon)
                                <div>
                                    <label class="text-[9px] text-slate-400 uppercase font-black block mb-0.5">Penerimaan / Pemohon</label>
                                    <p class="text-xs font-bold text-slate-700 truncate leading-relaxed">{{ $surat->pemohon->nama ?? '-' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Scan Footer -->
                        <div class="text-center">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                                {{ now()->translatedFormat('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 border-t border-slate-100 p-5 text-center">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Jurusan Proteksi Tanaman</p>
                        <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-tight">Fakultas Pertanian Universitas Lampung</p>
                    </div>
                </div>
            </div>

            <!-- Content Area: Archive View -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-700 to-slate-800 p-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center text-white shadow-lg shadow-red-500/30">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base leading-none">Salinan Fisik</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest">Verified Scan Document</p>
                            </div>
                        </div>
                        @php
                            $filePath = $surat->generated_file_path ? 'uploads/' . $surat->generated_file_path : ($surat->uploaded_pdf_path ? 'uploads/' . $surat->uploaded_pdf_path : null);
                            $fileUrl = $filePath ? url($filePath) : '#';
                        @endphp
                        @if($filePath)
                        <a href="{{ $fileUrl }}" target="_blank" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-xs font-black flex items-center gap-2 transition-all shadow-lg active:scale-95">
                            <i class="fas fa-download"></i> UNDUH ASLI
                        </a>
                        @endif
                    </div>

                    <div class="bg-slate-100 h-[85vh] relative scrollbar-thin scrollbar-thumb-slate-300">
                        @if($surat->status === 'ditolak')
                            <div class="flex items-center justify-center h-full bg-red-50 p-12 text-center text-red-500">
                                <div class="max-w-md">
                                    <i class="fas fa-ban text-5xl mb-4 opacity-50"></i>
                                    <h4 class="text-xl font-black uppercase tracking-tight">Salinan Ditutup</h4>
                                    <p class="text-sm font-bold italic mt-2">Dokumen tidak dapat diakses.</p>
                                </div>
                            </div>
                        @elseif($filePath)
                            <iframe src="{{ $fileUrl }}#toolbar=0" class="w-full h-full" frameborder="0"></iframe>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 p-12 text-center">
                                <div>
                                    <i class="fas fa-file-circle-question text-5xl mb-4 opacity-20"></i>
                                    <h4 class="font-bold text-slate-600">Arsip Tidak Ditemukan</h4>
                                    <p class="text-xs italic">File scan untuk dokumen ini belum terunggah ke database repository.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
