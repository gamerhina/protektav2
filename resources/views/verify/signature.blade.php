<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Tanda Tangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-slate-200">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-lg mx-auto">
            @if($valid)
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Success Header -->
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-6 text-center">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-4xl text-white"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-white">Tanda Tangan Valid</h1>
                        <p class="text-emerald-100 text-sm mt-1">{{ $message }}</p>
                    </div>

                    <!-- Signature Details -->
                    <div class="p-8 space-y-6">
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
                                <i class="fas fa-signature mr-2"></i> Informasi Penanda Tangan
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="text-xs text-slate-500">Nama Penanda Tangan</div>
                                    <div class="text-lg font-bold text-slate-800">{{ $signature->signer_name }}</div>
                                </div>

                                @if($signature->signer_nip)
                                <div>
                                    <div class="text-xs text-slate-500">NIP</div>
                                    <div class="text-lg font-semibold text-slate-700 font-mono">{{ $signature->signer_nip }}</div>
                                </div>
                                @endif

                                <div>
                                    <div class="text-xs text-slate-500">Jabatan/Peran</div>
                                    <div class="text-base font-semibold text-slate-700">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                            {{ $signature->signer_type_label }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="text-xs text-slate-500">Tanggal Penandatanganan</div>
                                    <div class="text-base font-semibold text-slate-700">
                                        <i class="far fa-calendar-alt mr-2 text-slate-400"></i>
                                        {{ $signature->signed_at?->translatedFormat('l, d F Y') ?? '-' }}
                                    </div>
                                    <div class="text-sm text-slate-500 ml-6">
                                        {{ $signature->signed_at?->translatedFormat('H:i') ?? '' }} WIB
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Info -->
                        @if($surat)
                        <div class="bg-blue-50 rounded-2xl p-6 border border-blue-200">
                            <h2 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4">
                                <i class="fas fa-file-alt mr-2"></i> Dokumen Terkait
                            </h2>
                            
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-blue-500">Jenis Surat</div>
                                    <div class="text-base font-semibold text-blue-800">{{ $surat->jenis?->nama ?? '-' }}</div>
                                </div>

                                @if($surat->no_surat)
                                <div>
                                    <div class="text-xs text-blue-500">Nomor Surat</div>
                                    <div class="text-base font-mono font-semibold text-blue-800">{{ $surat->no_surat }}</div>
                                </div>
                                @endif

                                <div>
                                    <div class="text-xs text-blue-500">Tanggal Surat</div>
                                    <div class="text-base font-semibold text-blue-800">{{ $surat->tanggal_surat?->translatedFormat('d F Y') ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Verification Badge -->
                        <div class="text-center pt-4">
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-full text-emerald-700 text-sm font-medium">
                                <i class="fas fa-shield-alt"></i>
                                Diverifikasi oleh Sistem Protekta
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Error Header -->
                    <div class="bg-gradient-to-r from-red-500 to-rose-500 px-8 py-6 text-center">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-times-circle text-4xl text-white"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-white">Tidak Valid</h1>
                        <p class="text-red-100 text-sm mt-1">{{ $message }}</p>
                    </div>

                    <div class="p-8 text-center">
                        <p class="text-slate-600 mb-6">
                            Kode QR yang Anda scan tidak ditemukan dalam sistem. 
                            Dokumen mungkin tidak asli atau telah diubah.
                        </p>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-left">
                            <h3 class="font-bold text-amber-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Perhatian
                            </h3>
                            <p class="text-sm text-amber-700">
                                Jika Anda yakin dokumen ini seharusnya valid, 
                                silakan hubungi administrator sistem.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Footer -->
            <div class="text-center mt-8 text-slate-500 text-sm">
                <p>&copy; {{ date('Y') }} Sistem Protekta</p>
            </div>
        </div>
    </div>
</body>
</html>
