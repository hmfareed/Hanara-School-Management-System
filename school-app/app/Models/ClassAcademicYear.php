<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassAcademicYear extends Model
{
    protected $fillable = ['school_class_id', 'academic_year_id', 'class_teacher_id'];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'class_teacher_id');
    }

    public function classStudents(): HasMany
    {
        return $this->hasMany(ClassStudent::class);
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            ClassStudent::class,
            'class_academic_year_id', // FK on class_student
            'id',                      // FK on students
            'id',                      // Local key on class_academic_years
            'student_id'               // Local key on class_student
        );
    }
}
