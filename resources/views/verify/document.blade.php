<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-slate-200">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            @if($valid)
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Success Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6 text-center">
                        <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-shield text-4xl text-white"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-white">Dokumen Asli</h1>
                        <p class="text-blue-100 text-sm mt-1">{{ $message }}</p>
                    </div>

                    <!-- Document Details -->
                    <div class="p-8 space-y-6">
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">
                                <i class="fas fa-file-alt mr-2"></i> Informasi Dokumen
                            </h2>
                            
                            <div class="space-y-4">
                                @if($type === 'surat')
                                    <div>
                                        <div class="text-xs text-slate-500">Jenis Dokumen</div>
                                        <div class="text-lg font-bold text-slate-800">Surat - {{ $document->jenis?->nama ?? 'Umum' }}</div>
                                    </div>

                                    @if($document->no_surat)
                                    <div>
                                        <div class="text-xs text-slate-500">Nomor Surat</div>
                                        <div class="text-lg font-mono font-semibold text-slate-700">{{ $document->no_surat }}</div>
                                    </div>
                                    @endif

                                    <div>
                                        <div class="text-xs text-slate-500">Tanggal Surat</div>
                                        <div class="text-base font-semibold text-slate-700">
                                            <i class="far fa-calendar-alt mr-2 text-slate-400"></i>
                                            {{ $document->tanggal_surat?->translatedFormat('d F Y') ?? '-' }}
                                        </div>
                                    </div>

                                    @if($document->perihal)
                                    <div>
                                        <div class="text-xs text-slate-500">Perihal</div>
                                        <div class="text-base text-slate-700">{{ $document->perihal }}</div>
                                    </div>
                                    @endif

                                    <div>
                                        <div class="text-xs text-slate-500">Status</div>
                                        <div class="text-base">
                                            @php
                                                $statusColors = [
                                                    'diajukan' => 'bg-yellow-100 text-yellow-800',
                                                    'diproses' => 'bg-blue-100 text-blue-800',
                                                    'dikirim' => 'bg-green-100 text-green-800',
                                                    'ditolak' => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$document->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @elseif($type === 'seminar')
                                    <div>
                                        <div class="text-xs text-slate-500">Jenis Dokumen</div>
                                        <div class="text-lg font-bold text-slate-800">Dokumen Seminar {{ $document->seminarJenis?->nama ?? '' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs text-slate-500">Judul</div>
                                        <div class="text-base font-semibold text-slate-700">{{ $document->judul }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs text-slate-500">Mahasiswa</div>
                                        <div class="text-base font-semibold text-slate-700">{{ $document->mahasiswa?->nama ?? '-' }}</div>
                                        <div class="text-sm text-slate-500">{{ $document->mahasiswa?->npm ?? '' }}</div>
                                    </div>

                                    @if($document->tanggal)
                                    <div>
                                        <div class="text-xs text-slate-500">Tanggal Seminar</div>
                                        <div class="text-base font-semibold text-slate-700">
                                            <i class="far fa-calendar-alt mr-2 text-slate-400"></i>
                                            {{ $document->tanggal?->translatedFormat('d F Y') ?? '-' }}
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Signatures -->
                        @if($signatures && $signatures->count() > 0)
                        <div class="bg-emerald-50 rounded-2xl p-6 border border-emerald-200">
                            <h2 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4">
                                <i class="fas fa-signature mr-2"></i> Tanda Tangan Digital
                            </h2>
                            
                            <div class="space-y-3">
                                @foreach($signatures as $sig)
                                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-emerald-100">
                                    <div>
                                        <div class="font-semibold text-slate-800">
                                            {{ $type === 'surat' ? ($sig->signer_name ?? 'Penanda Tangan') : ($sig->dosen?->nama ?? 'Dosen') }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ $type === 'surat' ? ($sig->signer_type_label ?? $sig->signer_type) : ucfirst($sig->jenis_penilai) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-emerald-600">
                                            <i class="fas fa-check-circle mr-1"></i> Valid
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $type === 'surat' ? ($sig->signed_at?->translatedFormat('d/m/Y') ?? '-') : ($sig->tanggal_ttd?->translatedFormat('d/m/Y') ?? '-') }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- View PDF Button -->
                        <div class="text-center pt-4">
                            <a href="{{ url('/verify/document/' . ($document->qr_verification_code ?? '') . '/preview') }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                                <i class="fas fa-file-pdf"></i>
                                Lihat Dokumen PDF
                            </a>
                        </div>

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
                        <h1 class="text-2xl font-bold text-white">Dokumen Tidak Valid</h1>
                        <p class="text-red-100 text-sm mt-1">{{ $message }}</p>
                    </div>

                    <div class="p-8 text-center">
                        <p class="text-slate-600 mb-6">
                            Kode QR yang Anda scan tidak ditemukan dalam sistem. 
                            Dokumen mungkin tidak asli atau kode verifikasi tidak valid.
                        </p>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-left">
                            <h3 class="font-bold text-amber-800 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Perhatian
                            </h3>
                            <ul class="text-sm text-amber-700 list-disc list-inside space-y-1">
                                <li>Pastikan QR code tidak rusak atau buram</li>
                                <li>Dokumen mungkin telah dibatalkan atau dicabut</li>
                                <li>Hubungi administrator jika Anda yakin dokumen ini valid</li>
                            </ul>
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
