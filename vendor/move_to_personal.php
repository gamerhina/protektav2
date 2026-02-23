<?php
require_once 'vendor/autoload.php';

$keyFile = 'credentials.json';
// Ganti dengan ID folder yang Anda buat di Akun Pribadi (Langkah 2)
// ID Folder ada di URL browser saat Anda membuka foldernya.
$targetFolderId = '16cXRkQE9K4-1KD8fYsmljfiIzglQ3Tp6';

try {
    $client = new Google\Client();
    $client->setAuthConfig($keyFile);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    echo "--- MEMINDAHKAN FILE PENGISI KUOTA DARI SERVICE ACCOUNT ---<br>";

    // Query: Cari SEMUA file aktif (bukan di Trash) milik Service Account
    // Perintah 'me' menunjukkan pemiliknya adalah Service Account
    $query = "trashed = false";
    $files = $service->files->listFiles([
        'q' => $query,
        'spaces' => 'drive',
        // Ambil field parents dan id untuk pemindahan
        'fields' => 'files(id, name, parents)'
    ]);

    $movedCount = 0;

    foreach ($files->getFiles() as $file) {
        $fileId = $file->id;

        // Dapatkan semua parent (folder) saat ini
        $parents = implode(',', $file->parents);

        // Pindahkan file ke folder yang dibagikan (Target Folder ID)
        $updatedFile = $service->files->update(
            $fileId,
            null,
            array(
                'addParents' => $targetFolderId, // Tambahkan folder tujuan
                'removeParents' => $parents,     // Hapus dari folder lama (Drive Service Account)
                'fields' => 'id, parents'
            )
        );
        echo "   - File dipindahkan: " . $file->name . "<br>";
        $movedCount++;
    }

    echo "---------------------------------------------------<br>";
    echo "Total file yang dipindahkan: " . $movedCount . ".<br>";
    echo "Silakan cek folder Drive pribadi Anda. Kuota Service Account seharusnya sudah kosong.";

} catch (Exception $e) {
    echo "Terjadi Error saat memindahkan file: " . $e->getMessage();
}
?>
