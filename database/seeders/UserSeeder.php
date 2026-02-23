<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin
        $admin = Admin::firstOrCreate([
            'email' => 'admin@protekta.com',
        ], [
            'nama' => 'Admin Protekta',
            'nip' => '001',
            'email' => 'admin@protekta.com',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole('admin');

        // Create default dosens
        $dosenA = Dosen::firstOrCreate([
            'email' => 'dosen.a@protekta.com',
        ], [
            'nama' => 'Dosen A',
            'nip' => '123',
            'email' => 'dosen.a@protekta.com',
            'password' => Hash::make('dosen123'),
        ]);
        $dosenA->assignRole('dosen');

        $dosenB = Dosen::firstOrCreate([
            'email' => 'dosen.b@protekta.com',
        ], [
            'nama' => 'Dosen B',
            'nip' => '456',
            'email' => 'dosen.b@protekta.com',
            'password' => Hash::make('dosen123'),
        ]);
        $dosenB->assignRole('dosen');

        // Create dosen C with same NIP as dosen A (as per requirements)
        $dosenC = Dosen::firstOrCreate([
            'email' => 'dosen.c@protekta.com',
        ], [
            'nama' => 'Dosen C',
            'nip' => '123',
            'email' => 'dosen.c@protekta.com',
            'password' => Hash::make('dosen123'),
        ]);
        $dosenC->assignRole('dosen');

        // Create default mahasiswas
        $mhsX = Mahasiswa::firstOrCreate([
            'email' => 'mahasiswa.x@protekta.com',
        ], [
            'nama' => 'Mahasiswa X',
            'npm' => '1001',
            'email' => 'mahasiswa.x@protekta.com',
            'password' => Hash::make('mahasiswa123'),
        ]);
        $mhsX->assignRole('mahasiswa');

        $mhsY = Mahasiswa::firstOrCreate([
            'email' => 'mahasiswa.y@protekta.com',
        ], [
            'nama' => 'Mahasiswa Y',
            'npm' => '1002',
            'email' => 'mahasiswa.y@protekta.com',
            'password' => Hash::make('mahasiswa123'),
        ]);
        $mhsY->assignRole('mahasiswa');
    }
}
