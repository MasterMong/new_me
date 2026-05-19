<?php

namespace App\Notifications;

use App\Models\TestAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentSubmitted extends Notification
{
    use Queueable;

    public $attempt;

    /**
     * Create a new notification instance.
     */
    public function __construct(TestAttempt $attempt)
    {
        $this->attempt = $attempt;
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
        return (new MailMessage)
            ->subject('มีใบงานใหม่รอตรวจ (New Assignment Submission)')
            ->greeting('เรียน ผู้เชี่ยวชาญ')
            ->line('ผู้เรียน '.$this->attempt->user->fullName().' ได้ส่งใบงานใหม่สำหรับโมดูล: '.$this->attempt->assessment->module->title)
            ->action('ตรวจสอบใบงาน', route('expert.submissions.review', $this->attempt->id))
            ->line('กรุณาเข้าสู่ระบบเพื่อตรวจสอบและให้คะแนนใบงานนี้');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attempt_id' => $this->attempt->id,
            'module_title' => $this->attempt->assessment->module->title,
            'user_name' => $this->attempt->user->fullName(),
            'message' => 'ผู้เรียน '.$this->attempt->user->fullName().' ได้ส่งใบงานใหม่',
        ];
    }
}
