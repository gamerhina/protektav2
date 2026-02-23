<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Surat;

class SuratSubmittedNotification extends Notification
{
    use Queueable;

    public $surat;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat)
    {
        $this->surat = $surat;
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
        $pemohon = $this->surat->pemohon_type === 'mahasiswa' 
            ? ($this->surat->pemohonMahasiswa->nama ?? 'N/A')
            : ($this->surat->pemohonDosen->nama ?? 'N/A');

        $pemohonType = $this->surat->pemohon_type === 'mahasiswa' ? 'Mahasiswa' : 'Dosen';

        return (new MailMessage)
            ->subject('Pengajuan Surat Baru - ' . ($this->surat->jenis->nama ?? 'N/A'))
            ->greeting('Halo Admin,')
            ->line($pemohonType . ' baru saja mengajukan permohonan surat dan menunggu proses Anda.')
            ->line('**Detail Permohonan:**')
            ->line('Jenis Surat: ' . ($this->surat->jenis->nama ?? 'N/A'))
            ->line('Pemohon: ' . $pemohon)
            ->line('Nomor Surat: ' . ($this->surat->no_surat ?? 'Belum ada'))
            ->line('Tanggal Surat: ' . ($this->surat->tanggal_surat ? $this->surat->tanggal_surat->translatedFormat('d F Y') : 'N/A'))
            ->line('Perihal: ' . ($this->surat->perihal ?? '-'))
            ->line('Tujuan: ' . ($this->surat->tujuan ?? '-'))
            ->action('Lihat Detail', route('admin.surat.show', $this->surat))
            ->line('Mohon segera melakukan review dan proses permohonan ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $pemohon = $this->surat->pemohon_type === 'mahasiswa' 
            ? ($this->surat->pemohonMahasiswa->nama ?? 'N/A')
            : ($this->surat->pemohonDosen->nama ?? 'N/A');

        return [
            'surat_id' => $this->surat->id,
            'jenis_surat' => $this->surat->jenis->nama ?? null,
            'pemohon' => $pemohon,
            'pemohon_type' => $this->surat->pemohon_type,
            'no_surat' => $this->surat->no_surat,
            'status' => $this->surat->status,
            'message' => 'Pengajuan surat baru dari ' . $pemohon,
            'action_url' => route('admin.surat.show', $this->surat),
        ];
    }
}
