<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateFilePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-file-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating Dosen photos...');
        \App\Models\Dosen::where('foto', 'like', 'foto-dosen/%')->get()->each(function ($item) {
            $item->foto = str_replace('foto-dosen/', 'photos/dosen/', $item->foto);
            $item->save();
        });

        $this->info('Updating Mahasiswa photos...');
        \App\Models\Mahasiswa::where('foto', 'like', 'foto-mahasiswa/%')->get()->each(function ($item) {
            $item->foto = str_replace('foto-mahasiswa/', 'photos/mahasiswa/', $item->foto);
            $item->save();
        });

        $this->info('Updating Admin photos...');
        \App\Models\Admin::where('foto', 'like', 'foto-admin/%')->get()->each(function ($item) {
            $item->foto = str_replace('foto-admin/', 'photos/admin/', $item->foto);
            $item->save();
        });

        $this->info('Updating Seminar documents...');
        \App\Models\Seminar::get()->each(function ($item) {
            $changed = false;
            $berkas = $item->berkas_syarat;
            if (is_array($berkas)) {
                foreach ($berkas as $key => $val) {
                    if (is_string($val) && str_contains($val, 'seminar-berkas/')) {
                        $berkas[$key] = str_replace('seminar-berkas/', 'documents/seminar/', $val);
                        $changed = true;
                    }
                }
            } elseif (is_string($berkas) && str_contains($berkas, 'seminar-berkas/')) {
                 // Handle legacy string format if exists
                 $berkas = str_replace('seminar-berkas/', 'documents/seminar/', $berkas);
                 $changed = true;
            }

            if ($changed) {
                $item->berkas_syarat = $berkas;
                $item->save();
            }
        });
        
        $this->info('Updating Surat attachments and generated files...');
        \App\Models\Surat::get()->each(function ($item) {
            $changed = false;
            $data = $item->data;
            
            // Helper to recurse
            $updatePaths = function(&$value) use (&$updatePaths, &$changed) {
                if (is_array($value)) {
                    foreach ($value as $k => &$v) {
                        $updatePaths($v);
                    }
                } elseif (is_string($value)) {
                     if (str_contains($value, 'surat-attachments/')) {
                         $value = str_replace('surat-attachments/', 'documents/surat/attachments/', $value);
                         $changed = true;
                     } elseif (str_contains($value, 'generated-surats/')) {
                         $value = str_replace('generated-surats/', 'documents/surat/generated/', $value);
                         $changed = true;
                     }
                }
            };

            if (is_array($data)) {
                $updatePaths($data);
            }
            
            // Also check 'generated_file_path' if it exists in data or as a column (it's not a standard column in migration usually but checked in controller)
            
            if ($changed) {
                $item->data = $data;
                $item->save();
            }
        });

        $this->info('File paths updated successfully.');
    }
}
