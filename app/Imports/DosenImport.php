<?php

namespace App\Imports;

use App\Models\Dosen;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DosenImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validasi data
            if (empty($row['nama']) || empty($row['nip']) || empty($row['email'])) {
                continue; // Lewati baris jika data penting kosong
            }

            // Cek apakah dosen sudah ada berdasarkan NIP
            $dosen = Dosen::where('nip', $row['nip'])->first();

            if ($dosen) {
                // Jika sudah ada, update data
                $dosen->update([
                    'nama' => $row['nama'],
                    'email' => $row['email'],
                    'hp' => $row['hp'] ?? null,
                    'wa' => $row['wa'] ?? null,
                ]);
            } else {
                // Jika belum ada, buat baru
                $password = !empty($row['password']) ? $row['password'] : $row['nip'];
                Dosen::create([
                    'nama' => $row['nama'],
                    'nip' => $row['nip'],
                    'email' => $row['email'],
                    'hp' => $row['hp'] ?? null,
                    'wa' => $row['wa'] ?? null,
                    'password' => Hash::make($password), // Gunakan password dari file jika ada, atau NIP sebagai default
                    'remember_token' => Str::random(60),
                ]);
            }
        }
    }
}