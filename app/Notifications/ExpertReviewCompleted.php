<?php

namespace App\Notifications;

use App\Models\ExpertReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpertReviewCompleted extends Notification
{
    use Queueable;

    public $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(ExpertReview $review)
    {
        $this->review = $review;
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
        $statusText = $this->review->status->value === 'passed' ? 'ผ่าน' : 'รอแก้ไข';

        return (new MailMessage)
            ->subject('ผลการตรวจใบงาน (Assignment Review Results)')
            ->greeting('สวัสดีคุณ '.$notifiable->fullName())
            ->line('ผู้เชี่ยวชาญได้ทำการตรวจใบงานสำหรับโมดูล: '.$this->review->attempt->assessment->module->title.' เรียบร้อยแล้ว')
            ->line('ผลการประเมิน: **'.$statusText.'**')
            ->action('ดูผลการเรียน', route('learn.courses.show', $this->review->attempt->assessment->course_id))
            ->line('คุณสามารถเข้าสู่ระบบเพื่อดูรายละเอียดข้อเสนอแนะเพิ่มเติมได้');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'module_title' => $this->review->attempt->assessment->module->title,
            'status' => $this->review->status,
            'message' => 'ผู้เชี่ยวชาญได้ทำการตรวจใบงานสำหรับโมดูล: '.$this->review->attempt->assessment->module->title,
        ];
    }
}
