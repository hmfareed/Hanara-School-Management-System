<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id_number', 'first_name', 'last_name', 'other_names',
        'date_of_birth', 'gender', 'photo', 'address', 'nationality',
        'religion', 'blood_group', 'medical_notes', 'admission_date', 'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(ClassStudent::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->other_names, $this->last_name]);
        return implode(' ', $parts);
    }

    /**
     * Get current class enrollment for a given academic year.
     */
    public function currentClassEnrollment(?AcademicYear $year = null)
    {
        $year = $year ?? AcademicYear::current();
        if (!$year) return null;

        return $this->classEnrollments()
                    ->whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $year->id))
                    ->with('classAcademicYear.schoolClass')
                    ->first();
    }

    /**
     * Generate a new student ID number.
     */
    public static function generateStudentId(): string
    {
        $year = date('Y');
        $lastStudent = static::withTrashed()
            ->where('student_id_number', 'like', "HAN-{$year}-%")
            ->orderBy('student_id_number', 'desc')
            ->first();

        $nextNumber = $lastStudent
            ? ((int) substr($lastStudent->student_id_number, -4)) + 1
            : 1;

        return sprintf('HAN-%s-%04d', $year, $nextNumber);
    }
}
