<?php

namespace App\Models;

use App\Enums\ModuleProgressStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleProgress extends Model
{
    protected $fillable = ['user_id', 'module_id', 'status', 'started_at', 'completed_at'];

    protected $casts = [
        'status' => ModuleProgressStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
