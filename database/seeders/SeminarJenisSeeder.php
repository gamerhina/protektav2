<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeminarJenis;

class SeminarJenisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            [
                'kode' => 'SP',
                'nama' => 'Seminar Proposal',
                'keterangan' => 'Seminar Proposal Tugas Akhir/Skripsi',
            ],
            [
                'kode' => 'SH',
                'nama' => 'Seminar Hasil',
                'keterangan' => 'Seminar Hasil Tugas Akhir/Skripsi',
            ],
            [
                'kode' => 'US',
                'nama' => 'Ujian Skripsi',
                'keterangan' => 'Ujian Skripsi dengan penilaian detail per komponen',
            ],
        ];

        foreach ($defaults as $jenis) {
            SeminarJenis::updateOrCreate(
                ['kode' => $jenis['kode']],
                [
                    'nama' => $jenis['nama'],
                    'keterangan' => $jenis['keterangan'],
                ]
            );
        }
    }
}
