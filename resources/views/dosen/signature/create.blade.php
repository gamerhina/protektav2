@extends('layouts.app')

@section('title', 'E-Signature')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tanda Tangan Digital</h1>

        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-700">Informasi Seminar</h2>
            <p class="text-gray-600 font-medium mb-1">Judul:</p>
            <div class="text-gray-700 mb-2 whitespace-pre-wrap">{!! $seminar->judul !!}</div>
            <p class="text-gray-600">Tanggal: {{ $seminar->tanggal->translatedFormat('d F Y') }}</p>
            <p class="text-gray-600">Jenis: {{ $seminar->seminarJenis->nama ?? 'N/A' }}</p>
            <p class="text-gray-600">Mahasiswa: {{ $seminar->mahasiswa->nama ?? 'N/A' }}</p>
        </div>

        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Buat Tanda Tangan Anda</h2>

            {{-- Debug Information --}}
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-xs">
                <strong>DEBUG INFO:</strong><br>
                Signature Method: <code class="bg-yellow-100 px-2 py-1 rounded">{{ $signatureMethod ?? 'UNDEFINED' }}</code><br>
                Is QR Code: <code class="bg-yellow-100 px-2 py-1 rounded">{{ ($signatureMethod ?? '') === 'qr_code' ? 'YES' : 'NO' }}</code><br>
                Is Manual: <code class="bg-yellow-100 px-2 py-1 rounded">{{ ($signatureMethod ?? '') === 'manual' ? 'YES' : 'NO' }}</code>
            </div>

            <form id="signatureForm" action="{{ route('dosen.signature.store', ['seminarId' => $seminar->id, 'evaluatorType' => $evaluatorType]) }}" method="POST">
                @csrf
                
                <input type="hidden" name="signature_type" value="{{ $signatureMethod }}">

                @if($signatureMethod === 'manual')
                    <!-- Manual Signature Section -->
                    <div id="manual-signature-container" class="mb-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
                        <div class="signature-pad-wrapper-{{ $evaluatorType }}">
                            <input
                                type="hidden"
                                name="signature"
                                class="signature-input-{{ $evaluatorType }}"
                            >

                            <button
                                type="button"
                                class="toggle-signature-btn-{{ $evaluatorType }} btn-pill btn-pill-info text-xs px-4 py-2 mb-2 w-full sm:w-auto justify-center"
                            >
                                <i class="fas fa-signature mr-2"></i>
                                Buat / Ubah Tanda Tangan
                            </button>

                            <div class="signature-pad-container-{{ $evaluatorType }} hidden">
                                <canvas
                                    width="360"
                                    height="120"
                                    style="touch-action: none !important;"
                                    class="signature-canvas-{{ $evaluatorType }} border border-blue-200 rounded bg-white cursor-crosshair w-full"
                                ></canvas>
                                <button
                                    type="button"
                                    class="clear-signature-btn-{{ $evaluatorType }} text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full sm:w-auto"
                                >
                                    Bersihkan
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mt-2">
                            Gunakan mouse atau sentuhan untuk membuat tanda tangan.
                        </p>
                    </div>
                @else
                    <!-- QR Code Signature Section -->
                    <div id="qr-signature-container" class="mb-6 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-xl p-8 text-center shadow-sm">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white text-indigo-600 mb-6 shadow-sm ring-4 ring-indigo-50">
                            <i class="fas fa-qrcode text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Tanda Tangan Digital QR Code</h3>
                        <p class="text-sm text-gray-600 max-w-md mx-auto mb-8">
                            Dokumen ini menggunakan sistem verifikasi QR Code. Dengan mencentang kotak di bawah ini, Anda menyetujui isi dokumen dan membuat tanda tangan digital yang sah secara sistem.
                        </p>
                        
                        <div class="flex flex-col items-center">
                            <label class="flex items-start gap-4 cursor-pointer max-w-lg text-left p-4 rounded-xl border-2 border-dashed border-indigo-200 hover:bg-white hover:border-indigo-400 transition-all group select-none">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="qr_agreement" value="1" id="qr_agreement_check" class="peer h-6 w-6 cursor-pointer appearance-none rounded-md border-2 border-slate-300 bg-white shadow-sm transition-all checked:border-indigo-600 checked:bg-indigo-600 focus:ring-0 focus:ring-offset-0">
                                    <i class="fas fa-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white text-xs opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                        {{ $existingSignature && $existingSignature->signature_type === 'qr_code' ? 'Update Tanda Tangan QR' : 'Saya menyetujui dan menandatangani dokumen ini.' }}
                                    </span>
                                    <span class="block text-xs text-gray-500 mt-1">
                                        Tindakan ini akan menghasilkan QR Code unik yang terhubung dengan akun Anda sebagai tanda tangan sah.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif


                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
                        <i class="fas fa-save mr-2"></i>
                        {{ $existingSignature ? 'Simpan Perubahan' : 'Simpan Tanda Tangan' }}
                    </button>
                </div>
            </form>
        </div>

        <div>
            <h2 class="text-lg font-medium text-gray-700 mb-4">Petunjuk Penggunaan</h2>
            <ul class="list-disc pl-5 space-y-2 text-gray-600">
                <li>Gambar tanda tangan dengan mouse atau layar sentuh di atas kotak yang disediakan</li>
                <li>Gunakan tombol "Bersihkan" untuk menghapus tanda tangan dan menggambar ulang</li>
                <li>Klik "Simpan Tanda Tangan" setelah selesai</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/signature-pad.js')
    <script>
        (function() {
            function initSignatureValidation() {
                const form = document.getElementById('signatureForm');
                if (!form) return;

                if (form.dataset.initialized === 'true') return;

                form.addEventListener('submit', function (e) {
                    const signatureType = form.querySelector('input[name="signature_type"]').value;

                    if (signatureType === 'manual') {
                        // Check if canvas has signature
                        const signatureInput = form.querySelector('input[name="signature"]');
                        if (!signatureInput || !signatureInput.value) {
                            e.preventDefault();
                            alert('Silakan buat tanda tangan terlebih dahulu.');
                        }
                    } else if (signatureType === 'qr_code') {
                        // Check if agreement checkbox is checked
                        const agreementCheck = document.getElementById('qr_agreement_check');
                        if (!agreementCheck || !agreementCheck.checked) {
                            e.preventDefault();
                            alert('Anda harus menyetujui dokumen untuk menandatangani.');
                        }
                    }
                });

                form.dataset.initialized = 'true';
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initSignatureValidation();
            } else {
                document.addEventListener('DOMContentLoaded', initSignatureValidation);
            }
            window.addEventListener('page-loaded', initSignatureValidation);
        })();
    </script>
@endsection
