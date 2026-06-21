<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    protected $fillable = ['level', 'grade', 'min_score', 'max_score', 'remarks'];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    /**
     * Look up the grade for a given numeric score at a specific education level.
     */
    public static function lookup(float $score, string $level): ?self
    {
        return static::where('level', $level)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }
}
