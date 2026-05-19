<?php

namespace App\Models;

use App\Enums\PrerequisiteType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModulePrerequisite extends Model
{
    protected $fillable = [
        'module_id', 'prerequisite_type', 'prerequisite_module_id',
        'prerequisite_assessment_id', 'min_score_pct',
    ];

    protected $casts = [
        'prerequisite_type' => PrerequisiteType::class,
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function prerequisiteModule(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }

    public function prerequisiteAssessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'prerequisite_assessment_id');
    }
}
