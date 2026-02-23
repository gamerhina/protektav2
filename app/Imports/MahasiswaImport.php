<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MahasiswaImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validasi data
            if (empty($row['nama']) || empty($row['npm']) || empty($row['email'])) {
                continue; // Lewati baris jika data penting kosong
            }

            // Cari pembimbing akademik berdasarkan NIP jika disediakan
            $paId = null;
            $paNip = $row['pembimbing_akademik_nip'] ?? null;
            if ($paNip) {
                $pa = \App\Models\Dosen::where('nip', $paNip)->first();
                if ($pa) {
                    $paId = $pa->id;
                }
            }

            // Cek apakah mahasiswa sudah ada berdasarkan NPM
            $mahasiswa = Mahasiswa::where('npm', $row['npm'])->first();

            if ($mahasiswa) {
                // Jika sudah ada, update data
                $mahasiswa->update([
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'hp' => $row['hp'] ?? null,
                    'wa' => $row['wa'] ?? null,
                    'pembimbing_akademik_id' => $paId ?: $mahasiswa->pembimbing_akademik_id,
                ]);
            } else {
                // Jika belum ada, buat baru
                $password = !empty($row['password']) ? $row['password'] : $row['npm'];
                Mahasiswa::create([
                    'nama' => $row['nama'],
                    'npm' => $row['npm'],
                    'email' => $row['email'],
                    'hp' => $row['hp'] ?? null,
                    'wa' => $row['wa'] ?? null,
                    'pembimbing_akademik_id' => $paId,
                    'password' => Hash::make($password), // Gunakan password dari file jika ada, atau NPM sebagai default
                    'remember_token' => Str::random(60),
                ]);
            }
        }
    }
}