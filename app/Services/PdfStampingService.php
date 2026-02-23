<?php

namespace App\Services;

use App\Models\SuratApproval;
use App\Models\Surat;
use Mpdf\Mpdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


class PdfStampingService
{
    /**
     * Process the stamping for a specific approval.
     */
    public function process(SuratApproval $approval)
    {
        try {
            $surat = $approval->surat;
            $originalPath = Storage::disk('uploads')->path($surat->uploaded_pdf_path);
            
            if (!file_exists($originalPath)) {
                throw new \Exception("Original PDF file not found at: " . $originalPath);
            }

            // Always start from the original file to avoid double-stamping the same approver
            // and to allow re-positioning.
            $basePdfPath = $originalPath;

            // Initialize Mpdf with Import support & Custom Fonts
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'tempDir' => storage_path('app/mpdf'),
                'format' => 'A4',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts'),
                ]),
                'fontdata' => $fontData + [
                    'greatvibes' => [
                        'R' => 'GreatVibes-Regular.ttf',
                        'I' => 'GreatVibes-Regular.ttf', 
                        'B' => 'GreatVibes-Regular.ttf',
                        'BI' => 'GreatVibes-Regular.ttf',
                    ],
                ],
                'default_font' => 'helvetica'
            ]);

            // Disable auto page break to prevent stamps placed at the bottom from jumping to a new page
            $mpdf->SetAutoPageBreak(false);

            // Set source file
            $pageCount = $mpdf->setSourceFile($basePdfPath);
            
            // Get all approvals that should be stamped
            // We include the current one (it might be newly approved or being re-stamped)
            $approvalsToStamp = $surat->approvals()
                ->where('is_stamped', true)
                ->get();

            // Loop through all pages
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $mpdf->importPage($i);
                $size = $mpdf->getTemplateSize($templateId);

                // Get page dimensions in mm
                $w = isset($size['width']) ? (float)$size['width'] : (isset($size['w']) ? (float)$size['w'] : 210);
                $h = isset($size['height']) ? (float)$size['height'] : (isset($size['h']) ? (float)$size['h'] : 297);
                
                // Detect orientation
                $orientation = ($w > $h) ? 'L' : 'P';

                // Log for debugging
                Log::info('Importing page', ['page' => $i, 'width' => $w, 'height' => $h, 'orientation' => $orientation]);

                // Add page with custom dimensions using array format to avoid type errors in AddPage
                // This ensures the page size matches the template exactly
                $mpdf->AddPageByArray([
                    'orientation' => $orientation,
                    'newformat' => [$w, $h]
                ]);
                
                // Use template at 0,0 with explicit dimensions to fill the page
                $mpdf->useTemplate($templateId, 0, 0, $w, $h);

                // Overlay signatures for all relevant approvals on this page
                foreach ($approvalsToStamp as $app) {
                    // Call overlay for every page, let the method decide what to render based on page number
                    $this->overlaySignature($mpdf, $app, $size, $i);
                }
            }

            // Save the new file
            $fileName = 'documents/surat/stamped/STAMP_' . time() . '_' . basename($originalPath);
            $newPath = Storage::disk('uploads')->path($fileName);
            
            // Ensure directory exists
            if (!file_exists(dirname($newPath))) {
                mkdir(dirname($newPath), 0755, true);
            }

            $mpdf->Output($newPath, \Mpdf\Output\Destination::FILE);

            // Update Surat
            $updateData = ['generated_file_path' => $fileName];
            if ($surat->status === 'diajukan') {
                $updateData['status'] = 'diproses';
            }
            $surat->update($updateData);

            return $fileName;
        } catch (\Exception $e) {
            Log::error('PDF Stamping Failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Overlay the signature on the current page.
     */
    private function overlaySignature(Mpdf $mpdf, SuratApproval $approval, array $pageSize, int $currentPage)
    {
        // Get page dimensions from template (in mm)
        $templateWidth = isset($pageSize['width']) ? (float)$pageSize['width'] : (isset($pageSize['w']) ? (float)$pageSize['w'] : 210);
        $templateHeight = isset($pageSize['height']) ? (float)$pageSize['height'] : (isset($pageSize['h']) ? (float)$pageSize['h'] : 297);
        
        // Frontend coordinates are in PDF.js canvas pixels at scale 1.3
        // PDF.js works in points (1/72 inch), then scales for canvas
        // 1 point = 0.352778 mm
        // Frontend stores: canvasPixels = pdfPoints * scale
        // To get mm: mm = (canvasPixels / scale) * 0.352778
        
        $scaleFactor = 1.3; // Same as frontend
        $pointsToMm = 0.352778; // 1 point = 0.352778 mm
        
        $convertX = function($val, $max = null) use ($pointsToMm, $scaleFactor) {
            if ($val === null) return 0;
            $result = ($val / $scaleFactor) * $pointsToMm;
            $result = is_finite($result) ? $result : 0;
            if ($max !== null && $result > $max) return $max;
            return max(0, $result);
        };
        
        $convertY = function($val, $max = null) use ($pointsToMm, $scaleFactor) {
            if ($val === null) return 0;
            $result = ($val / $scaleFactor) * $pointsToMm;
            $result = is_finite($result) ? $result : 0;
            if ($max !== null && $result > $max) return $max;
            return max(0, $result);
        };

        // --- 1. Render Main QR Code ---
        // Calculate dimensions first
        $width_db = $approval->stamp_width;
        $height_db = $approval->stamp_height;
        $width_mm = $width_db > 0 ? $convertX($width_db) : 32.5; // Default ~120px
        $height_mm = $height_db > 0 ? $convertY($height_db) : 32.5;

        // Use dimensions to clamp positions
        $x_mm = $convertX($approval->stamp_x, $templateWidth - $width_mm - 1); // 1mm safety margin
        $y_mm = $convertY($approval->stamp_y, $templateHeight - $height_mm - 1);
        
        // Ensure width and height are positive and within page
        $width_mm = min($width_mm, $templateWidth - $x_mm);
        $height_mm = min($height_mm, $templateHeight - $y_mm);
        
        // Fallback if conversion results in zero or negative
        if ($width_mm <= 0 || !is_finite($width_mm)) {
            Log::warning('Invalid QR width, using fallback', ['approval_id' => $approval->id, 'width_db' => $width_db, 'width_mm' => $width_mm]);
            $width_mm = 40;
        }
        if ($height_mm <= 0 || !is_finite($height_mm)) {
            Log::warning('Invalid QR height, using fallback', ['approval_id' => $approval->id, 'height_db' => $height_db, 'height_mm' => $height_mm]);
            $height_mm = 40;
        }
        
        // Debug logging
        Log::info('Rendering stamp', [
            'page' => $currentPage,
            'stamp_page' => $approval->stamp_page,
            'x_px' => $approval->stamp_x, 'y_px' => $approval->stamp_y,
            'clamped_x_mm' => round($x_mm, 2), 'clamped_y_mm' => round($y_mm, 2),
            'width_mm' => round($width_mm, 2), 'height_mm' => round($height_mm, 2),
            'template_size' => ['w' => $templateWidth, 'h' => $templateHeight],
            'conversion' => 'mm = (px / 1.3) * 0.352778 (Clamped to page borders)'
        ]);

        // Only render Main QR if it belongs to this page
        if ((int)$approval->stamp_page === $currentPage) {
            if ($width_mm > 1 && $height_mm > 1) { // Use > 1 to avoid floating point near-zero issues? Or > 0.
             // Generate QR Code URL
             // Use role code if available, otherwise use unique approval ID to avoid collisions
             $roleKey = $approval->role?->kode ? strtolower($approval->role->kode) : 'approval-' . $approval->id;
             $verifyUrl = URL::signedRoute('verify.surat.signature', ['suratId' => $approval->surat->id, 'type' => $roleKey]);
             $verifyUrl = app(\App\Services\UrlShortenerService::class)->shorten($verifyUrl);

             $options = new QROptions([
                 'version'      => QRCode::VERSION_AUTO,
                 'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                 'eccLevel'     => EccLevel::L,
                 'scale'        => 10,
                 'imageBase64'  => false,
                 'quietzoneSize'=> 1,
             ]);
            
             try {
                 $qrcode = (new QRCode($options))->render($verifyUrl);
                 $pngBase64 = base64_encode($qrcode);
                 $dataUri = 'data:image/png;base64,' . $pngBase64;
                 $mpdf->Image($dataUri, $x_mm, $y_mm, $width_mm, $height_mm, 'png');
             } catch (\Exception $e) {
                 Log::error("Failed to render QR for approval {$approval->id}: " . $e->getMessage());
             }
            }
        }

        // --- 2. Render Additional Stamps (Text/Tags) ---
        $additionalStamps = $approval->additional_stamps ?? [];
        if (is_string($additionalStamps)) {
            $additionalStamps = json_decode($additionalStamps, true) ?? [];
        }

        if (empty($additionalStamps)) return;

        foreach ($additionalStamps as $stamp) {
            // Only render if stamp belongs to this page
            if (empty($stamp['type']) || (int)($stamp['page'] ?? 1) !== $currentPage) continue;

            // Sanitize stamp data - ensure all fields are proper types
            // Skip this stamp entirely if it has any array values
            $hasArray = false;
            $sanitizedStamp = [];
            foreach ($stamp as $key => $value) {
                if (is_array($value)) {
                    $hasArray = true;
                    break;
                }
                $sanitizedStamp[$key] = $value;
            }
            
            if ($hasArray) {
                Log::warning('Skipping stamp with array values', ['approval_id' => $approval->id, 'stamp' => $stamp]);
                continue;
            }
            
            $stamp = $sanitizedStamp;

            // Coordinates
            $stampWidthDb = $stamp['width'] ?? 0;
            $stampHeightDb = $stamp['height'] ?? 0;

            if (($stamp['type'] ?? '') === 'qr') {
                // For QR, use actual dimensions for clamping
                $clampW = $convertX($stampWidthDb);
                $clampH = $convertY($stampHeightDb);
            } else {
                // For text/tags, use minimal clamping (1mm) so position is not shifted
                $clampW = 1;
                $clampH = 1;
            }

            $sX = (float)$convertX($stamp['x'] ?? 0, $templateWidth - $clampW);
            $sY = (float)$convertY($stamp['y'] ?? 0, $templateHeight - $clampH);

            // Handle Additional QR
            if (($stamp['type'] ?? '') === 'qr') {
                $qrWidth = (float)$convertX($stampWidthDb, $templateWidth - $sX - 1);
                $qrHeight = (float)$convertY($stampHeightDb, $templateHeight - $sY - 1);
                
                // Validate and ensure minimum size
                if ($qrWidth <= 0 || !is_finite($qrWidth)) {
                    Log::warning('Invalid additional QR width, skipping', [
                        'approval_id' => $approval->id,
                        'stamp' => $stamp
                    ]);
                    continue; // Skip this stamp
                }
                if ($qrHeight <= 0 || !is_finite($qrHeight)) {
                    Log::warning('Invalid additional QR height, skipping', [
                        'approval_id' => $approval->id,
                        'stamp' => $stamp
                    ]);
                    continue; // Skip this stamp
                }

                // Use provided text, nip, role as params - ensure strings
                $customSigner = is_string($stamp['text'] ?? '') ? $stamp['text'] : '';
                $customNip    = is_string($stamp['nip'] ?? '') ? $stamp['nip'] : '';
                $customRole   = is_string($stamp['custom_role'] ?? '') ? $stamp['custom_role'] : '';

                // Construct URL carefully
                if ($customRole === 'surat_verification') {
                    // Use text directly as URL
                    $qrData = $customSigner; 
                } else {
                    $params = [
                        'suratId' => $approval->surat->id, 
                        'type' => 'approval-' . $approval->id,
                        'signer_name' => $customSigner,
                        'signer_nip' => $customNip,
                        'signer_role' => $customRole,
                    ];
                    $qrData = URL::signedRoute('verify.surat.signature', $params);
                }
                
                $qrData = app(\App\Services\UrlShortenerService::class)->shorten($qrData);

                // Render QR
                try {
                    $extraOptions = new QROptions([
                        'version'      => QRCode::VERSION_AUTO,
                        'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                        'eccLevel'     => EccLevel::L,
                        'scale'        => 10,
                        'imageBase64'  => false,
                        'quietzoneSize'=> 1,
                    ]);
                    $qrGenerator = new QRCode($extraOptions);
                    $extraQrBinary = $qrGenerator->render($qrData);
                    
                    if ($extraQrBinary) {
                        $extraPngBase64 = base64_encode($extraQrBinary);
                        $extraDataUri = 'data:image/png;base64,' . $extraPngBase64;
                        $mpdf->Image($extraDataUri, $sX, $sY, $qrWidth, $qrHeight, 'png');
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to render extra QR for approval {$approval->id}: " . $e->getMessage());
                }
                continue; // Skip text rendering for this item
            }
            
            // Allow manual text adjustment for baseline (Text is bottom-left anchored in some libs, top-left in others)
            // mPDF Text() x,y is usually bottom-left of the first character?
            // Actually mPDF Text() coordinates are X, Y. Creating a text box is WriteCell.
            // Let's use SetXY + WriteCell for better control or Text.
            // Standard Text($x, $y, $txt) puts the baseline at $y.
            // Frontend "top" is top of the element.
            // We might need to add font-size to Y to approximate top-alignment if logic differs.
            // HTML DOM 'top' is top of the box. mPDF Text is baseline.
            // Let's adjust Y by adding font size (approx).
            
            $fontSize = isset($stamp['fontSize']) ? (int)$stamp['fontSize'] : 10;
            // 1px = 0.75pt approx (standard CSS/PDF ratio)
            $fontSizePt = $fontSize * 0.75; 
            
            // Adjust Y: $sY from frontend is top of the text box.
            // mPDF Text() needs the baseline coordinate.
            // The baseline is typically ~80% down from the top of the font height.
            // mm per pt is 0.352778.
            $fontHeightMm = $fontSizePt * 0.352778;
            $sY += ($fontHeightMm * 0.65); // Significant fix: 0.65 lifts text to match editor's visible top better.
            // Font
            $fontFamily = 'helvetica'; // Default
            $fontInput = is_string($stamp['font'] ?? 'arial') ? strtolower($stamp['font']) : 'arial';
            switch ($fontInput) {
                case 'times': 
                case 'times new roman':
                    $fontFamily = 'times'; 
                    break;
                case 'courier': 
                case 'courier new':
                    $fontFamily = 'courier'; 
                    break;
                case 'greatvibes':
                case 'great vibes':
                case 'monotypecorsiva': // Fallback for old templates
                case 'monotype corsiva':
                    $fontFamily = 'greatvibes'; 
                    break;
                case 'helvetica': 
                case 'arial': 
                case 'calibri':
                case 'tahoma':
                case 'verdana':
                case 'geneva':
                case 'impact':
                default:
                    $fontFamily = 'helvetica'; 
                    break;
            }

            // Content
            $text = '';
            if ($stamp['type'] === 'text') {
                $text = $stamp['text'] ?? '';
            } elseif ($stamp['type'] === 'tag') {
                $key = $stamp['key'] ?? '';
                $text = $this->resolveTag($key, $approval);
            }

            if (empty($text)) continue;

             // Style
            $style = '';
            if (!empty($stamp['isBold'])) $style .= 'B';
            if (!empty($stamp['isItalic'])) $style .= 'I';
            if (!empty($stamp['isUnderline'])) $style .= 'U';

            // Render
            $mpdf->SetFont($fontFamily, $style, $fontSizePt);
            $mpdf->SetTextColor(0, 0, 0);
            $mpdf->Text($sX, $sY, $text);
        }
    }

    private function resolveTag($key, SuratApproval $approval)
    {
        $key = str_replace(['{', '}'], '', $key);
        $surat = $approval->surat;
        // Indonesian Date Helper
        $formatDate = fn($date) => $date ? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') : '-';

        switch ($key) {
            case 'no_surat': return $surat->no_surat ?? $surat->nomor_surat ?? '[Nomor Surat]';
            case 'tanggal_surat': return $formatDate($surat->tanggal_surat);
            case 'tanggal': return $formatDate(now()); // Current validation date
            case 'waktu_ttd': return $approval->updated_at ? $approval->updated_at->format('H:i') . ' WIB' : date('H:i') . ' WIB';
            case 'nama_penandatangan': 
                return $approval->dosen ? $approval->dosen->nama : ($approval->user?->name ?? 'Pejabat');
            case 'nip':
            case 'nip_penandatangan': 
                $nip = $approval->dosen ? $approval->dosen->nip : '-';
                $isStampType = $surat->jenis->is_uploaded ?? false;
                return ($isStampType && $nip !== '-') ? 'NIP. ' . $nip : $nip;
            case 'jabatan_penandatangan':
                return $approval->role_nama ?: ($approval->role?->nama ?? 'Pejabat');
            default: return $key;
        }
    }

}
