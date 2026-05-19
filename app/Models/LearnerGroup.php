<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearnerGroup extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(UserGroupMembership::class, 'group_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_memberships', 'group_id', 'user_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function contentAccess(): HasMany
    {
        return $this->hasMany(ContentGroupAccess::class, 'group_id');
    }
}
