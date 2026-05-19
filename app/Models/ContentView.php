<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    protected $fillable = [
        'user_id', 'content_id', 'watch_duration_sec', 'is_completed',
        'last_position_sec', 'viewed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(ModuleContent::class, 'content_id');
    }
}
