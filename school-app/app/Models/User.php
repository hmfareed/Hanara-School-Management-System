<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'userable_type',
        'userable_id',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Get the linked profile (Staff, Student, or Guardian).
     */
    public function userable()
    {
        return $this->morphTo();
    }

    /*
    |----------------------------------------------------------------------
    | Teacher Assignment Relationships
    |----------------------------------------------------------------------
    */

    /**
     * All teaching assignments for this user.
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * The form-teacher assignment (one class where is_form_teacher = true).
     */
    public function formTeacherAssignment()
    {
        return $this->hasOne(TeacherAssignment::class)->where('is_form_teacher', true);
    }

    /**
     * Subject-teaching assignments (is_form_teacher = false, subject_id not null).
     */
    public function subjectAssignments()
    {
        return $this->hasMany(TeacherAssignment::class)->where('is_form_teacher', false)->whereNotNull('subject_id');
    }

    /*
    |----------------------------------------------------------------------
    | RBAC Helper Methods
    |----------------------------------------------------------------------
    */

    /**
     * Check if this user is a form teacher for any class.
     */
    public function isFormTeacher(): bool
    {
        return $this->teacherAssignments()->where('is_form_teacher', true)->exists();
    }

    /**
     * Check if this user teaches any subjects.
     */
    public function isSubjectTeacher(): bool
    {
        return $this->teacherAssignments()->where('is_form_teacher', false)->whereNotNull('subject_id')->exists();
    }

    /**
     * Check if this user can access a specific class (is assigned to it in any capacity).
     */
    public function canAccessClass(int $classId): bool
    {
        // Full-access roles can access any class
        if ($this->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            return true;
        }

        return $this->teacherAssignments()->where('class_id', $classId)->exists();
    }

    /**
     * Check if this user can access a specific subject in a specific class.
     */
    public function canAccessSubject(int $subjectId, int $classId): bool
    {
        // Full-access roles can access any subject
        if ($this->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            return true;
        }

        // Form teachers can access all subjects in their class
        if ($this->teacherAssignments()->where('class_id', $classId)->where('is_form_teacher', true)->exists()) {
            return true;
        }

        return $this->teacherAssignments()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    /**
     * Get all class IDs this user is assigned to.
     */
    public function assignedClassIds(): array
    {
        return $this->teacherAssignments()->pluck('class_id')->unique()->values()->toArray();
    }

    /**
     * Get all subject IDs this user is assigned to.
     */
    public function assignedSubjectIds(): array
    {
        return $this->teacherAssignments()->whereNotNull('subject_id')->pluck('subject_id')->unique()->values()->toArray();
    }

    /**
     * Get the dashboard route for this user's primary role.
     */
    public function getDashboardRoute(): string
    {
        $role = $this->roles->first()?->name;

        return match ($role) {
            'Proprietor' => 'dashboard.proprietor',
            'HeadTeacher' => 'dashboard.head-teacher',
            'Supervisor' => 'dashboard.head-teacher',
            'ClassTeacher' => 'dashboard.class-teacher',
            'SubjectTeacher' => 'dashboard.subject-teacher',
            'Accounts' => 'dashboard.accounts',
            'FrontDesk' => 'dashboard.front-desk',
            'Parent' => 'dashboard.parent',
            'Student' => 'dashboard.student',
            default => 'dashboard.proprietor',
        };
    }

    /**
     * Get or generate a unique personal code for a staff member.
     */
    public function getOrGeneratePersonalCode(): ?string
    {
        if ($this->userable_type !== \App\Models\Staff::class || !$this->userable) {
            return null;
        }

        $staff = $this->userable;
        if (!$staff->personal_code) {
            do {
                $code = strval(rand(100000, 999999));
            } while (\App\Models\Staff::where('personal_code', $code)->exists());

            $staff->update(['personal_code' => $code]);
        }

        return $staff->personal_code;
    }
}
