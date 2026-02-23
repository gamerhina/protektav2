<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailConnection extends Command
{
    protected $signature = 'mail:test {email}';
    protected $description = 'Test the SMTP mail connection';

    public function handle()
    {
        $to = $this->argument('email');
        $this->info("Menyiapkan pengiriman email tes ke: {$to}...");

        try {
            Mail::raw('Halo! Ini adalah email percobaan dari aplikasi Protekta Unila menggunakan server SMTP Hostinger. Jika Anda menerima ini, berarti konfigurasi sudah BERHASIL.', function ($message) use ($to) {
                $message->to($to)
                        ->subject('Tes Koneksi Email Protekta V2');
            });

            $this->info('✅ Berhasil! Email telah dikirim. Silakan cek Inbox atau folder Spam Anda.');
        } catch (\Exception $e) {
            $this->error('❌ Gagal mengirim email!');
            $this->error('Pesan Error: ' . $e->getMessage());
        }
    }
}
