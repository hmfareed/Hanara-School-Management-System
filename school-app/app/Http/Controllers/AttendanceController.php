<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassAcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Show daily attendance marking workspace.
     */
    public function mark()
    {
        return view('attendance.mark');
    }

    /**
     * Show monthly attendance register grid.
     */
    public function register(Request $request)
    {
        $user = auth()->user();
        $isPowerUser = $user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor']);

        $classes = [];
        if ($isPowerUser) {
            $classes = SchoolClass::orderBy('display_order')->get();
        } else {
            $formClassIds = $user->teacherAssignments()
                ->where('is_form_teacher', true)
                ->pluck('class_id')
                ->toArray();
            $classes = SchoolClass::whereIn('id', $formClassIds)->orderBy('display_order')->get();
        }

        $classId = $request->input('class_id');

        // Restrict non-power users to only query their form class
        if (!$isPowerUser) {
            $formClassIds = $user->teacherAssignments()
                ->where('is_form_teacher', true)
                ->pluck('class_id')
                ->toArray();

            // Default to the first form class if no valid class_id was provided
            if (!$classId || !in_array($classId, $formClassIds)) {
                $classId = reset($formClassIds) ?: null;
            }
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $currentYear = AcademicYear::where('is_current', true)->first();

        $students = collect();
        $grid = [];
        $daysInMonth = 0;
        $dateObj = null;

        if ($classId && $currentYear) {
            $classAcYear = ClassAcademicYear::where('school_class_id', $classId)
                ->where('academic_year_id', $currentYear->id)
                ->first();

            if ($classAcYear) {
                // Load enrolled students
                $students = $classAcYear->students()->orderBy('last_name')->orderBy('first_name')->get();

                // Compute days in target month
                $dateObj = Carbon::createFromDate($year, $month, 1);
                $daysInMonth = $dateObj->daysInMonth;

                // Load all attendance records for this class and month
                $startDate = $dateObj->copy()->startOfMonth()->toDateString();
                $endDate = $dateObj->copy()->endOfMonth()->toDateString();

                $attendances = Attendance::where('class_academic_year_id', $classAcYear->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->get()
                    ->groupBy('student_id');

                // Generate matrix of attendance
                foreach ($students as $student) {
                    $studentAtts = $attendances->get($student->id, collect())->keyBy(function($item) {
                        return Carbon::parse($item->date)->day;
                    });

                    $present = 0;
                    $absent = 0;
                    $late = 0;
                    $days = [];

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $record = $studentAtts->get($day);
                        $status = $record ? $record->status : null;
                        
                        if ($status === 'present') $present++;
                        elseif ($status === 'late') $late++;
                        elseif ($status === 'absent') $absent++;

                        $days[$day] = $status;
                    }

                    $totalMarked = $present + $late + $absent;
                    $rate = $totalMarked > 0 
                        ? round((($present + $late) / $totalMarked) * 100, 1) 
                        : null;

                    $grid[$student->id] = [
                        'student' => $student,
                        'days' => $days,
                        'present' => $present,
                        'late' => $late,
                        'absent' => $absent,
                        'rate' => $rate,
                    ];
                }
            }
        }

        return view('attendance.register', compact(
            'classes', 'students', 'grid', 'daysInMonth', 'month', 'year', 'classId', 'dateObj'
        ));
    }
}
