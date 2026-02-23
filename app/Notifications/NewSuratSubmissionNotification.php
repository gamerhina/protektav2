<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSuratSubmissionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $surat;

    /**
     * Create a new notification instance.
     */
    public function __construct($surat)
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
        return ['database']; // Default to database only for now to avoid mail config issues
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permohonan Surat Baru: ' . $this->surat->jenis->nama)
            ->line('Ada permohonan surat baru yang perlu ditinjau.')
            ->line('Pemohon: ' . ($this->surat->pemohonDosen->nama ?? $this->surat->pemohonMahasiswa->nama ?? 'Unknown'))
            ->line('Jenis: ' . $this->surat->jenis->nama)
            ->action('Lihat Surat', route('admin.surat.show', $this->surat->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'key' => 'new_surat_submission',
            'title' => 'Permohonan Surat Baru',
            'message' => 'Permohonan dari ' . ($this->surat->pemohonDosen->nama ?? $this->surat->pemohonMahasiswa->nama ?? 'Unknown'),
            'action_url' => route('admin.surat.show', $this->surat->id),
            'surat_id' => $this->surat->id,
        ];
    }
}
