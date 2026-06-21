<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'staff_id_number', 'first_name', 'last_name', 'other_names',
        'date_of_birth', 'gender', 'phone', 'email', 'address',
        'qualification', 'date_joined', 'position', 'status', 'photo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
    ];

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function classAcademicYears()
    {
        return $this->hasMany(ClassAcademicYear::class, 'class_teacher_id');
    }

    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'staff_id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->other_names, $this->last_name]);
        return implode(' ', $parts);
    }

    /**
     * Generate a new staff ID number.
     */
    public static function generateStaffId(): string
    {
        $year = date('Y');
        $lastStaff = static::withTrashed()
            ->where('staff_id_number', 'like', "STF-{$year}-%")
            ->orderBy('staff_id_number', 'desc')
            ->first();

        $nextNumber = $lastStaff
            ? ((int) substr($lastStaff->staff_id_number, -4)) + 1
            : 1;

        return sprintf('STF-%s-%04d', $year, $nextNumber);
    }
}
