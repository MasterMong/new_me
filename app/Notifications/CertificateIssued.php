<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Certificate;
use App\Notifications\Channels\LearnerDatabaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification
{
    use Queueable;

    public Certificate $certificate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', LearnerDatabaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ยินดีด้วย! คุณได้รับเกียรติบัตร (Certificate Issued)')
            ->greeting('ยินดีด้วยคุณ '.$notifiable->fullName())
            ->line('คุณได้ผ่านเกณฑ์การเรียนหลักสูตร: '.$this->certificate->course->title.' เรียบร้อยแล้ว')
            ->line('เลขที่เกียรติบัตร: '.$this->certificate->certificate_number)
            ->action('ดูเกียรติบัตรของฉัน', route('learn.certificates.index'))
            ->line('ขอบคุณที่เข้าร่วมการเรียนรู้กับเรา');
    }

    /**
     * Shape written to the notifications table via LearnerDatabaseChannel.
     *
     * @return array<string, mixed>
     */
    public function toLearnerDatabase(object $notifiable): array
    {
        return [
            'type' => NotificationType::CertificateIssued,
            'title' => 'ได้รับเกียรติบัตร: '.$this->certificate->course->title,
            'message' => 'คุณได้ผ่านเกณฑ์การเรียนหลักสูตร '.$this->certificate->course->title.' และได้รับเกียรติบัตรเรียบร้อยแล้ว',
            'reference_id' => $this->certificate->id,
        ];
    }
}
