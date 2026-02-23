<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Surat;

class SuratStatusUpdatedNotification extends Notification
{
    use Queueable;

    public $surat;
    public $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat, string $previousStatus)
    {
        $this->surat = $surat;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'diajukan' => 'Pengajuan',
            'diproses' => 'Sedang Diproses',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
        ];

        $statusText = $statusMessages[$this->surat->status] ?? $this->surat->status;
        $pemohonName = $this->surat->pemohon->nama ?? 'Pemohon';
        
        $subject = 'Status Surat a.n. ' . $pemohonName . ' Diperbarui: ' . $statusText;

        $greeting = 'Halo, ';
        
        if ($notifiable instanceof \App\Models\Dosen) {
            $greeting .= 'Bapak/Ibu ' . $notifiable->nama;
        } elseif ($notifiable instanceof \App\Models\Mahasiswa) {
            $greeting .= $notifiable->nama;
        } else {
            $greeting .= 'Admin';
        }

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line('Status permohonan surat telah diperbarui.')
            ->line('**Detail Surat:**')
            ->line('Pemohon: ' . $pemohonName)
            ->line('Jenis Surat: ' . ($this->surat->jenis->nama ?? 'N/A'))
            ->line('Nomor Surat: ' . ($this->surat->no_surat ?? 'Belum ada'))
            ->line('Tanggal Surat: ' . ($this->surat->tanggal_surat ? $this->surat->tanggal_surat->translatedFormat('d F Y') : 'N/A'))
            ->line('Perihal: ' . ($this->surat->perihal ?? '-'))
            ->line('Tujuan: ' . ($this->surat->tujuan ?? '-'))
            ->line('')
            ->line('**Status Update:**')
            ->line('Dari: ' . ($statusMessages[$this->previousStatus] ?? $this->previousStatus))
            ->line('Menjadi: ' . $statusText);

        if ($this->surat->status === 'selesai') {
            $mailMessage->action('Lihat Detail', route('dosen.surat.show', $this->surat));
        } elseif ($notifiable instanceof \App\Models\Admin) {
            $mailMessage->action('Lihat Detail', route('admin.surat.show', $this->surat));
        }

        if ($this->surat->status === 'ditolak') {
            $mailMessage->line('Mohon menghubungi admin untuk informasi lebih lanjut.');
        } elseif ($this->surat->status === 'selesai') {
            $mailMessage->line('Surat telah selesai diproses dan dapat diunduh.');
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusMessages = [
            'diajukan' => 'Pengajuan',
            'diproses' => 'Sedang Diproses',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
        ];

        $pemohonName = $this->surat->pemohon->nama ?? 'Pemohon';
        $statusText = $statusMessages[$this->surat->status] ?? $this->surat->status;

        return [
            'surat_id' => $this->surat->id,
            'jenis_surat' => $this->surat->jenis->nama ?? null,
            'no_surat' => $this->surat->no_surat,
            'pemohon_nama' => $pemohonName,
            'previous_status' => $this->previousStatus,
            'current_status' => $this->surat->status,
            'status_text' => $statusText,
            'message' => 'Status surat a.n. ' . $pemohonName . ' diperbarui: ' . $statusText,
            'action_url' => $notifiable instanceof \App\Models\Admin 
                ? route('admin.surat.show', $this->surat)
                : ($notifiable instanceof \App\Models\Dosen 
                    ? route('dosen.surat.show', $this->surat)
                    : route('mahasiswa.surat.show', $this->surat)),
        ];
    }
}
