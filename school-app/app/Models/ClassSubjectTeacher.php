<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubjectTeacher extends Model
{
    protected $fillable = [
        'class_academic_year_id',
        'subject_id',
        'staff_id'
    ];

    public function classAcademicYear(): BelongsTo
    {
        return $this->belongsTo(ClassAcademicYear::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}