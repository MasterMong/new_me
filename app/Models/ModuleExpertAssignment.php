<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleExpertAssignment extends Model
{
    protected $fillable = ['module_id', 'expert_id', 'assigned_at', 'assigned_by'];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
