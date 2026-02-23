<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\Admin\LandingPageSettingController;
use App\Http\Controllers\AdminSuratController;
use App\Http\Controllers\AdminSuratJenisController;
use App\Http\Controllers\AdminSuratTemplateController;
use App\Http\Controllers\AdminDocumentTemplateController;
use App\Http\Controllers\DosenSuratController;
use App\Helpers\Terbilang;

// Temporary debug route
require __DIR__.'/debug.php';


// Public routes
Route::get('/', LandingPageController::class)->name('landing');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// API route for Terbilang
Route::get('/api/terbilang/{number}', function ($number) {
    return response()->json([
        'number' => $number,
        'terbilang' => Terbilang::convert($number)
    ]);
});

Route::get('/favicon.ico', [App\Http\Controllers\ManagementController::class, 'showSeminarFile'])
    ->defaults('path', 'favicon.ico');

Route::get('/uploads/{path}', [App\Http\Controllers\ManagementController::class, 'showSeminarFile'])
    ->where('path', '.*')
    ->name('uploads.show');


// Additional public routes can go here if needed
Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');

// Public verification routes for QR codes (Signed for security)
Route::prefix('verify')->name('verify.')->middleware('signed')->group(function () {
    Route::get('/seminar/{seminarId}', [App\Http\Controllers\VerificationController::class, 'verifySeminar'])->name('seminar');
    Route::get('/seminar/{seminarId}/signature/{type}', [App\Http\Controllers\VerificationController::class, 'verifySeminarSignature'])->name('seminar.signature');
    Route::get('/surat/{suratId}', [App\Http\Controllers\VerificationController::class, 'verifySurat'])->name('surat');
    Route::get('/surat/{suratId}/signature/{type}', [App\Http\Controllers\VerificationController::class, 'verifySuratSignature'])->name('surat.signature');
});

// Short URL redirection
Route::get('/v/{code}', function ($code) {
    $shortUrl = \App\Models\ShortUrl::where('code', $code)->firstOrFail();
    return redirect($shortUrl->original_url);
})->name('v');

// Shared seminar document download route (Used by Admin, Dosen, and Mahasiswa)
Route::get('/seminar-document/{seminar}/template/{template}/download', [App\Http\Controllers\ManagementController::class, 'downloadSeminarPdf'])
    ->middleware(['auth:admin,dosen,mahasiswa'])
    ->name('seminar.document.download');


// Protected routes for admin
Route::middleware(['auth:admin', 'notifications'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    // Profile routes for admin
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/foto', [App\Http\Controllers\ProfileController::class, 'destroyFoto'])->name('profile.destroy-foto');
    Route::get('/change-password', [App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/change-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('change-password.update');

    // GDrive management routes
    Route::get('/gdrive', [App\Http\Controllers\GDriveController::class, 'index'])->name('gdrive.index');
    Route::get('/gdrive/create', [App\Http\Controllers\GDriveController::class, 'create'])->name('gdrive.create');
    Route::post('/gdrive', [App\Http\Controllers\GDriveController::class, 'store'])->name('gdrive.store');
    Route::get('/gdrive/{gdriveFolder}/edit', [App\Http\Controllers\GDriveController::class, 'edit'])->name('gdrive.edit');
    Route::put('/gdrive/{gdriveFolder}', [App\Http\Controllers\GDriveController::class, 'update'])->name('gdrive.update');
    Route::delete('/gdrive/{gdriveFolder}', [App\Http\Controllers\GDriveController::class, 'destroy'])->name('gdrive.destroy');

    // User management routes
    Route::get('/dosen', [App\Http\Controllers\ManagementController::class, 'indexDosen'])->name('dosen.index');
    Route::get('/dosen/create', [App\Http\Controllers\ManagementController::class, 'createDosen'])->name('dosen.create');
    Route::post('/dosen', [App\Http\Controllers\ManagementController::class, 'storeDosen'])->name('dosen.store');
    Route::get('/dosen/{dosen}/edit', [App\Http\Controllers\ManagementController::class, 'editDosen'])->name('dosen.edit');
    Route::put('/dosen/{dosen}', [App\Http\Controllers\ManagementController::class, 'updateDosen'])->name('dosen.update');
    Route::delete('/dosen/{dosen}', [App\Http\Controllers\ManagementController::class, 'destroyDosen'])->name('dosen.destroy');

    // Import dosen routes
    Route::get('/dosen/import', [App\Http\Controllers\ManagementController::class, 'showImportDosen'])->name('dosen.import.form');
    Route::post('/dosen/import', [App\Http\Controllers\ManagementController::class, 'importDosen'])->name('dosen.import');
    Route::get('/dosen/sample/download', [App\Http\Controllers\ManagementController::class, 'downloadSampleDosen'])->name('dosen.sample.download');

    Route::get('/mahasiswa', [App\Http\Controllers\ManagementController::class, 'indexMahasiswa'])->name('mahasiswa.index');
    Route::get('/mahasiswa/create', [App\Http\Controllers\ManagementController::class, 'createMahasiswa'])->name('mahasiswa.create');
    Route::post('/mahasiswa', [App\Http\Controllers\ManagementController::class, 'storeMahasiswa'])->name('mahasiswa.store');
    Route::get('/mahasiswa/{mahasiswa}/edit', [App\Http\Controllers\ManagementController::class, 'editMahasiswa'])->name('mahasiswa.edit');
    Route::put('/mahasiswa/{mahasiswa}', [App\Http\Controllers\ManagementController::class, 'updateMahasiswa'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{mahasiswa}', [App\Http\Controllers\ManagementController::class, 'destroyMahasiswa'])->name('mahasiswa.destroy');

    // Import mahasiswa routes
    Route::get('/mahasiswa/import', [App\Http\Controllers\ManagementController::class, 'showImportMahasiswa'])->name('mahasiswa.import.form');
    Route::post('/mahasiswa/import', [App\Http\Controllers\ManagementController::class, 'importMahasiswa'])->name('mahasiswa.import');
    Route::get('/mahasiswa/sample/download', [App\Http\Controllers\ManagementController::class, 'downloadSampleMahasiswa'])->name('mahasiswa.sample.download');

    Route::get('/admins', [App\Http\Controllers\ManagementController::class, 'indexAdmin'])->name('admins.index');
    Route::get('/admins/create', [App\Http\Controllers\ManagementController::class, 'createAdmin'])->name('admins.create');
    Route::post('/admins', [App\Http\Controllers\ManagementController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [App\Http\Controllers\ManagementController::class, 'editAdmin'])->name('admins.edit');
    Route::put('/admins/{admin}', [App\Http\Controllers\ManagementController::class, 'updateAdmin'])->name('admins.update');
    Route::delete('/admins/{admin}', [App\Http\Controllers\ManagementController::class, 'destroyAdmin'])->name('admins.destroy');

    // Pembimbing Akademik routes
    Route::get('/pembimbing-akademik', [App\Http\Controllers\ManagementController::class, 'indexPA'])->name('pa.index');

    // Seminar jenis management routes
    Route::resource('seminarjenis', App\Http\Controllers\SeminarJenisController::class)
        ->parameters(['seminarjenis' => 'seminarJenis'])
        ->names([
        'index' => 'seminarjenis.index',
        'create' => 'seminarjenis.create',
        'store' => 'seminarjenis.store',
        'show' => 'seminarjenis.show',
        'edit' => 'seminarjenis.edit',
        'update' => 'seminarjenis.update',
        'destroy' => 'seminarjenis.destroy',
    ]);

    Route::get('/seminarjenis/{seminarJenis}/templates', [AdminDocumentTemplateController::class, 'index'])->name('document-template.index');
    Route::get('/seminarjenis/{seminarJenis}/templates/create', [AdminDocumentTemplateController::class, 'create'])->name('document-template.create');
    Route::post('/seminarjenis/{seminarJenis}/templates', [AdminDocumentTemplateController::class, 'store'])->name('document-template.store');
    Route::get('/seminarjenis/{seminarJenis}/templates/{template}/edit', [AdminDocumentTemplateController::class, 'edit'])->name('document-template.edit');
    Route::put('/seminarjenis/{seminarJenis}/templates/{template}', [AdminDocumentTemplateController::class, 'update'])->name('document-template.update');
    Route::delete('/seminarjenis/{seminarJenis}/templates/{template}', [AdminDocumentTemplateController::class, 'destroy'])->name('document-template.destroy');
    Route::post('/seminarjenis/{seminarJenis}/templates/{template}/toggle-aktif', [AdminDocumentTemplateController::class, 'toggleAktif'])->name('document-template.toggle-aktif');

    // Assessment aspects management routes
    Route::get('/seminarjenis/{seminarJenis}/aspects', [App\Http\Controllers\AssessmentAspectController::class, 'index'])->name('seminarjenis.aspects.index');
    Route::post('/seminarjenis/{seminarJenis}/aspects', [App\Http\Controllers\AssessmentAspectController::class, 'store'])->name('seminarjenis.aspects.store');
    Route::put('/seminarjenis/{seminarJenis}/aspects/{aspect}', [App\Http\Controllers\AssessmentAspectController::class, 'update'])->name('seminarjenis.aspects.update');
    Route::delete('/seminarjenis/{seminarJenis}/aspects/{aspect}', [App\Http\Controllers\AssessmentAspectController::class, 'destroy'])->name('seminarjenis.aspects.destroy');

    // Seminar management routes (using methods in ManagementController)
    Route::get('/seminars/next-no-surat', [App\Http\Controllers\ManagementController::class, 'getNextNoSurat'])->name('seminar.next-no-surat');
    Route::get('/seminars/export', [App\Http\Controllers\ManagementController::class, 'exportSeminar'])->name('seminar.export');
    Route::get('/seminars', [App\Http\Controllers\ManagementController::class, 'indexSeminar'])->name('seminar.index');
    Route::get('/seminars/create', [App\Http\Controllers\ManagementController::class, 'createSeminar'])->name('seminar.create');
    Route::post('/seminars', [App\Http\Controllers\ManagementController::class, 'storeSeminar'])->name('seminar.store');
    Route::get('/seminars/{seminar}', [App\Http\Controllers\ManagementController::class, 'showSeminar'])->name('seminar.show');
    Route::get('/seminars/{seminar}/documents/{template}/preview', [App\Http\Controllers\ManagementController::class, 'previewSeminarPdf'])->name('seminar.document.preview');
    Route::get('/seminars/{seminar}/documents/{template}/download', [App\Http\Controllers\ManagementController::class, 'downloadSeminarPdf'])->name('seminar.document.download');
    Route::get('/seminars/{seminar}/edit', [App\Http\Controllers\ManagementController::class, 'editSeminar'])->name('seminar.edit');
    Route::put('/seminars/{seminar}', [App\Http\Controllers\ManagementController::class, 'updateSeminar'])->name('seminar.update');
    Route::get('/seminars/files/{path}', [App\Http\Controllers\ManagementController::class, 'showSeminarFile'])
        ->where('path', '.*')
        ->name('seminar.files.show');
    Route::put('/seminars/{seminar}/nilai/{jenis}', [App\Http\Controllers\ManagementController::class, 'updateNilai'])->name('seminar.update-nilai');
    Route::delete('/seminars/{seminar}/berkas/{filename}', [App\Http\Controllers\ManagementController::class, 'deleteBerkas'])
        ->where('filename', '.*')
        ->name('seminar.delete-berkas');
    Route::post('/seminars/{seminar}/preview-notification', [App\Http\Controllers\ManagementController::class, 'previewNotification'])->name('seminar.preview-notification');
    Route::post('/seminars/{seminar}/send-notification', [App\Http\Controllers\ManagementController::class, 'sendNotification'])->name('seminar.send-notification');
    Route::post('/seminars/{seminar}/approve', [App\Http\Controllers\ManagementController::class, 'approveSeminar'])->name('seminar.approve');
    Route::post('/seminars/{seminar}/reject', [App\Http\Controllers\ManagementController::class, 'rejectSeminar'])->name('seminar.reject');
    Route::delete('/seminars/{seminar}', [App\Http\Controllers\ManagementController::class, 'destroySeminar'])->name('seminar.destroy');

    // Document Preview & Generation Routes (Mapped from view requirements)
    Route::prefix('documents')->name('document.')->group(function() {
        Route::get('/preview/{seminar}/{template}', [App\Http\Controllers\ManagementController::class, 'previewSeminarPdf'])->name('preview');
        Route::post('/save-custom/{seminar}', [App\Http\Controllers\ManagementController::class, 'saveCustomSeminarHtml'])->name('save-custom');
        Route::post('/reset-custom/{seminar}', [App\Http\Controllers\ManagementController::class, 'resetCustomSeminarHtml'])->name('reset-custom');
        Route::post('/send/{seminar}/{template}', [App\Http\Controllers\ManagementController::class, 'sendSeminarDocument'])->name('send');
    });

    // Settings management
    Route::get('/settings/nilai-percentage', [App\Http\Controllers\SettingController::class, 'showNilaiPercentage'])->name('admin.settings.nilai-percentage');
    Route::put('/settings/nilai-percentage', [App\Http\Controllers\SettingController::class, 'updateNilaiPercentage'])->name('admin.settings.nilai-percentage.update');

    Route::get('/settings/landing-page', [LandingPageSettingController::class, 'edit'])->name('settings.landing');
    Route::put('/settings/landing-page', [LandingPageSettingController::class, 'update'])->name('settings.landing.update');


    // Surat management (admin)
    Route::get('/surat-jenis', [AdminSuratJenisController::class, 'index'])->name('suratjenis.index');
    Route::get('/surat-jenis/create', [AdminSuratJenisController::class, 'create'])->name('suratjenis.create');
    Route::post('/surat-jenis', [AdminSuratJenisController::class, 'store'])->name('suratjenis.store');
    Route::get('/surat-jenis/{suratJenis}/edit', [AdminSuratJenisController::class, 'edit'])->name('suratjenis.edit');
    Route::put('/surat-jenis/{suratJenis}', [AdminSuratJenisController::class, 'update'])->name('suratjenis.update');
    Route::delete('/surat-jenis/{suratJenis}', [AdminSuratJenisController::class, 'destroy'])->name('suratjenis.destroy');

    Route::get('/surat-jenis/{suratJenis}/templates', [AdminSuratTemplateController::class, 'index'])->name('surat-template.index');
    Route::get('/surat-jenis/{suratJenis}/templates/create', [AdminSuratTemplateController::class, 'create'])->name('surat-template.create');
    Route::post('/surat-jenis/{suratJenis}/templates', [AdminSuratTemplateController::class, 'store'])->name('surat-template.store');
    Route::get('/surat-jenis/{suratJenis}/templates/{template}/edit', [AdminSuratTemplateController::class, 'edit'])->name('surat-template.edit');
    Route::put('/surat-jenis/{suratJenis}/templates/{template}', [AdminSuratTemplateController::class, 'update'])->name('surat-template.update');
    Route::delete('/surat-jenis/{suratJenis}/templates/{template}', [AdminSuratTemplateController::class, 'destroy'])->name('surat-template.destroy');

    Route::post('/surat-jenis/{suratJenis}/templates/{template}/toggle-aktif', [AdminSuratTemplateController::class, 'toggleAktif'])->name('surat-template.toggle-aktif');


    Route::get('/surats', [AdminSuratController::class, 'index'])->name('surat.index');
    Route::get('/surats/export', [AdminSuratController::class, 'export'])->name('surat.export');
    Route::get('/surats/next-no-surat', [AdminSuratController::class, 'getNextNoSurat'])->name('surat.next-no-surat');
    Route::get('/surats/create', [AdminSuratController::class, 'create'])->name('surat.create');
    Route::post('/surats', [AdminSuratController::class, 'store'])->name('surat.store');
    Route::get('/surats/{surat}', [AdminSuratController::class, 'show'])->name('surat.show');

    Route::post('/surats/{surat}/save-html', [AdminSuratController::class, 'saveHtml'])->name('surat.save-html');
    Route::post('/surats/{surat}/reset-html', [AdminSuratController::class, 'resetHtml'])->name('surat.reset-html');
    Route::get('/surats/{surat}/download', [AdminSuratController::class, 'downloadPdf'])->name('surat.download');
    Route::post('/surats/{surat}/preview-email', [AdminSuratController::class, 'previewEmail'])->name('surat.preview-email');
    Route::post('/surats/{surat}/send-email', [AdminSuratController::class, 'sendEmail'])->name('surat.send-email');
    Route::put('/surats/{surat}', [AdminSuratController::class, 'update'])->name('surat.update');
    Route::post('/surats/{surat}/approve', [AdminSuratController::class, 'approve'])->name('surat.approve');
    Route::post('/surats/{surat}/reject', [AdminSuratController::class, 'reject'])->name('surat.reject');
    Route::delete('/surats/{surat}', [AdminSuratController::class, 'destroy'])->name('surat.destroy');

    // Impersonate
    Route::get('/impersonate/{type}/{id}', [ImpersonateController::class, 'loginAs'])->name('impersonate');
});

// =============================================
// SHARED APPROVAL SYSTEM ROUTES (Admin & Dosen)
// =============================================
Route::middleware(['auth:admin,dosen', 'notifications'])->prefix('admin')->name('admin.')->group(function () {
    // Surat Role Management (Admin only usually, but let's keep it here for simplicity OR protect it in controller)
    Route::resource('surat-role', App\Http\Controllers\SuratRoleController::class);
    Route::post('surat-role/{suratRole}/toggle-status', [App\Http\Controllers\SuratRoleController::class, 'toggleStatus'])
        ->name('surat-role.toggle-status');
    
    // Approval Dashboard & Actions
    Route::get('approval/dashboard', [App\Http\Controllers\ApprovalController::class, 'dashboard'])
        ->name('approval.dashboard');
        
    // Shared Surat Preview
    Route::get('/surats/{surat}/preview', [App\Http\Controllers\AdminSuratController::class, 'previewPdf'])->name('surat.preview');
    Route::get('/surats/{surat}/preview-html/{template?}', [App\Http\Controllers\AdminSuratController::class, 'previewHtml'])->name('surat.preview-html');
    
    Route::get('approval/{approval}', [App\Http\Controllers\ApprovalController::class, 'show'])
        ->name('approval.show');
    
    Route::post('approval/{approval}/approve', [App\Http\Controllers\ApprovalController::class, 'approve'])
        ->name('approval.approve');
    
    Route::post('approval/{approval}/reject', [App\Http\Controllers\ApprovalController::class, 'reject'])
        ->name('approval.reject');
    
    Route::get('surat/{surat}/approval-history', [App\Http\Controllers\ApprovalController::class, 'history'])
        ->name('surat.approval-history');

    // PDF Stamping (E-Signature style)
    Route::get('approval-stamping', [App\Http\Controllers\ApprovalStampingController::class, 'index'])
        ->name('approval.stamping.index');
    Route::get('approval-stamping/{approval}', [App\Http\Controllers\ApprovalStampingController::class, 'show'])
        ->name('approval.stamping.show');
    Route::post('/approval-stamping/{approval}/stamp', [App\Http\Controllers\ApprovalStampingController::class, 'stamp'])
        ->name('approval.stamping.process');
    // Comment on Surat
    Route::post('surat/{surat}/comment', [App\Http\Controllers\SuratCommentController::class, 'store'])->name('surat.comment.store');

    Route::post('/surat/{surat}/bulk-stamp', [App\Http\Controllers\ApprovalStampingController::class, 'bulkStamp'])
        ->name('approval.stamping.bulk');

    // Comment on Seminar
    Route::post('/seminar/{seminar}/comment', [App\Http\Controllers\SeminarCommentController::class, 'store'])->name('seminar.comment.store');
});

// Protected routes for dosen
Route::middleware(['auth:dosen', 'notifications'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DosenController::class, 'dashboard'])->name('dashboard');

    // Profile routes for dosen
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/foto', [App\Http\Controllers\ProfileController::class, 'destroyFoto'])->name('profile.destroy-foto');
    Route::get('/change-password', [App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/change-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('change-password.update');

    // Signature routes
    Route::get('/signature/{seminarId}/{evaluatorType}', [App\Http\Controllers\SignatureController::class, 'showSignatureForm'])->name('signature.form');
    Route::post('/signature/{seminarId}/{evaluatorType}', [App\Http\Controllers\SignatureController::class, 'storeSignature'])->name('signature.store');
    Route::get('/signature/{seminarId}/{evaluatorType}/get', [App\Http\Controllers\SignatureController::class, 'getSignature'])->name('signature.get');

    // Evaluasi routes
    Route::get('/evaluasi', [App\Http\Controllers\DosenController::class, 'evaluasiIndex'])->name('evaluasi.index');
    
    // Manage seminar routes
    Route::get('/manage-seminar', [App\Http\Controllers\DosenController::class, 'manageSeminarIndex'])->name('manage-seminar.index');
    
    // Profil mahasiswa route (read-only)
    Route::get('/profil-mahasiswa', [App\Http\Controllers\DosenController::class, 'mahasiswaIndex'])->name('mahasiswa.index');

    // Nilai input route
    Route::get('/nilai/{seminar}', [App\Http\Controllers\NilaiController::class, 'showInputForm'])->name('nilai.input');
    Route::post('/nilai/{seminar}', [App\Http\Controllers\NilaiController::class, 'storeNilai'])->name('nilai.store');

    // Surat request routes
    Route::get('/surat', [DosenSuratController::class, 'index'])->name('surat.index');
    Route::get('/surat/create', [DosenSuratController::class, 'create'])->name('surat.create');
    Route::get('/surat/{surat}', [DosenSuratController::class, 'show'])->whereNumber('surat')->name('surat.show');
    Route::get('/surat/{surat}/download', [DosenSuratController::class, 'downloadPdf'])->whereNumber('surat')->name('surat.download');
    Route::get('/surat/{surat}/preview-html/{template?}', [DosenSuratController::class, 'previewHtml'])->whereNumber('surat')->name('surat.preview-html');
    Route::get('/surat/{surat}/download-html', [DosenSuratController::class, 'downloadHtml'])->whereNumber('surat')->name('surat.download-html');
    Route::put('/surat/{surat}', [DosenSuratController::class, 'update'])->whereNumber('surat')->name('surat.update');
    Route::delete('/surat/{surat}', [DosenSuratController::class, 'destroy'])->whereNumber('surat')->name('surat.destroy');
    Route::post('/surat', [DosenSuratController::class, 'store'])->name('surat.store');

    // GDrive management routes (read-only for dosen)
    Route::get('/gdrive', [App\Http\Controllers\GDriveController::class, 'index'])->name('gdrive.index');

    // Notifications
    // Notifications (Index removed)
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markNotificationAsRead'])->name('notifications.markNotificationAsRead');
});

// Protected routes for mahasiswa
Route::middleware(['auth:mahasiswa', 'notifications'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('mahasiswa.dashboard');
    })->name('dashboard');

    Route::get('/dosen', [App\Http\Controllers\MahasiswaDosenController::class, 'index'])->name('dosen.index');

    // Profile routes for mahasiswa
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/foto', [App\Http\Controllers\ProfileController::class, 'destroyFoto'])->name('profile.destroy-foto');
    Route::get('/change-password', [App\Http\Controllers\ProfileController::class, 'showChangePasswordForm'])->name('change-password');
    Route::put('/change-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('change-password.update');

    // Seminar registration routes
    Route::get('/seminar', [App\Http\Controllers\SeminarRegistrationController::class, 'index'])->name('seminar.index');
    Route::get('/seminar/register', [App\Http\Controllers\SeminarRegistrationController::class, 'showRegistrationForm'])->name('seminar.register');
    Route::post('/seminar', [App\Http\Controllers\SeminarRegistrationController::class, 'store'])->name('seminar.store');
    Route::get('/seminar/{seminar}', [App\Http\Controllers\SeminarRegistrationController::class, 'show'])->whereNumber('seminar')->name('seminar.show');
    Route::get('/seminar/files/{path}', [App\Http\Controllers\SeminarRegistrationController::class, 'showFile'])->where('path', '.*')->name('seminar.files.show');
    Route::get('/seminar/{seminar}/edit', [App\Http\Controllers\SeminarRegistrationController::class, 'edit'])->name('seminar.edit');
    Route::put('/seminar/{seminar}', [App\Http\Controllers\SeminarRegistrationController::class, 'update'])->name('seminar.update');
    Route::put('/seminar/{seminar}/cancel', [App\Http\Controllers\SeminarRegistrationController::class, 'cancel'])->name('seminar.cancel');
    Route::post('/seminar/{seminar}/comment', [App\Http\Controllers\SeminarCommentController::class, 'store'])->name('seminar.comment.store');

    // Surat
    Route::get('/surat', [App\Http\Controllers\MahasiswaSuratController::class, 'index'])->name('surat.index');
    Route::get('/surat/create', [App\Http\Controllers\MahasiswaSuratController::class, 'create'])->name('surat.create');
    Route::post('/surat', [App\Http\Controllers\MahasiswaSuratController::class, 'store'])->name('surat.store');
    Route::get('/surat/{surat}', [App\Http\Controllers\MahasiswaSuratController::class, 'show'])->whereNumber('surat')->name('surat.show');
    Route::get('/surat/{surat}/download', [App\Http\Controllers\MahasiswaSuratController::class, 'downloadPdf'])->whereNumber('surat')->name('surat.download');
    Route::get('/surat/{surat}/preview-html/{template?}', [App\Http\Controllers\MahasiswaSuratController::class, 'previewHtml'])->whereNumber('surat')->name('surat.preview-html');
    Route::get('/surat/{surat}/download-html', [App\Http\Controllers\MahasiswaSuratController::class, 'downloadHtml'])->whereNumber('surat')->name('surat.download-html');
    Route::delete('/surat/{surat}', [App\Http\Controllers\MahasiswaSuratController::class, 'destroy'])->whereNumber('surat')->name('surat.destroy');
    Route::post('/surat/{surat}/comment', [App\Http\Controllers\SuratCommentController::class, 'store'])->name('surat.comment.store');

    // Notifications
    // Notifications (Index removed)
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markNotificationAsRead'])->name('notifications.markNotificationAsRead');
});
