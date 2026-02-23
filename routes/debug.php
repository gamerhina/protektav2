<?php

use App\Models\Seminar;
use App\Models\DocumentTemplate;

Route::get('/debug-signature/{seminarId}', function($seminarId) {
    $seminar = Seminar::with('seminarJenis')->findOrFail($seminarId);
    
    $signatureMethod = 'qr_code';
    $template = $seminar->seminarJenis->documentTemplates()->where('aktif', true)->first();
    
    return response()->json([
        'seminar_id' => $seminar->id,
        'seminar_jenis_id' => $seminar->seminar_jenis_id,
        'seminar_jenis_nama' => $seminar->seminarJenis->nama,
        'template_found' => $template ? true : false,
        'template_id' => $template ? $template->id : null,
        'template_nama' => $template ? $template->nama : null,
        'template_signature_method' => $template ? $template->signature_method : null,
        'final_signature_method' => $template ? $template->signature_method : $signatureMethod,
        'all_templates' => $seminar->seminarJenis->documentTemplates()->select('id', 'nama', 'aktif', 'signature_method')->get()
    ]);
});
