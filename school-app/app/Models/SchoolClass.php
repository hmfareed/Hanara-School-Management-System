<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'level', 'display_order'];

    public function classAcademicYears(): HasMany
    {
        return $this->hasMany(ClassAcademicYear::class);
    }

    public function academicYears(): BelongsToMany
    {
        return $this->belongsToMany(AcademicYear::class, 'class_academic_years')
                    ->withPivot('class_teacher_id')
                    ->withTimestamps();
    }

    /**
     * Teacher assignments for this class (RBAC).
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }
}
