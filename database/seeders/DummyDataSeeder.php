<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\SeminarJenis;
use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Models\DosenMahasiswa;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample dosen
        $dosen1 = Dosen::updateOrCreate(
            ['email' => 'ahmad.fauzi@example.com'],
            [
                'nama' => 'Dr. Ahmad Fauzi',
                'nip' => '198001012005011001',
                'password' => bcrypt('password'),
            ]
        );

        $dosen2 = Dosen::updateOrCreate(
            ['email' => 'siti.aminah@example.com'],
            [
                'nama' => 'Prof. Siti Aminah',
                'nip' => '197505152000032001',
                'password' => bcrypt('password'),
            ]
        );

        $dosen3 = Dosen::updateOrCreate(
            ['email' => 'budi.santoso@example.com'],
            [
                'nama' => 'Dr. Budi Santoso',
                'nip' => '198208082010011002',
                'password' => bcrypt('password'),
            ]
        );

        // Create sample mahasiswa
        $mhs1 = Mahasiswa::updateOrCreate(
            ['email' => 'rizki@example.com'],
            [
                'nama' => 'Muhammad Rizki',
                'npm' => '2021103021',
                'password' => bcrypt('password'),
            ]
        );

        $mhs2 = Mahasiswa::updateOrCreate(
            ['email' => 'dewi@example.com'],
            [
                'nama' => 'Dewi Anggraini',
                'npm' => '2021103022',
                'password' => bcrypt('password'),
            ]
        );

        $mhs3 = Mahasiswa::updateOrCreate(
            ['email' => 'budi@example.com'],
            [
                'nama' => 'Budi Prasetyo',
                'npm' => '2021103023',
                'password' => bcrypt('password'),
            ]
        );

        // Create sample seminar jenis
        $jenis1 = SeminarJenis::updateOrCreate(
            ['kode' => 'SP'],
            [
                'nama' => 'Seminar Proposal',
                'keterangan' => 'Seminar Proposal Tugas Akhir/Skripsi',
            ]
        );

        $jenis2 = SeminarJenis::updateOrCreate(
            ['kode' => 'SH'],
            [
                'nama' => 'Seminar Hasil',
                'keterangan' => 'Seminar Hasil Tugas Akhir/Skripsi',
            ]
        );

        $jenis3 = SeminarJenis::updateOrCreate(
            ['kode' => 'US'],
            [
                'nama' => 'Ujian Skripsi',
                'keterangan' => 'Ujian Skripsi dengan penilaian detail per komponen',
            ]
        );

        // Create sample seminars
        $seminar1 = Seminar::updateOrCreate(
            ['no_surat' => '001/SP/SKR/2025'],
            [
                'mahasiswa_id' => $mhs1->id,
                'seminar_jenis_id' => $jenis1->id,
                'judul' => 'Implementasi Sistem Informasi Berbasis Web untuk Manajemen Proyek',
                'tanggal' => now()->subDays(7),
                'waktu_mulai' => '09:00:00',
                'lokasi' => 'Ruang Sidang Lt. 2',
                'p1_dosen_id' => $dosen1->id, // Pembimbing 1
                'p2_dosen_id' => $dosen2->id, // Pembimbing 2
                'pembahas_dosen_id' => $dosen3->id, // Pembahas
                'berkas_syarat' => json_encode(['surat_keterangan_dospem.pdf', 'krs.pdf']),
                'status' => 'selesai',
            ]
        );

        $seminar2 = Seminar::updateOrCreate(
            ['no_surat' => '002/SP/SKR/2025'],
            [
                'mahasiswa_id' => $mhs2->id,
                'seminar_jenis_id' => $jenis1->id,
                'judul' => 'Analisis dan Perancangan Aplikasi Mobile untuk E-Learning',
                'tanggal' => now()->subDays(5),
                'waktu_mulai' => '10:00:00',
                'lokasi' => 'Ruang Sidang Lt. 1',
                'p1_dosen_id' => $dosen2->id, // Pembimbing 1
                'p2_dosen_id' => $dosen1->id, // Pembimbing 2
                'pembahas_dosen_id' => $dosen3->id, // Pembahas
                'berkas_syarat' => json_encode(['surat_keterangan_dospem.pdf', 'krs.pdf']),
                'status' => 'diajukan',
            ]
        );

        $seminar3 = Seminar::updateOrCreate(
            ['no_surat' => '003/SH/SKR/2025'],
            [
                'mahasiswa_id' => $mhs3->id,
                'seminar_jenis_id' => $jenis2->id,
                'judul' => 'Optimasi Algoritma Machine Learning untuk Prediksi Cuaca',
                'tanggal' => now()->addDays(3),
                'waktu_mulai' => '13:00:00',
                'lokasi' => 'Ruang Sidang Lt. 3',
                'p1_dosen_id' => $dosen1->id, // Pembimbing 1
                'p2_dosen_id' => $dosen3->id, // Pembimbing 2
                'pembahas_dosen_id' => $dosen2->id, // Pembahas
                'berkas_syarat' => json_encode(['surat_keterangan_dospem.pdf', 'krs.pdf']),
                'status' => 'disetujui',
            ]
        );

        // Create dosen-mahasiswa relationships
        DosenMahasiswa::updateOrCreate(
            ['dosen_id' => $dosen1->id, 'mahasiswa_id' => $mhs1->id, 'jenis_pembimbing' => 'p1'],
            []
        );

        DosenMahasiswa::updateOrCreate(
            ['dosen_id' => $dosen2->id, 'mahasiswa_id' => $mhs1->id, 'jenis_pembimbing' => 'p2'],
            []
        );

        DosenMahasiswa::updateOrCreate(
            ['dosen_id' => $dosen2->id, 'mahasiswa_id' => $mhs2->id, 'jenis_pembimbing' => 'p1'],
            []
        );

        DosenMahasiswa::updateOrCreate(
            ['dosen_id' => $dosen1->id, 'mahasiswa_id' => $mhs2->id, 'jenis_pembimbing' => 'p2'],
            []
        );

        // Create sample nilai
        SeminarNilai::updateOrCreate(
            ['seminar_id' => $seminar1->id, 'dosen_id' => $dosen1->id, 'jenis_penilai' => 'p1'],
            [
                'nilai_angka' => 85.00,
                'catatan' => 'Presentasi bagus, pembahasan perlu ditingkatkan'
            ]
        );

        SeminarNilai::updateOrCreate(
            ['seminar_id' => $seminar1->id, 'dosen_id' => $dosen2->id, 'jenis_penilai' => 'p2'],
            [
                'nilai_angka' => 87.50,
                'catatan' => 'Tema bagus, metodologi cukup kuat'
            ]
        );

        SeminarNilai::updateOrCreate(
            ['seminar_id' => $seminar1->id, 'dosen_id' => $dosen3->id, 'jenis_penilai' => 'pembahas'],
            [
                'nilai_angka' => 82.00,
                'catatan' => 'Topik relevan dengan perkembangan teknologi saat ini'
            ]
        );
    }
}
