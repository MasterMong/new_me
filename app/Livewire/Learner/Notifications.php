<?php

namespace App\Livewire\Learner;

use App\Models\LearnerNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('การแจ้งเตือน')]
class Notifications extends Component
{
    public function markAsRead(LearnerNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true]);
        }
    }

    public function markAllAsRead()
    {
        LearnerNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function delete(LearnerNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->delete();
        }
    }

    public function render()
    {
        $notifications = LearnerNotification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.learner.notifications', [
            'notifications' => $notifications,
        ]);
    }
}
