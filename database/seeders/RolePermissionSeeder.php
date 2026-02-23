<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create admin permissions
        $adminPermissions = [
            'admin access',
            'manage users',
            'manage dosen',
            'manage mahasiswa',
            'manage admins',
            'manage seminars',
            'manage nilai',
            'manage gdrive',
            'manage templates',
            'manage signatures',
        ];
        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create dosen permissions
        $dosenPermissions = [
            'dosen access',
            'view my seminars',
            'manage nilai',
            'manage signatures',
        ];
        foreach ($dosenPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'dosen']);
        }

        // Create mahasiswa permissions
        $mahasiswaPermissions = [
            'mahasiswa access',
            'register seminar',
            'upload berkas',
            'view my seminars',
        ];
        foreach ($mahasiswaPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'mahasiswa']);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        $dosenRole = Role::firstOrCreate(['name' => 'dosen', 'guard_name' => 'dosen']);
        $dosenRole->givePermissionTo(Permission::where('guard_name', 'dosen')->get());

        $mahasiswaRole = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'mahasiswa']);
        $mahasiswaRole->givePermissionTo(Permission::where('guard_name', 'mahasiswa')->get());
    }
}