<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\SuratApproval;

class SuratApprovalRequestNotification extends Notification
{
    use Queueable;

    protected $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct(SuratApproval $approval)
    {
        $this->approval = $approval;
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
        $surat = $this->approval->surat;
        $roleLabel = $this->approval->role_nama ?: ($this->approval->role->nama ?? 'Penyetuju');
        $pemohonNama = $surat->pemohonDosen->nama ?? $surat->pemohonMahasiswa->nama ?? 'Seseorang';

        $url = $surat->jenis->is_uploaded 
            ? route('admin.approval.stamping.show', $this->approval->id)
            : route('admin.approval.show', $this->approval->id);

        return (new MailMessage)
            ->subject('Permohonan Persetujuan Surat: ' . $surat->jenis->nama)
            ->greeting('Halo, ' . $notifiable->nama)
            ->line('Ada permohonan persetujuan surat baru yang memerlukan tindakan Anda.')
            ->line('**Jenis Surat:** ' . $surat->jenis->nama)
            ->line('**Pemohon:** ' . $pemohonNama)
            ->line('**Role Anda:** ' . $roleLabel)
            ->action('Proses Persetujuan', $url)
            ->line('Silakan klik tombol di atas untuk melihat detail dan memproses persetujuan tersebut.')
            ->line('Terima kasih!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $surat = $this->approval->surat;
        $roleLabel = $this->approval->role_nama ?: ($this->approval->role->nama ?? 'Penyetuju');
        $pemohonNama = $surat->pemohonDosen->nama ?? $surat->pemohonMahasiswa->nama ?? 'Seseorang';

        $url = $surat->jenis->is_uploaded 
            ? route('admin.approval.stamping.show', $this->approval->id)
            : route('admin.approval.show', $this->approval->id);

        return [
            'approval_id' => $this->approval->id,
            'surat_id' => $surat->id,
            'title' => 'Permohonan Persetujuan Baru',
            'message' => 'Surat ' . $surat->jenis->nama . ' dari ' . $pemohonNama . ' menunggu persetujuan Anda sebagai ' . $roleLabel,
            'type' => 'surat_approval_request',
            'action_url' => $url,
        ];
    }
}
