<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSuratCommentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $surat;
    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct($surat, $sender)
    {
        $this->surat = $surat;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Komentar Baru pada Surat No: ' . ($this->surat->no_surat ?? '-'))
            ->line('Ada komentar baru dari ' . $this->sender->nama)
            ->line('Pesan: ' . \Illuminate\Support\Str::limit($this->surat->comments->first()->message ?? '', 100))
            ->action('Lihat Surat', $this->getActionUrl($notifiable));
    }

    private function getActionUrl($notifiable): string
    {
        if ($notifiable instanceof \App\Models\Admin) {
            return route('admin.surat.show', $this->surat->id) . '#discussion';
        }

        if ($notifiable instanceof \App\Models\Dosen) {
            // Check if this dosen is an approver on this surat
            $approval = $this->surat->approvals()
                ->where('dosen_id', $notifiable->id)
                ->first();

            if ($approval && $this->surat->jenis?->is_uploaded) {
                // Dosen approver → go to stamping page
                return route('admin.approval.stamping.show', $approval);
            }

            // Dosen pemohon → go to surat show
            return route('dosen.surat.show', $this->surat->id) . '#discussion';
        }

        if ($notifiable instanceof \App\Models\Mahasiswa) {
            return route('mahasiswa.surat.show', $this->surat->id) . '#discussion';
        }

        return route('login');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'key' => 'new_surat_comment',
            'title' => 'Komentar Baru',
            'message' => $this->sender->nama . ': ' . \Illuminate\Support\Str::limit($this->surat->comments->first()->message ?? 'Pesan baru', 50),
            'action_url' => $this->getActionUrl($notifiable),
            'surat_id' => $this->surat->id,
        ];
    }
}
