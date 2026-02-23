<?php
require_once __DIR__ . '/autoload.php';

$baseDir = __DIR__;
$keyFile = $baseDir . '/credentials.json';

if (!file_exists($keyFile)) {
    exit('Terjadi Error saat membersihkan: credentials.json tidak ditemukan di folder vendor.' . PHP_EOL);
}

$prefix = getenv('GDRIVE_CLEAN_PREFIX') ?: 'protekta-doc-';

try {
    $client = new Google\Client();
    $client->setAuthConfig($keyFile);
    $client->addScope(Google\Service\Drive::DRIVE);
    $service = new Google\Service\Drive($client);

    // Cari file sementara yang dibuat oleh proses konversi berdasarkan prefix nama.
    $escaped = str_replace("'", "\'", $prefix);
    $query = "name contains '" . $escaped . "' and trashed = false";

    $files = $service->files->listFiles([
        'q' => $query,
        'spaces' => 'drive',
        'fields' => 'files(id, name, createdTime)',
        'pageSize' => 1000,
    ]);

    $deletedCount = 0;

    $items = $files->getFiles();

    if (count($items) === 0) {
        echo "Tidak ada file sisa konversi dengan prefix '{$prefix}'.";
    } else {
        echo "Ditemukan " . count($items) . " file sisa konversi. Mulai menghapus..." . PHP_EOL;

        foreach ($items as $file) {
            // Hapus file
            $service->files->delete($file->id);
            echo "   - Berhasil menghapus file: " . $file->name . " (ID: " . $file->id . ")" . PHP_EOL;
            $deletedCount++;
        }
        echo PHP_EOL . "Selesai. Total file yang dihapus: " . $deletedCount . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Terjadi Error saat membersihkan: " . $e->getMessage() . PHP_EOL;
}
?>
