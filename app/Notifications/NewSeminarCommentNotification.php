<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSeminarCommentNotification extends Notification
{
    use Queueable;

    public $seminar;
    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct($seminar, $sender)
    {
        $this->seminar = $seminar;
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
            ->subject('Diskusi Baru pada Seminar - ' . ($this->sender->nama ?? ''))
            ->line('Ada komentar baru dari ' . $this->sender->nama)
            ->line('Pesan: ' . \Illuminate\Support\Str::limit($this->seminar->comments->first()->message ?? '', 100))
            ->action('Lihat Seminar', route($this->getRouteName($notifiable), $this->seminar->id));
    }

    private function getRouteName($notifiable)
    {
        if ($notifiable instanceof \App\Models\Admin) return 'admin.seminar.edit';
        if ($notifiable instanceof \App\Models\Dosen) return 'dosen.nilai.input';
        if ($notifiable instanceof \App\Models\Mahasiswa) return 'mahasiswa.seminar.show';
        return 'login';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'key' => 'new_seminar_comment',
            'title' => 'Diskusi Seminar Baru',
            'message' => $this->sender->nama . ': ' . \Illuminate\Support\Str::limit($this->seminar->comments->first()->message ?? 'Pesan baru', 50),
            'action_url' => route($this->getRouteName($notifiable), $this->seminar->id) . '#discussion',
            'seminar_id' => $this->seminar->id,
        ];
    }
}
