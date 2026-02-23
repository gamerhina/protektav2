<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Surat</title>
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
            <!-- Sidebar: Verification Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden sticky top-8">
                    <!-- Header with Logo -->
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6 text-white text-center">
                        <div class="w-20 h-20 bg-white p-2 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <img src="{{ asset('assets/images/unila-logo.png') }}" alt="Logo Universitas Lampung" class="w-full h-full object-contain">
                        </div>
                        <h1 class="text-2xl font-bold uppercase tracking-tight">Terverifikasi</h1>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <!-- Surat Details -->
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-indigo-500"></i> Informasi Dokumen
                            </h2>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Nomor Surat</p>
                                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ $surat->no_surat ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Jenis Surat</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $surat->jenis->nama ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Tanggal Terbit</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $surat->tanggal_surat ? \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') : '-' }}</p>
                                </div>
                                @if($surat->pemohon)
                                <div>
                                    <p class="text-[9px] text-slate-400 uppercase font-black">Nama Pemohon</p>
                                    <p class="text-sm font-semibold text-slate-700 truncate">{{ $surat->pemohon->nama ?? '-' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Signatures List -->
                        @if($surat->approvals && $surat->approvals->where('status', 'approved')->count() > 0)
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-signature text-indigo-500"></i> Tanda Tangan
                            </h2>
                            <div class="space-y-3">
                                @foreach($surat->approvals->where('status', 'approved') as $app)
                                <div class="bg-white border border-slate-200 rounded-xl p-3 flex items-start gap-3 shadow-sm group">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-100 transition-colors">
                                        <i class="fas fa-user-check text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate">{{ $app->dosen->nama }}</p>
                                        <p class="text-[9px] text-slate-500 font-medium">{{ $app->role_nama ?: ($app->role->nama ?? 'Penyetuju') }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Scan Footer -->
                        <div class="text-center space-y-2">
                             <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-tighter shadow-sm">
                                <i class="fas fa-shield-check"></i> Dokumen Valid & Terdaftar
                            </div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                                {{ now()->translatedFormat('d F Y, H:i') }} WIB
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

            <!-- Content Area: Document Preview -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-700 to-slate-800 p-5 text-white flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm sm:text-base leading-none">Pratinjau Dokumen</h3>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest">Digital Archive Access</p>
                            </div>
                        </div>
                        @php
                            $filePath = $surat->generated_file_path ? 'uploads/' . $surat->generated_file_path : ($surat->uploaded_pdf_path ? 'uploads/' . $surat->uploaded_pdf_path : null);
                            $fileUrl = $filePath ? url($filePath) : '#';
                        @endphp
                        @if($filePath)
                        <a href="{{ $fileUrl }}" target="_blank" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-xs font-black flex items-center gap-2 transition-all transform hover:-translate-y-0.5 shadow-lg shadow-indigo-600/20">
                            <i class="fas fa-download"></i> UNDUH PDF
                        </a>
                        @endif
                    </div>

                    <div class="bg-slate-100 h-[85vh] relative overflow-auto scrollbar-thin scrollbar-thumb-slate-300">
                        @if($surat->status === 'ditolak')
                            <div class="flex items-center justify-center h-full bg-red-50 p-12 text-center">
                                <div class="max-w-md">
                                    <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <i class="fas fa-ban text-3xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-red-800 mb-2">Akses Dokumen Ditutup</h4>
                                    <p class="text-sm text-red-600 italic">Mohon maaf, dokumen ini tidak dapat diakses atau diunduh karena permohonan telah ditolak oleh pimpinan.</p>
                                </div>
                            </div>
                        @elseif($filePath)
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
                                    <h4 class="font-bold text-slate-600">Konten Tidak Tersedia</h4>
                                    <p class="text-xs italic">File pratinjau tidak ditemukan dalam sistem.</p>
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
