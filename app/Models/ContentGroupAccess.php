<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentGroupAccess extends Model
{
    protected $table = 'content_group_access';

    protected $fillable = ['content_id', 'group_id'];

    public function content(): BelongsTo
    {
        return $this->belongsTo(ModuleContent::class, 'content_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LearnerGroup::class, 'group_id');
    }
}
