<?php
require_once 'vendor/autoload.php';

// --- KONFIGURASI ---
// Lokasi file JSON credential Anda
$keyFile = 'credentials.json';
// Nama file DOCX yang mau diconvert (lokal di hosting)
$inputFile = 'surat_lamaran.docx';
// Nama file PDF hasil output
$outputFile = 'hasil_surat.pdf';

try {
    // 1. Setup Client Google
    $client = new Google\Client();
    $client->setAuthConfig($keyFile);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    // 2. Siapkan Metadata untuk Upload
    // Kita set mimeType tujuannya 'application/vnd.google-apps.document'
    // Ini ajaibnya: Google akan otomatis convert DOCX jadi Google Docs saat upload
    $fileMetadata = new Google\Service\Drive\DriveFile(array(
        'name' => 'Temp_Convert_' . time(), // Nama sementara di Drive
        'mimeType' => 'application/vnd.google-apps.document'
    ));

    // Baca file lokal
    $content = file_get_contents($inputFile);

    // 3. Upload File
    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Tipe file asal (DOCX)
        'uploadType' => 'multipart',
        'fields' => 'id'
    ));

    $fileId = $file->id;
    // echo "File berhasil diupload dengan ID: " . $fileId . "<br>";

    // 4. Export (Download) sebagai PDF
    // Kita minta Google export file ID tersebut dengan mimeType PDF
    $response = $service->files->export($fileId, 'application/pdf', array(
        'alt' => 'media'
    ));

    // Ambil konten PDF
    $pdfContent = $response->getBody()->getContents();

    // 5. Simpan ke Hosting
    file_put_contents($outputFile, $pdfContent);
    echo "Sukses! File PDF tersimpan sebagai: " . $outputFile;

    // 6. Bersih-bersih (Hapus file temp di Google Drive)
    // Penting supaya Drive service account tidak penuh
    $service->files->delete($fileId);

} catch (Exception $e) {
    echo "Terjadi Error: " . $e->getMessage();
}
?>
