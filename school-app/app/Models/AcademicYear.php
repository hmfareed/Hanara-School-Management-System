<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'is_current'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function classAcademicYears(): HasMany
    {
        return $this->hasMany(ClassAcademicYear::class);
    }

    public function assessmentComponents(): HasMany
    {
        return $this->hasMany(AssessmentComponent::class);
    }

    public function feeItems(): HasMany
    {
        return $this->hasMany(FeeItem::class);
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }
}
