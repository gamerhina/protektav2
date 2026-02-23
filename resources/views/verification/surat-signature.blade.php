<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tanda Tangan Surat</title>
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
            <!-- Sidebar: Signer Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-8">
                    <!-- Header with Logo (Signer Style) -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white text-center">
                        <div class="w-20 h-20 bg-white p-2 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <img src="{{ asset('assets/images/unila-logo.png') }}" alt="Logo Universitas Lampung" class="w-full h-full object-contain">
                        </div>
                        <h1 class="text-xl font-bold uppercase tracking-tight leading-tight">Tanda Tangan Terverifikasi</h1>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Signer Identifier -->
                        <div class="bg-indigo-50 rounded-2xl p-5 border border-indigo-100 relative overflow-hidden group">
                            <h2 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-id-card-clip text-indigo-500"></i> Detail Penandatangan
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Nama Lengkap</p>
                                    <p class="text-sm font-black text-slate-800 leading-tight">{{ $approverName ?? '-' }}</p>
                                </div>
                                @if($approverNip && $approverNip !== '-')
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">NIP / Identitas</p>
                                    <p class="text-sm font-bold text-slate-700 tracking-tight">{{ $approverNip }}</p>
                                </div>
                                @endif
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Jabatan / Peran</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-800 mt-1 uppercase">
                                        {{ $roleName ?? ucfirst($type) }}
                                    </span>
                                </div>
                                <div class="pt-3 border-t border-indigo-200/50">
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Waktu Penandatanganan</p>
                                    <p class="text-xs font-bold text-indigo-700">
                                        {{ isset($approvalTime) ? \Carbon\Carbon::parse($approvalTime)->translatedFormat('d F Y, H:i') : '-' }} WIB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Linked Letter Info -->
                        <div class="space-y-4">
                            <h2 class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-link text-slate-400"></i> Referensi Dokumen
                            </h2>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500 font-bold uppercase tracking-tighter text-[10px]">Nomor</span>
                                    <span class="font-black text-slate-800 tracking-tight">{{ $surat->no_surat ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500 font-bold uppercase tracking-tighter text-[10px]">Jenis</span>
                                    <span class="font-bold text-slate-700 truncate ml-4">{{ $surat->jenis->nama ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 border-t border-slate-100 p-5 text-center">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Jurusan Proteksi Tanaman</p>
                        <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-tight">Fakultas Pertanian Universitas Lampung</p>
                    </div>
                </div>
            </div>

            <!-- Content Area: Document Context -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-700 to-slate-800 p-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base leading-none">Konten Ditandatangani</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest">Document Context</p>
                            </div>
                        </div>
                        @php
                            $filePath = $surat->generated_file_path ? 'uploads/' . $surat->generated_file_path : ($surat->uploaded_pdf_path ? 'uploads/' . $surat->uploaded_pdf_path : null);
                            $fileUrl = $filePath ? url($filePath) : '#';
                        @endphp
                        @if($filePath)
                        <a href="{{ $fileUrl }}" target="_blank" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-xs font-black flex items-center gap-2 transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/20">
                            <i class="fas fa-eye"></i> BUKA FULLSCREEN
                        </a>
                        @endif
                    </div>

                    <div class="bg-slate-100 h-[85vh] relative overflow-auto scrollbar-thin scrollbar-thumb-slate-300">
                        @if($filePath)
                            <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0" class="w-full h-full" frameborder="0"></iframe>
                        @elseif(isset($previewHtml) && $previewHtml)
                            <div class="p-8 md:p-12 flex justify-center origin-top-center">
                                <div class="bg-white shadow-2xl scale-[0.6] sm:scale-75 md:scale-90 lg:scale-100 transition-all duration-500">
                                    {!! $previewHtml !!}
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 p-12 text-center">
                                <div>
                                    <i class="fas fa-file-circle-exclamation text-5xl mb-4 opacity-20"></i>
                                    <h4 class="font-bold text-slate-600 text-lg">Pratinjau Tidak Tersedia</h4>
                                    <p class="text-xs italic">Konteks berkas digital tidak dapat dimuat saat ini.</p>
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
