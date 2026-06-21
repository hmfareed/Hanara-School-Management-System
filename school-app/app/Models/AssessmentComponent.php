<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentComponent extends Model
{
    protected $fillable = ['name', 'weight', 'academic_year_id', 'level', 'max_score'];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
