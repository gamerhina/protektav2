<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeminarSignature;
use App\Models\Seminar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;


class SignatureController extends Controller
{
    /**
     * Show the signature canvas for a specific seminar and evaluator type
     */
    public function showSignatureForm($seminarId, $evaluatorType)
    {
        $seminar = Seminar::with(['p1Dosen', 'p2Dosen', 'pembahasDosen'])->findOrFail($seminarId);

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat ditandatangani.');
        }

        // Check if the authenticated dosen is the one who should sign
        $isEvaluator = false;
        $evaluatorName = '';

        if (Auth::guard('dosen')->check()) {
            $dosen = Auth::guard('dosen')->user();

            if ($evaluatorType === 'p1' && $dosen->id == $seminar->p1_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->p1Dosen->nama;
            } elseif ($evaluatorType === 'p2' && $dosen->id == $seminar->p2_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->p2Dosen->nama;
            } elseif ($evaluatorType === 'pembahas' && $dosen->id == $seminar->pembahas_dosen_id) {
                $isEvaluator = true;
                $evaluatorName = $seminar->pembahasDosen->nama;
            }
        }

        if (!$isEvaluator) {
            abort(403, 'Unauthorized to sign this document');
        }

        // Determine signature method from template
        $signatureMethod = 'qr_code'; // Default
        
        $template = $seminar->seminarJenis->documentTemplates()->where('aktif', true)->first();
        if ($template) {
            $signatureMethod = $template->signature_method;
        }

        // Debug logging
        \Log::info('SignatureController Debug', [
            'seminar_id' => $seminar->id,
            'seminar_jenis_id' => $seminar->seminar_jenis_id,
            'template_found' => $template ? true : false,
            'template_id' => $template ? $template->id : null,
            'signature_method' => $signatureMethod,
            'all_templates_count' => $seminar->seminarJenis->documentTemplates()->count()
        ]);

        // Check for existing signature
        $existingSignature = $seminar->signatures()
            ->where('dosen_id', $dosen->id)
            ->where('jenis_penilai', $evaluatorType)
            ->first();
            
        return view('dosen.signature.create', compact('seminar', 'evaluatorType', 'evaluatorName', 'signatureMethod', 'existingSignature'));
    }

    /**
     * Store the signature
     */
    public function storeSignature(Request $request, $seminarId, $evaluatorType)
    {
        $request->validate([
            'signature_type' => 'required|in:manual,qr_code',
            'signature' => 'required_if:signature_type,manual|string|nullable',
            'qr_agreement' => 'required_if:signature_type,qr_code|accepted',
        ]);

        $seminar = Seminar::findOrFail($seminarId);

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat ditandatangani.');
        }

        // Check if the authenticated dosen is the one who should sign
        $dosen = Auth::guard('dosen')->user();
        $isEvaluator = false;

        if ($evaluatorType === 'p1' && $dosen->id == $seminar->p1_dosen_id) {
            $isEvaluator = true;
        } elseif ($evaluatorType === 'p2' && $dosen->id == $seminar->p2_dosen_id) {
            $isEvaluator = true;
        } elseif ($evaluatorType === 'pembahas' && $dosen->id == $seminar->pembahas_dosen_id) {
            $isEvaluator = true;
        }

        if (!$isEvaluator) {
            abort(403, 'Unauthorized to sign this document');
        }

        $signatureFileName = null;
        $qrCodePath = null;
        $token = null;

        if ($request->signature_type === 'qr_code') {
            // Generate Token
            $token = \Illuminate\Support\Str::uuid()->toString();

            // Generate QR Code content (Verification URL - Signed for security)
            $url = URL::signedRoute('verify.seminar.signature', [
                'seminarId' => $seminar->id, 
                'type' => $evaluatorType,
                'token' => $token
            ]);

            // Shorten URL for cleaner QR
            $url = app(\App\Services\UrlShortenerService::class)->shorten($url);

            // Generate QR Image
            $options = new \chillerlan\QRCode\Options\QRCodeOptions([
                'version'      => \chillerlan\QRCode\QRCode::VERSION_AUTO,
                'outputType'   => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => \chillerlan\QRCode\QRCode::ECC_L,
                'scale'        => 10,
                'imageBase64'  => true,
            ]);

            $qrcode = (new \chillerlan\QRCode\QRCode($options))->render($url);

            // Process base64 image
            $signatureImage = preg_replace('#^data:image/[^;]+;base64,#i', '', $qrcode);
            $signatureFileName = 'signatures/seminar-' . $seminar->id . '-' . $evaluatorType . '-' . time() . '-qr.png';
            
            Storage::disk('uploads')->put($signatureFileName, base64_decode($signatureImage));
            $qrCodePath = $signatureFileName;

        } else {
            // Manual Signature Logic
            $signatureImage = (string) $request->signature;

            if (str_starts_with($signatureImage, 'data:image/')) {
                $signatureImage = preg_replace('#^data:image/[^;]+;base64,#i', '', $signatureImage);
                $signatureImage = str_replace(' ', '+', $signatureImage);

                $signatureFileName = 'signatures/seminar-' . $seminar->id . '-' . $evaluatorType . '-' . time() . '.png';
                Storage::disk('uploads')->put($signatureFileName, base64_decode($signatureImage));
            } else {
                $signatureFileName = $signatureImage;
            }
        }

        // Check if signature already exists for this seminar + dosen + jenis_penilai
        $existingSignature = $seminar->signatures()
            ->where('dosen_id', $dosen->id)
            ->where('jenis_penilai', $evaluatorType)
            ->first();

        if ($existingSignature) {
            // Delete old file if we are switching to a new stored file path
            if ($existingSignature->tanda_tangan && $existingSignature->tanda_tangan !== $signatureFileName) {
                Storage::disk('uploads')->delete($existingSignature->tanda_tangan);
            }

            $currentData = [
                'tanda_tangan' => $signatureFileName,
                'tanggal_ttd' => now(),
                'signature_type' => $request->signature_type,
            ];

            if ($token) {
                $currentData['verification_token'] = $token;
                $currentData['qr_code_path'] = $qrCodePath;
            }

            $existingSignature->update($currentData);
        } else {
            SeminarSignature::create([
                'seminar_id' => $seminar->id,
                'dosen_id' => $dosen->id,
                'jenis_penilai' => $evaluatorType,
                'tanda_tangan' => $signatureFileName,
                'tanggal_ttd' => now(),
                'signature_type' => $request->signature_type,
                'verification_token' => $token,
                'qr_code_path' => $qrCodePath,
            ]);
        }

        // Update seminar status based on evaluator completion
        $seminar->refreshCompletionStatus();

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }

    /**
     * Get signature for a specific seminar and evaluator
     */
    public function getSignature($seminarId, $evaluatorType)
    {
        $signature = SeminarSignature::where('seminar_id', $seminarId)
            ->where('jenis_penilai', $evaluatorType)
            ->with('dosen')
            ->first();

        if (!$signature) {
            return response()->json(['signature' => null]);
        }

        return response()->json([
            'signature' => $signature->tanda_tangan,
            'dosen_name' => $signature->dosen->nama ?? 'Unknown',
            'date' => $signature->tanggal_ttd ? $signature->tanggal_ttd->translatedFormat('d F Y') : null
        ]);
    }
}
