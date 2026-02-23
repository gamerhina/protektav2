<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Models\AssessmentAspect;
use App\Models\AssessmentScore;
use App\Helpers\Terbilang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\UrlShortenerService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class NilaiController extends Controller
{
    public function showInputForm(Seminar $seminar)
    {
        if (!Auth::guard('dosen')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $dosen = Auth::guard('dosen')->user();

        $evaluatorType = null;
        if ($seminar->p1_dosen_id == $dosen->id) {
            $evaluatorType = 'p1';
        } elseif ($seminar->p2_dosen_id == $dosen->id) {
            $evaluatorType = 'p2';
        } elseif ($seminar->pembahas_dosen_id == $dosen->id) {
            $evaluatorType = 'pembahas';
        } else {
            abort(403, 'Anda tidak memiliki akses untuk menilai seminar ini.');
        }

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat dinilai.');
        }

        $existingNilai = SeminarNilai::where('seminar_id', $seminar->id)
            ->where('dosen_id', $dosen->id)
            ->first();

        $aspects = AssessmentAspect::where('seminar_jenis_id', $seminar->seminar_jenis_id)
            ->where('evaluator_type', $evaluatorType)
            ->orderBy('urutan')
            ->get();

        $existingScores = [];
        if ($existingNilai) {
            $existingScores = $existingNilai->assessmentScores()
                ->pluck('nilai', 'assessment_aspect_id')
                ->toArray();
        }

        // Get existing signature
        $existingSignature = $seminar->signatures()
            ->where('dosen_id', $dosen->id)
            ->where('jenis_penilai', $evaluatorType)
            ->first();

        return view('dosen.nilai.input', compact('seminar', 'existingNilai', 'aspects', 'existingScores', 'evaluatorType', 'existingSignature'));
    }

    public function storeNilai(Request $request, Seminar $seminar)
    {
        if (!Auth::guard('dosen')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $dosen = Auth::guard('dosen')->user();

        $evaluatorType = null;
        if ($seminar->p1_dosen_id == $dosen->id) {
            $evaluatorType = 'p1';
        } elseif ($seminar->p2_dosen_id == $dosen->id) {
            $evaluatorType = 'p2';
        } elseif ($seminar->pembahas_dosen_id == $dosen->id) {
            $evaluatorType = 'pembahas';
        } else {
            abort(403, 'Anda tidak memiliki akses untuk menilai seminar ini.');
        }

        if (!in_array($seminar->status, ['disetujui', 'belum_lengkap', 'selesai'], true)) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Seminar ini belum disetujui sehingga belum dapat dinilai.');
        }

        $aspects = AssessmentAspect::where('seminar_jenis_id', $seminar->seminar_jenis_id)
            ->where('evaluator_type', $evaluatorType)
            ->get();

        $validationRules = [
            'catatan' => 'nullable|string|max:1000',
            'signature' => 'nullable|string',
        ];

        foreach ($aspects as $aspect) {
            if ($aspect->type === 'input') {
                $validationRules['aspect_' . $aspect->id] = 'required|numeric|min:0|max:100';
            }
        }

        $request->validate($validationRules);

        DB::transaction(function () use ($request, $seminar, $dosen, $evaluatorType, $aspects) {
            $nilai = SeminarNilai::firstOrNew([
                'seminar_id' => $seminar->id,
                'dosen_id' => $dosen->id,
            ]);

            if (!$nilai->exists) {
                $nilai->jenis_penilai = $evaluatorType;
                $nilai->nilai_angka = 0; // Initialize with 0 to pass non-null constraint
            }

            $nilai->catatan = $request->catatan;
            $nilai->save();

            foreach ($aspects as $aspect) {
                if ($aspect->type === 'input') {
                    AssessmentScore::updateOrCreate(
                        [
                            'seminar_nilai_id' => $nilai->id,
                            'assessment_aspect_id' => $aspect->id,
                        ],
                        [
                            'nilai' => $request->input('aspect_' . $aspect->id),
                        ]
                    );
                }
            }

            // Recalculate final score based on stored aspect scores
            $nilai->refresh();
            $nilai->nilai_angka = $nilai->calculateFinalScore();
            $nilai->save();

        // Handle Signatures
        if ($request->boolean('qr_agreement')) {
            // QR Code Signature Mode
            $verificationToken = Str::uuid()->toString();
            $originalUrl = \Illuminate\Support\Facades\URL::signedRoute('verify.seminar.signature', [
                            'seminarId' => $seminar->id,
                            'type' => $evaluatorType,
                            'token' => $verificationToken
                        ]);

            // Shorten URL
            $shortener = new UrlShortenerService();
            $qrCodeData = $shortener->shorten($originalUrl);
            
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'outputBase64' => false,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 10,
                'imageTransparent' => false,
            ]);
            
            $qrcode = new QRCode($options);
            $qrBinary = $qrcode->render($qrCodeData);
            
            $qrFileName = 'signatures/qr-' . $seminar->id . '-' . $evaluatorType . '-' . time() . '.png';
            Storage::disk('uploads')->put($qrFileName, $qrBinary);
            
            // Get or create signature record
            $existingSignature = $seminar->signatures()
                ->where('dosen_id', $dosen->id)
                ->where('jenis_penilai', $evaluatorType)
                ->first();
                
            if ($existingSignature) {
                // Delete old files
                if ($existingSignature->qr_code_path) {
                    Storage::disk('uploads')->delete($existingSignature->qr_code_path);
                }
                if ($existingSignature->tanda_tangan) {
                    Storage::disk('uploads')->delete($existingSignature->tanda_tangan);
                }
                
                $existingSignature->update([
                    'signature_type' => 'qr_code',
                    'qr_code_path' => $qrFileName,
                    'verification_token' => $verificationToken,
                    'tanda_tangan' => '', // Set empty string as it's not nullable in DB
                    'tanggal_ttd' => now(),
                ]);
            } else {
                $seminar->signatures()->create([
                    'dosen_id' => $dosen->id,
                    'jenis_penilai' => $evaluatorType,
                    'signature_type' => 'qr_code',
                    'qr_code_path' => $qrFileName,
                    'verification_token' => $verificationToken,
                    'tanda_tangan' => '', // Set empty string as it's not nullable in DB
                    'tanggal_ttd' => now(),
                ]);
            }
        } elseif ($request->filled('signature')) {
            // Process base64 image (Manual Canvas)
            $signatureImage = $request->signature;
            $signatureImage = str_replace('data:image/png;base64', '', $signatureImage);
            $signatureImage = str_replace(' ', '+', $signatureImage);
            $signatureFileName = 'signatures/seminar-' . $seminar->id . '-' . $evaluatorType . '-' . time() . '.png';
                
            Storage::disk('uploads')->put($signatureFileName, base64_decode($signatureImage));
                
            // Check if signature record already exists
            $existingSignature = $seminar->signatures()
                ->where('dosen_id', $dosen->id)
                ->where('jenis_penilai', $evaluatorType)
                ->first();
                    
            if ($existingSignature) {
                // Delete old file
                if ($existingSignature->tanda_tangan) {
                    Storage::disk('uploads')->delete($existingSignature->tanda_tangan);
                }
                    
                $existingSignature->update([
                    'signature_type' => 'manual',
                    'tanda_tangan' => $signatureFileName,
                    'qr_code_path' => null, // Clear QR code path
                    'verification_token' => null, // Clear QR verification token
                    'tanggal_ttd' => now(),
                ]);
            } else {
                // Create new signature record
                $seminar->signatures()->create([
                    'dosen_id' => $dosen->id,
                    'jenis_penilai' => $evaluatorType,
                    'signature_type' => 'manual',
                    'tanda_tangan' => $signatureFileName,
                    'tanggal_ttd' => now(),
                ]);
            }
        }
        });

        // Update seminar status based on evaluator completion
        $seminar->refreshCompletionStatus();

        return redirect()->back()->with('success', 'Nilai seminar berhasil disimpan.');
    }
}
