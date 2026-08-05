<?php

namespace App\Notifications\Channels;

use App\Models\LearnerNotification;
use Illuminate\Notifications\Notification;

/**
 * Writes to this app's custom `notifications` table (LearnerNotification),
 * which has a fixed enum `type` plus `title`/`message`/`reference_id` columns.
 * Laravel's built-in 'database' channel assumes its own generic polymorphic
 * schema (uuid id, notifiable_type/id, json data, read_at) and writes the
 * notification's fully-qualified class name into `type`, which is incompatible
 * with the NotificationType enum column here — that combination throws.
 */
class LearnerDatabaseChannel
{
    public function send(object $notifiable, Notification $notification): LearnerNotification
    {
        $data = $notification->toLearnerDatabase($notifiable);

        return $notifiable->notifications()->create([
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'reference_id' => $data['reference_id'] ?? null,
        ]);
    }
}
