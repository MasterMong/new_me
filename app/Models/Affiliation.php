<?php

namespace App\Models;

use App\Enums\AffiliationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => AffiliationType::class,
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
