<?php

namespace Database\Seeders;

use App\Models\TeacherAssignment;
use App\Models\User;
use App\Models\ClassAcademicYear;
use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Populate teacher_assignments from existing class_academic_years (form teachers)
     * and class_subject_teachers (subject teachers).
     * Can be run standalone: php artisan db:seed --class=TeacherAssignmentSeeder
     */
    public function run(): void
    {
        // 1. Create form teacher assignments from class_academic_years
        $classAcademicYears = ClassAcademicYear::whereNotNull('class_teacher_id')
            ->with(['schoolClass'])
            ->get();

        foreach ($classAcademicYears as $cay) {
            // Find the user linked to this staff member
            $user = User::where('userable_type', 'App\\Models\\Staff')
                ->where('userable_id', $cay->class_teacher_id)
                ->first();

            if ($user && $cay->schoolClass) {
                TeacherAssignment::firstOrCreate([
                    'user_id' => $user->id,
                    'class_id' => $cay->school_class_id,
                    'is_form_teacher' => true,
                ], [
                    'subject_id' => null,
                ]);
            }
        }

        // 2. Create subject teacher assignments from class_subject_teachers
        $subjectTeachers = ClassSubjectTeacher::with(['classAcademicYear.schoolClass'])->get();

        foreach ($subjectTeachers as $cst) {
            $user = User::where('userable_type', 'App\\Models\\Staff')
                ->where('userable_id', $cst->staff_id)
                ->first();

            if ($user && $cst->classAcademicYear && $cst->classAcademicYear->schoolClass) {
                TeacherAssignment::firstOrCreate([
                    'user_id' => $user->id,
                    'class_id' => $cst->classAcademicYear->school_class_id,
                    'subject_id' => $cst->subject_id,
                    'is_form_teacher' => false,
                ]);
            }
        }

        $this->command->info('Teacher assignments populated: ' . TeacherAssignment::count() . ' rows created.');
    }
}
