<?php

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleContent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'module_id', 'content_type', 'title', 'file_url', 'duration_minutes', 'sort_order',
    ];

    protected $casts = [
        'content_type' => ContentType::class,
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function groupAccess(): HasMany
    {
        return $this->hasMany(ContentGroupAccess::class, 'content_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ContentView::class, 'content_id');
    }
}
