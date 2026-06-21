<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'other_names',
        'date_of_birth',
        'gender',
        'level',
        'assigned_class_id',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_relationship',
        'status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function assignedClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'assigned_class_id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->other_names, $this->last_name]);
        return implode(' ', $parts);
    }
}
