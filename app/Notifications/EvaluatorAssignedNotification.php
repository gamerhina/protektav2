<?php

namespace App\Notifications;

use App\Models\Seminar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluatorAssignedNotification extends Notification
{
    use Queueable;

    protected Seminar $seminar;
    protected string $role;

    /**
     * Create a new notification instance.
     */
    public function __construct(Seminar $seminar, string $role)
    {
        $this->seminar = $seminar;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $roleLabels = [
            'p1' => 'Pembimbing 1',
            'p2' => 'Pembimbing 2',
            'pembahas' => 'Penguji/Pembahas',
        ];

        $roleLabel = $roleLabels[$this->role] ?? $this->role;

        return [
            'title' => 'Penugasan Penguji Seminar',
            'message' => "Anda ditugaskan sebagai {$roleLabel} pada seminar {$this->seminar->mahasiswa->nama}",
            'seminar_id' => $this->seminar->id,
            'role' => $this->role,
            'action_url' => route('dosen.nilai.input', $this->seminar->id),
            'action_text' => 'Lihat Detail',
            'type' => 'evaluator_assigned',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $roleLabels = [
            'p1' => 'Pembimbing 1',
            'p2' => 'Pembimbing 2',
            'pembahas' => 'Penguji/Pembahas',
        ];

        $roleLabel = $roleLabels[$this->role] ?? $this->role;

        return (new MailMessage)
            ->subject('Penugasan Penguji Seminar - ' . $this->seminar->mahasiswa->nama)
            ->greeting('Yth. ' . $notifiable->nama)
            ->line('Mahasiswa: ' . $this->seminar->mahasiswa->nama)
            ->line('Tanggal: ' . ($this->seminar->tanggal?->translatedFormat('l, d F Y') ?? '-'))
            ->line('Waktu: ' . ($this->seminar->waktu_mulai ?? '-'))
            ->line('Lokasi: ' . ($this->seminar->lokasi ?? '-'))
            ->action('Lihat Detail Seminar', route('dosen.nilai.input', $this->seminar->id))
            ->line('Mohon untuk mempersiapkan penilaian dan tanda tangan.')
            ->salutation('Terima kasih');
    }
}
