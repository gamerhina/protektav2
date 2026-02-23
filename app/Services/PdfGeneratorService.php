<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\SuratApproval;
use App\Models\SuratSignature;
use App\Models\Seminar;
use App\Models\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PdfGeneratorService
{
    /**
     * Generate PDF from a Surat model.
     */
    public function generateSuratPdf(Surat $surat, ?SuratTemplate $template = null)
    {
        // Try to get template from either relationship (new or old system)
        if (!$template) {
            // Try new system first (templates - hasMany)
            $template = $surat->jenis->templates->first();
            
            // Fallback to old system (template - belongsTo)
            if (!$template) {
                $template = $surat->jenis->template;
            }
        }

        if (!$template && !$surat->html_content) {
            throw new \Exception('Template surat tidak ditemukan.');
        }

        $html = $surat->html_content ?: ($template->template_html ?? '');
        
        if (!$html) {
            throw new \Exception('Konten surat atau template HTML tidak ditemukan.');
        }

        $html = $this->replaceSuratTags($html, $surat);

        return $this->renderPdf($html, $template->paper_size ?? 'A4', $template->header_image_path ?? null, [
            'header_repeat' => $template->header_repeat ?? false,
            'header_visibility' => $template->header_visibility ?? 'all',
            'header_custom_pages' => $template->header_custom_pages ?? '',
        ]);
    }

    /**
     * Generate HTML from template with data (for preview/download)
     */
    public function generateSuratHtml(Surat $surat, ?SuratTemplate $template = null): string
    {
        // Try to get template from either relationship (new or old system)
        if (!$template) {
            // Try new system first (templates - hasMany)
            $template = $surat->jenis->templates->first();
            
            // Fallback to old system (template - belongsTo)
            if (!$template) {
                $template = $surat->jenis->template;
            }
        }

        if (!$template && !$surat->html_content) {
            return '<div style="padding: 50px; text-align: center; font-family: sans-serif; color: #666;">
                        <h3 style="color: #444;">Template Tidak Ditemukan</h3>
                        <p>Jenis surat ini belum memiliki template aktif, dan tidak ada konten kustom yang tersimpan.</p>
                    </div>';
        }

        $html = $surat->html_content ?: ($template->template_html ?? '');
        $html = $this->replaceSuratTags($html, $surat);
        
        // Render with header support (same as seminar)
        return $this->renderHtml($html, $template->header_image_path ?? null, [
            'header_repeat' => $template->header_repeat ?? false,
            'header_visibility' => $template->header_visibility ?? 'all',
            'header_custom_pages' => $template->header_custom_pages ?? '',
        ]);
    }

    /**
     * Generate PDF from a Seminar model.
     */
    public function generateSeminarPdf(Seminar $seminar, ?DocumentTemplate $template = null)
    {
        $template = $template ?? $seminar->jenis->documentTemplates()->where('aktif', true)->first();

        if (!$template || !$template->template_html) {
            throw new \Exception('Template HTML tidak ditemukan untuk jenis seminar ini.');
        }

        $html = $this->replaceSeminarTags($template->template_html, $seminar, $template);

        return $this->renderPdf($html, $template->paper_size ?? 'A4', $template->header_image_path, [
            'header_repeat' => $template->header_repeat,
            'header_visibility' => $template->header_visibility,
            'header_custom_pages' => $template->header_custom_pages,
        ]);
    }

    /**
     * Strip layout wrappers to get raw content (prevents recursive wrapping)
     */
    public function stripWrappers(string $html): string
    {
        $html = preg_replace('/<div\s+[^>]*class=["\']pages-container["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<div\s+[^>]*class=["\']document-preview["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<div\s+[^>]*class=["\']content["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<div\s+[^>]*class=["\']page-separator["\'][^>]*>.*?<\/div>/is', '', $html);
        $html = str_replace(['</div></div></div>', '</div></div>'], '</div>', $html); 
        $html = preg_replace('/<\/div>\s*$/i', '', trim($html)); 
        $html = preg_replace('/^<div[^>]*>/i', '', trim($html)); 
        return $html;
    }

    /**
     * Core HTML rendering for preview.
     */
    public function renderHtml(string $html, ?string $headerImagePath = null, ?array $settings = []): string
    {
        $headerRepeat = $settings['header_repeat'] ?? false;
        $headerVisibility = $settings['header_visibility'] ?? 'all';
        $headerCustomPages = $settings['header_custom_pages'] ?? '';
        $base64Header = '';

        if ($headerImagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($headerImagePath)) {
            $path = \Illuminate\Support\Facades\Storage::disk('public')->path($headerImagePath);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $imgData = file_get_contents($path);
            $base64Header = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        $headerImageTag = $base64Header ? '<div class="document-header" style="text-align: center; margin-bottom: 2px;"><img src="' . $base64Header . '" style="width: 100%; height: auto;"></div>' : '';
        $repeatedHeaderTag = ($base64Header && $headerRepeat) ? '<div class="document-header-repeated" style="text-align: center; margin: 5px 0 2px 0;"><img src="' . $base64Header . '" style="width: 100%; height: auto;"></div>' : '';

        // Clean up leading/trailing breaks
        $html = preg_replace('/^(\s*<p>(?:&nbsp;|\s|<br\s*\/?>)*<\/p>\s*)+/i', '', ltrim($html));

        // Ensure we work on raw content by stripping any existing layout wrappers
        $html = $this->stripWrappers($html);

        // Split by page break - keeping the delimiter to preserve the markers for the editor
        $parts = preg_split('/(<div\s+[^>]*class=["\']page-break["\'][^>]*>\s*<\/div>)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pagesHtml = '';
        
        $customPages = ($headerVisibility === 'custom' && $headerCustomPages) ? array_map('trim', explode(',', $headerCustomPages)) : [];

        $currentPageIndex = 0;
        foreach ($parts as $index => $part) {
            // If this part is a page-break marker, just add it as is (visual indicator + for splitting next time)
            if (preg_match('/class=["\']page-break["\']/i', $part)) {
                $pagesHtml .= $part;
                $pagesHtml .= "<div class=\"page-separator\"></div>";
                continue;
            }

            $pageNum = ++$currentPageIndex;
            $currentPageHtml = '';
            
            // Determine if header should be on this page
            if ($pageNum === 1) {
                if ($headerVisibility === 'all' || $headerVisibility === 'first_only' || in_array('1', $customPages)) {
                    $currentPageHtml .= $headerImageTag;
                }
            } else {
                if ($headerVisibility === 'all' || ($headerVisibility === 'except_first' && $headerRepeat) || in_array((string)$pageNum, $customPages)) {
                    $currentPageHtml .= $repeatedHeaderTag ?: $headerImageTag;
                }
            }

            $currentPageHtml .= $part;

            $pagesHtml .= "
                <div class=\"document-preview\">
                    <div class=\"content\">
                        $currentPageHtml
                    </div>
                </div>
            ";
            
            // Add a visual indicator if this is not the last part AND the next part is not a marker 
            // (markers are already followed by a indicator in their own block)
            if ($index < count($parts) - 1 && !preg_match('/class=["\']page-break["\']/i', $parts[$index + 1])) {
                $pagesHtml .= "<div class=\"page-separator\"></div>";
            }
        }

        return "<div class=\"pages-container\">$pagesHtml</div>";
    }

    /**
     * Get base styles for document rendering
     */
    public function getBaseStyles(): string
    {
        return "
            body { background: #f1f5f9; margin: 0; padding: 40px 0; }
            .document-preview {
                background: white;
                margin: 0 auto;
                width: 21cm;
                min-height: 29.7cm;
                padding: 0 1.5cm 1.5cm 1.5cm;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                border: 1px solid #e2e8f0;
                box-sizing: border-box;
                position: relative;
            }
            .content {
                font-family: 'Times New Roman', Times, serif;
                color: #000;
                line-height: 1.6;
                font-size: 12pt;
            }
            .page-separator {
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f1f5f9;
                width: 100%;
                margin: 0;
                position: relative;
            }
            .page-separator::after {
                content: 'PAGE BREAK';
                color: #94a3b8;
                font-size: 10px;
                font-weight: bold;
                letter-spacing: 2px;
                padding: 4px 15px;
                border-radius: 20px;
                background: #f8fafc;
            }
            table { border-collapse: collapse; width: 100%; }
            table p { margin: 0 !important; padding: 0 !important; }
            img { max-width: 100%; height: auto; }
            @media print {
                body { background: white; padding: 0; }
                .document-preview { 
                    box-shadow: none; 
                    margin: 0; 
                    width: 100%; 
                    border: none;
                    padding: 0 1.5cm 1.5cm 1.5cm;
                    page-break-after: always;
                    break-after: page;
                }
                .document-preview:last-child {
                    page-break-after: avoid !important;
                    break-after: avoid !important;
                }
                .page-separator { display: none; }
                .page-break { display: none !important; }
            }
        ";
    }

    /**
     * Core PDF rendering logic using DomPDF.
     */
    public function renderPdf(string $html, string $paperSize = 'A4', ?string $headerImagePath = null, ?array $settings = [])
    {
        $headerRepeat = $settings['header_repeat'] ?? false;
        $headerVisibility = $settings['header_visibility'] ?? 'all';
        $headerCustomPages = $settings['header_custom_pages'] ?? '';
        $headerHtml = '';
        $isInlineHeader = ($headerVisibility === 'custom');

        if ($headerImagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($headerImagePath)) {
            $path = \Illuminate\Support\Facades\Storage::disk('public')->path($headerImagePath);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $imgData = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
            
            $headerImageHtml = '<img src="' . $base64 . '" style="width: 100%; height: auto;">';
            $headerImageTag = '<div style="text-align: center; margin-bottom: 2px;">' . $headerImageHtml . '</div>';
            
            if ($headerVisibility === 'custom' && $headerCustomPages) {
                // Use inline injection for custom pages
                $pagesToShow = array_map('trim', explode(',', $headerCustomPages));
                $parts = explode('<div class="page-break"></div>', $html);
                foreach ($parts as $index => &$part) {
                    $pageNum = $index + 1;
                    if (in_array((string)$pageNum, $pagesToShow)) {
                        $part = $headerImageTag . $part;
                        // Clean leading empty tags in this part after injecting header
                        $part = preg_replace('/^(\s*<p>(?:&nbsp;|\s|<br\s*\/?>)*<\/p>\s*)+/i', '', ltrim($part));
                    }
                }
                $html = implode('<div class="page-break"></div>', $parts);
                $headerHtml = '';
            } elseif ($headerRepeat) {
                // Repeat header on every page using fixed positioning
                $headerHtml = '<header style="position: fixed; top: 0; left: 0.5cm; right: 0.5cm; height: 3cm; text-align: center;">' . $headerImageHtml . '</header>';
            } else {
                // Only on first page
                if ($headerVisibility === 'all' || $headerVisibility === 'first_only') {
                    $headerHtml = $headerImageTag;
                }
            }
        }

        $marginTop = ($headerRepeat && !$isInlineHeader) ? "3.5cm" : "0";

        // Clean leading empty paragraphs/spaces from HTML to prevent gap on first page
        $html = preg_replace('/^(\s*<p>(?:&nbsp;|\s|<br\s*\/?>)*<\/p>\s*)+/i', '', ltrim($html));

        // Add basic CSS for PDF rendering
        $styledHtml = "
            <html>
            <head>
                <style>
                    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; line-height: 1.5; color: #000; margin: 0; padding: 0; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 1em; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11pt; vertical-align: middle; }
                    table p { margin: 0 !important; padding: 0 !important; }
                    .no-border, .no-border tr, .no-border td { border: none !important; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .text-justify { text-align: justify; }
                    .font-bold { font-weight: bold; }
                    .page-break { page-break-after: always; }
                    @page { margin: 0 0.5cm 0.5cm 0.5cm; }
                    .content { margin-top: $marginTop; }
                    /* Ensure first element after margin-top has no extra margin */
                    .content > *:first-child { margin-top: 0 !important; padding-top: 0 !important; }
                    .page-break + * { margin-top: 0 !important; }
                    .document-header + * { margin-top: 0 !important; }
                    .document-header-repeated + * { margin-top: 0 !important; }
                    p:empty, p:blank { display: none !important; }
                </style>
            </head>
            <body>
                $headerHtml
                <div class=\"content\">
                    $html
                </div>
            </body>
            </html>
        ";

        return Pdf::loadHTML($styledHtml)->setPaper($paperSize);
    }

    /**
     * Replace tags in Surat templates with improved tag matching and sizing support.
     */
    public function replaceSuratTags(string $html, Surat $surat): string
    {
        // 1. Initial Cleanup
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = str_replace(["\xc2\xa0", "&nbsp;"], ' ', $html);
        $html = str_replace('<!-- pagebreak -->', '<div class="page-break"></div>', $html);
        
        $data = $this->getSuratData($surat);

        // 2. Main Replacement Regex
        // This regex catches everything between << and >> even with spaces and HTML tags inside
        $html = preg_replace_callback('/<<\s*((?:(?!>>).)+?)\s*>>/is', function($matches) use ($surat, $data) {
            $rawMatch = $matches[0];
            $rawContent = $matches[1];
            $cleanContent = strip_tags($rawContent);
            
            // Sizing support: tag:width:height or tag:size
            $parts = explode(':', $cleanContent);
            $tagOriginal = trim($parts[0]);
            
            // Normalize tag for lookup (lowercase, underscores, no special chars)
            $tag = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '.'], '_', strtolower($tagOriginal)));
            
            if (empty($tag)) return $rawMatch;

            // A. Handle QR Tags & Signature Tags (Image rendering)
            if (str_ends_with($tag, '_qr_code') || str_ends_with($tag, '_qr_signature') || str_ends_with($tag, '_signature')) {
                // Sizing
                $width = $height = 100;
                if (isset($parts[2])) {
                    $width = (int)trim($parts[1]);
                    $height = (int)trim($parts[2]);
                } elseif (isset($parts[1])) {
                    $width = $height = (int)trim($parts[1]);
                }

                $url = '';
                if ($tag === 'surat_qr_code') {
                    $url = $surat->verification_url;
                } elseif ($tag === 'dosen_qr_signature' || $tag === 'pemohon_qr_signature') {
                    $url = \Illuminate\Support\Facades\URL::signedRoute('verify.surat.signature', ['suratId' => $surat->id, 'type' => 'pemohon']);
                } elseif (str_ends_with($tag, '_qr_signature')) {
                    $roleCodeToMatch = str_replace('_qr_signature', '', $tag);
                    $approval = $surat->approvals->filter(function($a) use ($roleCodeToMatch) {
                        $role = $a->role;
                        if (!$role && $a->role_nama) {
                            $role = \App\Models\SuratRole::where('nama', $a->role_nama)->first();
                        }
                        
                        $codes = [];
                        if ($role) {
                            if ($role->kode) $codes[] = $role->kode;
                            if ($role->nama) $codes[] = $role->nama;
                        }
                        if ($a->role_nama) $codes[] = $a->role_nama;
                        
                        foreach (array_unique($codes) as $raw) {
                            $normalized = strtolower($raw);
                            $check = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '.'], '_', $normalized));
                            
                            if ($check === $roleCodeToMatch) return true;
                            
                            // Map dynamic aliases
                            if ($roleCodeToMatch === 'pa' && str_contains($normalized, 'pembimbing akademik')) return true;
                            if ($roleCodeToMatch === 'p1' && str_contains($normalized, 'pembimbing 1')) return true;
                            if ($roleCodeToMatch === 'p2' && str_contains($normalized, 'pembimbing 2')) return true;
                            if ($roleCodeToMatch === 'pmb' && (str_contains($normalized, 'pembahas') || str_contains($normalized, 'evaluator'))) return true;
                        }
                        return false;
                    })->first();
                    
                    if ($approval && $approval->status === \App\Models\SuratApproval::STATUS_APPROVED) {
                        $signatureType = $approval->role ? ($approval->role->kode ?: $approval->role->nama) : $approval->role_nama;
                        $url = \Illuminate\Support\Facades\URL::signedRoute('verify.surat.signature', [
                            'suratId' => $surat->id, 
                            'type' => str_replace(' ', '_', strtolower($signatureType))
                        ]);
                    }
                }

                if ($url) {
                    try {
                        // Only shorten if it's a long local URL that hasn't been shortened yet
                        if (str_contains($url, config('app.url')) && !str_contains($url, '/v/')) {
                            $url = app(\App\Services\UrlShortenerService::class)->shorten($url);
                        }
                        
                        $options = new \chillerlan\QRCode\QROptions([
                            'version'      => \chillerlan\QRCode\QRCode::VERSION_AUTO,
                            'outputType'   => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                            'eccLevel'     => \chillerlan\QRCode\QRCode::ECC_L,
                            'imageBase64'  => true,
                            'scale'        => 5,
                            'quietzoneSize'=> 1,
                        ]);
                        $qrcode = (new \chillerlan\QRCode\QRCode($options))->render($url);
                        return '<img src="' . $qrcode . '" width="' . $width . '" height="' . $height . '">';
                    } catch (\Exception $e) {
                        \Log::error("Failed to generate QR for tag {$tagOriginal}: " . $e->getMessage());
                        return '';
                    }
                }

                if (array_key_exists($tag, $data) || array_key_exists($tagOriginal, $data)) {
                    $imgContent = $data[$tag] ?? $data[$tagOriginal] ?? '';
                    if (is_string($imgContent) && str_starts_with($imgContent, 'data:image')) {
                        return '<img src="' . $imgContent . '" width="' . $width . '" height="' . $height . '">';
                    }
                    return ''; // Field exists but is not an image
                }
                
                return ''; // Hide signatures if not available/approved
            }

            // B. Handle Text Tags
            if (array_key_exists($tag, $data)) {
                return (string)($data[$tag] ?? '');
            }
            if (array_key_exists($tagOriginal, $data)) {
                return (string)($data[$tagOriginal] ?? '');
            }
            
            // For unreplaced tags, re-encode the original match to prevent it being parsed as HTML
            return htmlspecialchars($rawMatch, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }, $html);

        return $html;
    }

    /**
     * Replace tags in Seminar templates.
     */
    public function replaceSeminarTags(string $html, Seminar $seminar, ?DocumentTemplate $template = null): string
    {
        \Log::info("replaceSeminarTags called for Seminar ID: " . $seminar->id);
        // Decode HTML entities first (TinyMCE saves tags as &lt;&lt;tag&gt;&gt;)
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Convert TinyMCE default pagebreak comment to our div
        $html = str_replace('<!-- pagebreak -->', '<div class="page-break"></div>', $html);
        
        $data = $this->getSeminarData($seminar);

        // Replace non-image tags first (case-insensitive)
        foreach ($data as $key => $value) {
            // Skip signature and QR tags - will be handled by regex callback below
            if (str_ends_with($key, '_signature') || str_ends_with($key, '_qr_code') || str_ends_with($key, '_qr_signature')) {
                continue;
            }
            $html = str_ireplace("<<{$key}>>", $value ?? '', $html);
        }

        // Handle Tags with sizing support: <<tag>>, <<tag:size>>, or <<tag:width:height>>
        // This handles both QR tags (generated on the fly) and Signature tags (base64 from getSeminarData)
        // Updated to support dynamic roles like <<koor_qr_signature>>, <<dekan_signature>>, etc.
        // Regex improved to handle optional spaces around tag content
        $html = preg_replace_callback('/<<\s*([a-z0-9_]+)(?::(\d+))?(?::(\d+))?\s*>>/i', function($matches) use ($seminar, $data, $template) {
            $tag = strtolower(trim($matches[1])); // Normalize to lowercase and trim spaces
            \Log::info("Seminar Tag Match: " . $tag);

            // Only handle tags that look like image/QR tags
            if (!str_ends_with($tag, '_qr_code') && !str_ends_with($tag, '_qr_signature') && !str_ends_with($tag, '_signature')) {
                // Check if this tag exists in $data, if so it was already replaced by str_replace
                // If it wasn't replaced, it might be an unhandled tag, return it as is
                return isset($data[$tag]) ? $data[$tag] : $matches[0];
            }

            // Determine width and height
            if (isset($matches[3])) { // <<tag:width:height>>
                $width = (int)$matches[2];
                $height = (int)$matches[3];
            } elseif (isset($matches[2])) { // <<tag:size>>
                $width = $height = (int)$matches[2];
            } else { // Default
                $width = $height = 100;
            }

            // Case 1: QR Tags (generated for verification)
            if (str_ends_with($tag, '_qr_code') || str_ends_with($tag, '_qr_signature')) {
                $url = '';
                if ($tag === 'seminar_qr_code') {
                    $routeParams = ['seminarId' => $seminar->id];
                    if ($template) $routeParams['template'] = $template->id;
                    $url = URL::signedRoute('verify.seminar', $routeParams);
                } else {
                    // p1_qr_signature, koor_qr_signature, etc.
                    $type = str_replace('_qr_signature', '', $tag);
                    
                    // Normalize shorthand
                    if ($type === 'pmb') $type = 'pembahas'; 
                    if ($type === 'pa') $type = 'pembimbing_akademik';
                    
                    // CRITICAL CHECK: Only render signature if the evaluator is assigned and has signed
                    $hasSignature = $seminar->signatures()->where('jenis_penilai', $type)->exists();
                    
                    // Fallback for role-based signatures that might not have a record but exist globally
                    $isRoleBased = in_array(strtolower($type), ['kajur', 'sekjur', 'koor', 'dekan']);
                    if ($isRoleBased && !$hasSignature) {
                        // For these roles, we only show it if the seminar is already completed
                        $hasSignature = ($seminar->status === 'selesai' || $seminar->status === 'disetujui');
                    }

                    // For p1, p2, pembahas, pa - they MUST be assigned and MUST have signed
                    if (in_array($type, ['p1', 'p2', 'pembahas', 'pa', 'pembimbing_akademik'])) {
                        $exists = false;
                        if ($type === 'p1') $exists = ($seminar->p1_dosen_id || $seminar->p1_nama);
                        elseif ($type === 'p2') $exists = ($seminar->p2_dosen_id || $seminar->p2_nama);
                        elseif ($type === 'pembahas' || $type === 'pmb') $exists = ($seminar->pembahas_dosen_id || $seminar->pembahas_nama);
                        elseif ($type === 'pa' || $type === 'pembimbing_akademik') $exists = ($seminar->pa_dosen_id || $seminar->pa_nama);
                        
                        if (!$exists || !$hasSignature) {
                            return '';
                        }
                    } elseif (!$hasSignature) {
                        // For other roles, if no signature record and not a completed role-based one, hide it
                        return '';
                    }

                    $routeParams = [
                        'seminarId' => $seminar->id, 
                        'type' => $type
                    ];
                    if ($template) $routeParams['template'] = $template->id;
                    $url = URL::signedRoute('verify.seminar.signature', $routeParams);
                }
                
                if ($url) {
                    try {
                        // Shorten URL for cleaner QR
                        $url = app(\App\Services\UrlShortenerService::class)->shorten($url);

                        $options = new \chillerlan\QRCode\QROptions([
                            'version'      => \chillerlan\QRCode\QRCode::VERSION_AUTO,
                            'outputType'   => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                            'eccLevel'     => \chillerlan\QRCode\QRCode::ECC_L,
                            'scale'        => 10,
                            'imageBase64'  => true,
                            'quietzoneSize'=> 1,
                        ]);
                        
                        $qrcode = (new \chillerlan\QRCode\QRCode($options))->render($url);
                        return '<img src="' . $qrcode . '" width="' . $width . '" height="' . $height . '">';
                    } catch (\Exception $e) {
                        \Log::error("Failed to generate Seminar QR for tag {$tag}: " . $e->getMessage());
                        return '';
                    }
                }
            }

            // Case 2: Actual Signature Tags (already base64 string in $data)
            if (isset($data[$tag]) && !empty($data[$tag])) {
                $imgSrc = $data[$tag];
                // If it's already a base64 string, use it directly
                if (str_starts_with($imgSrc, 'data:image')) {
                    return '<img src="' . $imgSrc . '" width="' . $width . '" height="' . $height . '">';
                }
            }

            return ''; // Hide the tag if no content found
        }, $html);

        return $html;
    }

    /**
     * Collect all available data for Surat tagging.
     */
    private function getSuratData(Surat $surat): array
    {
        $data = [
            'surat_no' => $surat->no_surat,
            'no_surat' => $surat->no_surat,
            'surat_nr' => $surat->no_surat,
            'surat_tanggal' => $surat->tanggal_surat ? $surat->tanggal_surat->translatedFormat('d F Y') : '',
            'surat_hari' => $surat->tanggal_surat ? $surat->tanggal_surat->translatedFormat('l') : '',
            'surat_tahun' => $surat->tanggal_surat ? $surat->tanggal_surat->format('Y') : '',
            'surat_tujuan' => $surat->tujuan,
            'surat_perihal' => $surat->perihal,
            'surat_isi' => $surat->isi,
            'surat_email' => $surat->penerima_email,
            'surat_jenis_nama' => $surat->jenis->nama ?? '',
            'surat_link' => route('admin.surat.preview-html', $surat),
            'link_dokumen' => route('admin.surat.preview-html', $surat),
            'surat_verification_url' => $surat->verification_url,
        ];

        // Pemohon data
        $pemohonDosen = $surat->pemohonDosen;
        $pemohonMahasiswa = $surat->pemohonMahasiswa;
        $pemohonAdmin = $surat->pemohonAdmin;
        
        if ($pemohonDosen) {
            $data['dosen_nama'] = $pemohonDosen->nama;
            $data['pemohon_nama'] = $pemohonDosen->nama;
            $nipValue = $pemohonDosen->nip ?? '';
            $data['nip'] = $nipValue;
            $data['dosen_nip'] = $nipValue;
            $data['pemohon_nip_npm'] = $nipValue;
            $data['dosen_email'] = $pemohonDosen->email;

            if ($pemohonDosen->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($pemohonDosen->signature_path)) {
                $path = \Illuminate\Support\Facades\Storage::disk('public')->path($pemohonDosen->signature_path);
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $imgData = file_get_contents($path);
                $data['dosen_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
            }
        } elseif ($pemohonMahasiswa) {
            $data['mahasiswa_nama'] = $pemohonMahasiswa->nama;
            $data['pemohon_nama'] = $pemohonMahasiswa->nama;
            $data['mahasiswa_npm'] = $pemohonMahasiswa->npm;
            $data['pemohon_nip_npm'] = $pemohonMahasiswa->npm;
            $data['mahasiswa_prodi'] = $pemohonMahasiswa->prodi;
            $data['mahasiswa_email'] = $pemohonMahasiswa->email;
        } elseif ($pemohonAdmin) {
            $data['admin_nama'] = $pemohonAdmin->nama;
            $data['pemohon_nama'] = $pemohonAdmin->nama;
        }

        // Generic Pemohon Tags for custom types
        $pemohon = $surat->pemohon;
        if ($pemohon && !isset($data['pemohon_nama'])) {
            $data['pemohon_nama'] = $pemohon->nama ?? '';
            $data['pemohon_email'] = $pemohon->email ?? '';
            $data['pemohon_wa'] = $pemohon->wa ?: ($pemohon->hp ?? '');
            $data['pemohon_nip_npm'] = $pemohon->nip ?: ($pemohon->npm ?? '');
        }

        // Custom fields from form data
        if (is_array($surat->data)) {
            foreach ($surat->data as $key => $val) {
                if (!is_array($val)) {
                    $data[$key] = $val;
                    // Also provide a normalized key (lowercase and underscores)
                    $normalizedKey = str_replace(' ', '_', strtolower($key));
                    $data[$normalizedKey] = $val;
                }
            }
        }

        // Approval data
        if ($surat->approvals->isNotEmpty()) {
            foreach ($surat->approvals as $approval) {
                $role = $approval->role;
                
                // Robust Fallback: If role_id is empty, try to find role by name
                if (!$role && $approval->role_nama) {
                    $role = \App\Models\SuratRole::where('nama', $approval->role_nama)->first();
                }

                if ($role || $approval->role_nama) {
                    $codes = [];
                    if ($role && $role->kode) $codes[] = $role->kode;
                    if ($role && $role->nama) $codes[] = $role->nama;
                    if ($approval->role_nama) $codes[] = $approval->role_nama;

                    foreach (array_unique($codes) as $rawCode) {
                        $roleCode = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '.'], '_', strtolower($rawCode)));
                        if (!$roleCode) continue;

                        $data[$roleCode . '_nama'] = $approval->dosen->nama ?? $approval->role_nama ?? '-';
                        $nipValue = $approval->dosen->nip ?? '-';
                        
                        // Add NIP prefix if it's a stamp-based letter and NIP is not empty
                        $isStampType = $surat->jenis->is_uploaded ?? false;
                        $data[$roleCode . '_nip'] = ($isStampType && $nipValue !== '-') ? 'NIP. ' . $nipValue : $nipValue;
                        
                        $data[$roleCode . '_jabatan'] = $role->nama ?? $approval->role_nama;
                        $data[$roleCode . '_tanggal'] = $approval->approved_at ? $approval->approved_at->translatedFormat('d F Y') : '';

                        // Signature image if approved
                        if ($approval->status === SuratApproval::STATUS_APPROVED && $approval->signature_path && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($approval->signature_path)) {
                            $path = \Illuminate\Support\Facades\Storage::disk('uploads')->path($approval->signature_path);
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $imgData = @file_get_contents($path);
                            if ($imgData) {
                                $data[$roleCode . '_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
                            }
                        }
                    }
                }
        }

            // Provide aliases for dynamic roles to match common tag naming conventions
            $aliases = [
                'pa' => ['pembimbing_akademik', 'dosen_pa', 'pembimbing_akademik_dinamis'],
                'p1' => ['pembimbing_1', 'pembimbing1', 'pembimbing_1_dinamis'],
                'p2' => ['pembimbing_2', 'pembimbing2', 'pembimbing_2_dinamis'],
                'pmb' => ['pembahas', 'evaluator', 'pembahas_dinamis'],
            ];

            foreach ($aliases as $short => $mains) {
                foreach ($mains as $main) {
                    // Try to map main to short
                    if (isset($data[$main . '_nama']) || isset($data[$main . '_nip'])) {
                        foreach (['nama', 'nip', 'signature', 'jabatan', 'tanggal'] as $suffix) {
                            if (isset($data[$main . '_' . $suffix]) && !isset($data[$short . '_' . $suffix])) {
                                $data[$short . '_' . $suffix] = $data[$main . '_' . $suffix];
                            }
                        }
                    }
                    
                    // Try to map short to main (reverse)
                    if (isset($data[$short . '_nama']) || isset($data[$short . '_nip'])) {
                        foreach (['nama', 'nip', 'signature', 'jabatan', 'tanggal'] as $suffix) {
                            if (isset($data[$short . '_' . $suffix]) && !isset($data[$main . '_' . $suffix])) {
                                $data[$main . '_' . $suffix] = $data[$short . '_' . $suffix];
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Collect all available data for Seminar tagging.
     */
    public function getSeminarData(Seminar $seminar): array
    {
        $mahasiswa = $seminar->mahasiswa;
        
        $data = [
            'mahasiswa_nama' => $mahasiswa->nama,
            'mahasiswa_npm' => $mahasiswa->npm,
            'mahasiswa_prodi' => $mahasiswa->prodi,
            'mahasiswa_email' => $mahasiswa->email,
            'mahasiswa_no_hp' => $mahasiswa->wa ?? $mahasiswa->hp ?? '',
            
            'seminar_no_surat' => $seminar->no_surat,
            'seminar_judul' => $seminar->judul,
            'seminar_tanggal' => $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : '',
            'seminar_tahun' => $seminar->tanggal ? $seminar->tanggal->format('Y') : '',
            'seminar_hari' => $seminar->tanggal ? $seminar->tanggal->translatedFormat('l') : '',
            'seminar_waktu_mulai' => $seminar->waktu_mulai,
            'seminar_lokasi' => $seminar->lokasi,
            'seminar_status' => $seminar->status,
            'seminar_jenis_nama' => $seminar->seminarJenis->nama ?? '',
            'link_dokumen' => '<<link_dokumen>>',
        ];

        // Dosen data
        // P1
        if ($seminar->p1Dosen) {
            $data['p1_nama'] = $seminar->p1Dosen->nama;
            $data['p1_nip'] = $seminar->p1Dosen->nip;
            $data['p1_email'] = $seminar->p1Dosen->email;
        } else {
            $data['p1_nama'] = $seminar->p1_nama;
            $data['p1_nip'] = $seminar->p1_nip;
            $data['p1_email'] = '';
        }

        $sig1 = $seminar->signatures()
            ->where('jenis_penilai', 'p1')
            ->where('dosen_id', $seminar->p1_dosen_id)
            ->first();
        if ($sig1 && $sig1->tanda_tangan && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($sig1->tanda_tangan)) {
            $path = \Illuminate\Support\Facades\Storage::disk('uploads')->path($sig1->tanda_tangan);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $imgData = file_get_contents($path);
            $data['p1_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        // P2
        if ($seminar->p2Dosen) {
            $data['p2_nama'] = $seminar->p2Dosen->nama;
            $data['p2_nip'] = $seminar->p2Dosen->nip;
            $data['p2_email'] = $seminar->p2Dosen->email;
        } else {
            $data['p2_nama'] = $seminar->p2_nama;
            $data['p2_nip'] = $seminar->p2_nip;
            $data['p2_email'] = '';
        }

        $sig2 = $seminar->signatures()
            ->where('jenis_penilai', 'p2')
            ->where('dosen_id', $seminar->p2_dosen_id)
            ->first();
        if ($sig2 && $sig2->tanda_tangan && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($sig2->tanda_tangan)) {
            $path = \Illuminate\Support\Facades\Storage::disk('uploads')->path($sig2->tanda_tangan);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $imgData = file_get_contents($path);
            $data['p2_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        // Pembahas
        if ($seminar->pembahasDosen) {
            $data['pmb_nama'] = $seminar->pembahasDosen->nama;
            $data['pmb_nip'] = $seminar->pembahasDosen->nip;
            $data['pmb_email'] = $seminar->pembahasDosen->email;
        } else {
            $data['pmb_nama'] = $seminar->pembahas_nama;
            $data['pmb_nip'] = $seminar->pembahas_nip;
            $data['pmb_email'] = '';
        }

        $sigP = $seminar->signatures()
            ->where('jenis_penilai', 'pembahas')
            ->where('dosen_id', $seminar->pembahas_dosen_id)
            ->first();
        if ($sigP && $sigP->tanda_tangan && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($sigP->tanda_tangan)) {
            $path = \Illuminate\Support\Facades\Storage::disk('uploads')->path($sigP->tanda_tangan);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $imgData = file_get_contents($path);
            $data['pmb_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
        }

        // Nilai data
        $jenis = $seminar->seminarJenis;
        $submittedNilai = $seminar->nilai;
        
        $p1Raw = $submittedNilai->where('jenis_penilai', 'p1')->first()?->nilai_angka ?? 0;
        $p2Raw = $submittedNilai->where('jenis_penilai', 'p2')->first()?->nilai_angka ?? 0;
        $pembahasRaw = $submittedNilai->where('jenis_penilai', 'pembahas')->first()?->nilai_angka ?? 0;
        
        $p1Weight = (float) ($jenis->p1_weight ?? 0);
        $p2Weight = (float) ($jenis->p2_weight ?? 0);
        $pembahasWeight = (float) ($jenis->pembahas_weight ?? 0);
        $totalWeight = $p1Weight + $p2Weight + $pembahasWeight;

        $p1Weighted = $totalWeight > 0 ? round(($p1Raw * $p1Weight) / $totalWeight, 2) : 0;
        $p2Weighted = $totalWeight > 0 ? round(($p2Raw * $p2Weight) / $totalWeight, 2) : 0;
        $pembahasWeighted = $totalWeight > 0 ? round(($pembahasRaw * $pembahasWeight) / $totalWeight, 2) : 0;

        $finalScore = $seminar->calculateWeightedScore();
        $data['nilai_akhir'] = $finalScore;
        $data['nilai_huruf'] = \App\Helpers\Terbilang::toHuruf($finalScore);
        $data['nilai_terbilang'] = \App\Helpers\Terbilang::convert($finalScore);
        $data['HM'] = $data['nilai_huruf'];
        $data['terbilang'] = $data['nilai_terbilang'];
        $data['NxB'] = $finalScore;
        $data['dinyatakan'] = $finalScore >= 60 ? 'LULUS' : 'TIDAK LULUS';
        $data['diperkenankan'] = $finalScore >= 60 ? 'diperkenankan' : 'belum diperkenankan';
        $data['nilai_rata'] = count($submittedNilai) > 0 ? round($submittedNilai->avg('nilai_angka'), 2) : 0;
        $data['nilai_catatan'] = $seminar->nilai_catatan ?? '';
        
        $data['p1_bobot'] = $p1Weight;
        $data['p1_nilai'] = $p1Raw;
        $data['p1_nilai_bobot'] = $p1Weighted;
        $data['p1_nxb'] = $p1Weighted;
        
        $data['p2_bobot'] = $p2Weight;
        $data['p2_nilai'] = $p2Raw;
        $data['p2_nilai_bobot'] = $p2Weighted;
        $data['p2_nxb'] = $p2Weighted;
        
        $data['pmb_bobot'] = $pembahasWeight;
        $data['pmb_nilai'] = $pembahasRaw;
        $data['pmb_nilai_bobot'] = $pembahasWeighted;
        $data['pmb_nxb'] = $pembahasWeighted;

        // Individual Assessment Aspects (Aspek Penilaian)
        if ($jenis) {
            $aspects = $jenis->assessmentAspects()->orderBy('evaluator_type')->orderBy('urutan')->get();
            $groupedAspects = $aspects->groupBy('evaluator_type');
            
            foreach ($groupedAspects as $type => $typeAspects) {
                $prefix = ($type === 'pembahas' ? 'pmb' : $type);
                $evalNilai = $submittedNilai->where('jenis_penilai', $type)->first();
                $evalScores = $evalNilai ? $evalNilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                
                $i = 1;
                foreach ($typeAspects as $aspect) {
                    $key = $prefix . '_' . $i;
                    $scoreValue = $evalScores->has($aspect->id) ? $evalScores->get($aspect->id)->nilai : '';
                    
                    // Format number if numeric
                    if (is_numeric($scoreValue)) {
                        $data[$key] = number_format((float)$scoreValue, 2, ',', '.');
                    } else {
                        $data[$key] = $scoreValue;
                    }
                    $i++;
                }
            }
        }

        // Approval Role data (Dynamic)
        try {
            $roles = \App\Models\SuratRole::where('is_active', true)->with('delegatedDosen')->get();
            foreach ($roles as $role) {
                $prefix = strtolower($role->kode);
                $dosen = $role->delegatedDosen;
                
                $data[$prefix . '_nama'] = $dosen ? $dosen->nama : '';
                $data[$prefix . '_nip'] = $dosen ? $dosen->nip : '';
                $data[$prefix . '_jabatan'] = $role->nama;
                
                // For global roles, signatures are optional but if they have signed this seminar specifically
                $sig = $seminar->signatures()->where('jenis_penilai', $prefix)->first();
                if ($sig && $sig->tanda_tangan && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($sig->tanda_tangan)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('uploads')->path($sig->tanda_tangan);
                    if (file_exists($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $imgData = file_get_contents($path);
                        $data[$prefix . '_signature'] = 'data:image/' . $type . ';base64,' . base64_encode($imgData);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore if error
        }

        // Dynamic Fields from Seminar Form (berkas_syarat)
        // This handles custom fields defined in SeminarJenis->berkas_syarat_items
        if (is_array($seminar->berkas_syarat)) {
            // Build a lookup map: key => type from SeminarJenis config
            $berkasItemTypes = [];
            $berkasConfig = $seminar->seminarJenis->berkas_syarat_items ?? [];
            if (is_array($berkasConfig)) {
                foreach ($berkasConfig as $item) {
                    if (isset($item['key'])) {
                        $itemKey = $item['key'];
                        $itemType = $item['type'] ?? '';
                        // Auto-detect date type by key name if type is not set
                        if (!$itemType) {
                            $keyLower = strtolower($itemKey);
                            if ((str_contains($keyLower, 'tgl') || str_contains($keyLower, 'tanggal'))
                                && !str_contains($keyLower, 'tempat')) {
                                $itemType = 'date';
                            }
                        }
                        $berkasItemTypes[$itemKey] = $itemType;
                    }
                }
            }

            foreach ($seminar->berkas_syarat as $key => $val) {
                // Only add scalar values (text, numbers, dates)
                if (is_scalar($val)) {
                    // Format date fields to Indonesian locale
                    $fieldType = $berkasItemTypes[$key] ?? '';
                    if ($fieldType === 'date' && !empty($val)) {
                        try {
                            $data[$key] = \Carbon\Carbon::parse($val)->translatedFormat('d F Y');
                        } catch (\Exception $e) {
                            $data[$key] = $val;
                        }
                    } else {
                        $data[$key] = $val;
                    }
                }
            }
        }

        return $data;
    }
}

