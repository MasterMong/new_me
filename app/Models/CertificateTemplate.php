<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'course_id', 'template_image_url', 'name_x', 'name_y', 'date_x', 'date_y',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
