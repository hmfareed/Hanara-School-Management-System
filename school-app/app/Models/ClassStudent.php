<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStudent extends Model
{
    protected $table = 'class_student';

    protected $fillable = ['student_id', 'class_academic_year_id', 'enrolled_at', 'status'];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classAcademicYear(): BelongsTo
    {
        return $this->belongsTo(ClassAcademicYear::class);
    }
}
