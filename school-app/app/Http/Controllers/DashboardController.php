<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\Term;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new \Illuminate\Routing\Controllers\Middleware(function ($request, $next) {
                if (auth()->check()) {
                    auth()->user()->getOrGeneratePersonalCode();
                }
                return $next($request);
            }),
        ];
    }

    public function proprietor()
    {
        $currentYear = AcademicYear::current();
        $totalStudents = Student::where('status', 'active')->count();
        $activeStaff = Staff::where('status', 'active')->count();

        // Enrollment by level
        $enrollmentByLevel = [];
        if ($currentYear) {
            $enrollmentByLevel = Student::where('students.status', 'active')
                ->whereHas('classEnrollments', function ($q) use ($currentYear) {
                    $q->whereHas('classAcademicYear', fn($q2) => $q2->where('academic_year_id', $currentYear->id));
                })
                ->join('class_student', 'students.id', '=', 'class_student.student_id')
                ->join('class_academic_years', 'class_student.class_academic_year_id', '=', 'class_academic_years.id')
                ->join('school_classes', 'class_academic_years.school_class_id', '=', 'school_classes.id')
                ->where('class_academic_years.academic_year_id', $currentYear->id)
                ->selectRaw('school_classes.level, COUNT(DISTINCT students.id) as count')
                ->groupBy('school_classes.level')
                ->pluck('count', 'level')
                ->toArray();
        }

        // Fee collection percentage
        $feeCollection = 0;
        $currentTerm = null;
        if ($currentYear) {
            $currentTerm = $currentYear->terms()->where('is_current', true)->first();
            if ($currentTerm) {
                $totalInvoiced = Invoice::where('term_id', $currentTerm->id)->sum('total_amount');
                $totalPaid = Invoice::where('term_id', $currentTerm->id)->sum('amount_paid');
                $feeCollection = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100) : 0;
            }
        }

        // Attendance Rate
        $avgAttendance = 94; // fallback
        if ($currentYear) {
            $attendanceCount = \App\Models\Attendance::whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $currentYear->id))->count();
            if ($attendanceCount > 0) {
                $presentCount = \App\Models\Attendance::whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $currentYear->id))->where('status', 'present')->count();
                $avgAttendance = round(($presentCount / $attendanceCount) * 100);
            }
        }

        // Students needing attention (overdue fees) — only real data, no placeholders
        $attentionList = [];
        if ($totalStudents > 0) {
            $overdueInvoices = Invoice::with(['student.classEnrollments.classAcademicYear.schoolClass'])
                ->where('balance', '>', 0)
                ->where('due_date', '<', now()->toDateString())
                ->orderBy('balance', 'desc')
                ->take(5)
                ->get();

            foreach ($overdueInvoices as $invoice) {
                $enrollment = $invoice->student->currentClassEnrollment();
                $attentionList[] = [
                    'name' => $invoice->student->full_name,
                    'className' => $enrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned',
                    'reason' => 'Fee Overdue: GH₵' . number_format($invoice->balance, 2),
                    'badge' => 'badge-error',
                    'student_id' => $invoice->student_id,
                ];
            }
        }

        // Recent Activity (actual payments & audit logs)
        $recentActivities = [];
        $recentPayments = Payment::with('invoice.student')->orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentPayments as $payment) {
            $recentActivities[] = [
                'icon' => 'payments',
                'bg' => 'bg-[#dcfce7]',
                'color' => 'text-[#166534]',
                'text' => 'Tuition Fee Payment: GH₵' . number_format($payment->amount, 2) . ' received from ' . $payment->invoice->student->full_name,
                'time' => $payment->created_at->diffForHumans(),
            ];
        }

        $recentAuditLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentAuditLogs as $log) {
            $actionText = ucfirst(str_replace('_', ' ', $log->action));
            $recentActivities[] = [
                'icon' => 'history',
                'bg' => 'bg-primary-container/10',
                'color' => 'text-primary',
                'text' => $actionText . ' log entry recorded by ' . ($log->user?->name ?? 'System'),
                'time' => $log->created_at->diffForHumans(),
            ];
        }

        // Fallback placeholder activity if none exist
        if (empty($recentActivities)) {
            $recentActivities[] = [
                'icon' => 'person_add',
                'bg' => 'bg-primary-container/10',
                'color' => 'text-primary',
                'text' => 'New student profile created in database',
                'time' => '1 hour ago',
            ];
        }

        return view('dashboards.proprietor', compact(
            'totalStudents', 'activeStaff', 'enrollmentByLevel', 'feeCollection', 'avgAttendance', 'attentionList', 'recentActivities', 'currentYear'
        ));
    }

    public function headTeacher()
    {
        $currentYear = AcademicYear::current();
        $totalStudents = Student::where('status', 'active')->count();
        $activeStaff = Staff::where('status', 'active')->count();

        $avgAttendance = 95;
        if ($currentYear) {
            $attendanceCount = \App\Models\Attendance::whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $currentYear->id))->count();
            if ($attendanceCount > 0) {
                $presentCount = \App\Models\Attendance::whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $currentYear->id))->where('status', 'present')->count();
                $avgAttendance = round(($presentCount / $attendanceCount) * 100);
            }
        }

        // Get class summaries
        $classes = [];
        if ($currentYear) {
            $classes = SchoolClass::orderBy('display_order')
                ->withCount(['classAcademicYears as students_count' => function($q) use ($currentYear) {
                    $q->where('academic_year_id', $currentYear->id)
                      ->join('class_student', 'class_academic_years.id', '=', 'class_student.class_academic_year_id')
                      ->where('class_student.status', 'enrolled');
                }])
                ->get();
        }

        $isSupervisor = auth()->user()->hasRole('Supervisor');

        return view('dashboards.head-teacher', compact(
            'totalStudents', 'activeStaff', 'avgAttendance', 'classes', 'currentYear', 'isSupervisor'
        ));
    }

    public function classTeacher()
    {
        $currentYear = AcademicYear::current();
        
        $classAcademicYear = null;
        $studentsCount = 0;
        $attendanceRate = 0;
        $attendanceToday = false;
        $students = collect();
        $recentAttendance = collect();

        if ($currentYear) {
            $assignment = \App\Models\TeacherAssignment::where('user_id', auth()->id())
                ->where('is_form_teacher', true)
                ->first();
                
            if ($assignment) {
                $classAcademicYear = \App\Models\ClassAcademicYear::where('academic_year_id', $currentYear->id)
                    ->where('school_class_id', $assignment->class_id)
                    ->with('schoolClass')
                    ->first();
                    
                if ($classAcademicYear) {
                    // Get students enrolled in this class
                    $students = \App\Models\ClassStudent::where('class_academic_year_id', $classAcademicYear->id)
                        ->where('status', 'enrolled')
                        ->with('student')
                        ->get()
                        ->pluck('student')
                        ->sortBy('last_name');

                    $studentsCount = $students->count();

                    // Check attendance today
                    $attendanceToday = \App\Models\Attendance::where('class_academic_year_id', $classAcademicYear->id)
                        ->whereDate('date', now()->toDateString())
                        ->exists();

                    // Compute class attendance rate
                    $totalAttendanceRecords = \App\Models\Attendance::where('class_academic_year_id', $classAcademicYear->id)->count();
                    if ($totalAttendanceRecords > 0) {
                        $presentRecords = \App\Models\Attendance::where('class_academic_year_id', $classAcademicYear->id)
                            ->whereIn('status', ['present', 'late'])
                            ->count();
                        $attendanceRate = round(($presentRecords / $totalAttendanceRecords) * 100);
                    } else {
                        $attendanceRate = 100; // default if no records
                    }

                    // Recent attendance logs
                    $recentAttendance = \App\Models\Attendance::where('class_academic_year_id', $classAcademicYear->id)
                        ->orderBy('date', 'desc')
                        ->with('student')
                        ->take(10)
                        ->get();
                }
            }
        }

        // Active announcements for staff/teachers
        $announcements = \App\Models\Announcement::active()
            ->forAudience('staff')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.class-teacher', compact(
            'classAcademicYear', 'studentsCount', 'attendanceRate', 'attendanceToday', 'students', 'recentAttendance', 'announcements', 'currentYear'
        ));
    }

    public function subjectTeacher()
    {
        $currentYear = AcademicYear::current();
        
        $assignments = collect();
        $totalStudents = 0;
        $classCount = 0;
        $subjectCount = 0;
        $uniqueStudentIds = collect();

        if ($currentYear) {
            $assignments = \App\Models\TeacherAssignment::where('user_id', auth()->id())
                ->where('is_form_teacher', false)
                ->whereNotNull('subject_id')
                ->with(['schoolClass', 'subject'])
                ->get();

            $classCount = $assignments->pluck('class_id')->unique()->count();
            $subjectCount = $assignments->pluck('subject_id')->unique()->count();

            // Count unique students across all assigned classes
            foreach ($assignments as $assign) {
                $classAY = \App\Models\ClassAcademicYear::where('school_class_id', $assign->class_id)
                    ->where('academic_year_id', $currentYear->id)
                    ->first();

                if ($classAY) {
                    $studentIds = \App\Models\ClassStudent::where('class_academic_year_id', $classAY->id)
                        ->where('status', 'enrolled')
                        ->pluck('student_id');
                    $uniqueStudentIds = $uniqueStudentIds->merge($studentIds);
                }
            }
            $totalStudents = $uniqueStudentIds->unique()->count();
        }

        // Active announcements for staff/teachers
        $announcements = \App\Models\Announcement::active()
            ->forAudience('staff')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.subject-teacher', compact(
            'assignments', 'classCount', 'subjectCount', 'totalStudents', 'announcements', 'currentYear'
        ));
    }

    public function accounts()
    {
        $currentYear = AcademicYear::current();
        $currentTerm = Term::current();

        $totalInvoiced = 0;
        $totalCollected = 0;
        $outstanding = 0;
        $collectionRate = 0;

        if ($currentTerm) {
            $totalInvoiced = Invoice::where('term_id', $currentTerm->id)->sum('total_amount');
            $totalCollected = Invoice::where('term_id', $currentTerm->id)->sum('amount_paid');
            $outstanding = Invoice::where('term_id', $currentTerm->id)->sum('balance');
            $collectionRate = $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100) : 0;
        }

        // Payment method breakdown
        $momoCollected = Payment::where('method', 'momo')->sum('amount');
        $cashCollected = Payment::where('method', 'cash')->sum('amount');
        $cardCollected = Payment::where('method', 'card')->sum('amount');
        $bankCollected = Payment::where('method', 'bank_transfer')->sum('amount');

        // Recent outstanding invoices
        $defaulters = Invoice::with('student')
            ->where('balance', '>', 0)
            ->orderBy('balance', 'desc')
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::with('invoice.student')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.accounts', compact(
            'totalInvoiced', 'totalCollected', 'outstanding', 'collectionRate',
            'momoCollected', 'cashCollected', 'cardCollected', 'bankCollected',
            'defaulters', 'recentPayments', 'currentTerm'
        ));
    }

    public function frontDesk()
    {
        $totalAdmissions = \App\Models\Admission::count();
        $pendingAdmissions = \App\Models\Admission::where('status', 'pending')->count();
        $acceptedAdmissions = \App\Models\Admission::where('status', 'accepted')->count();
        $declinedAdmissions = \App\Models\Admission::where('status', 'declined')->count();

        // 5 most recent admissions
        $recentAdmissions = \App\Models\Admission::orderBy('created_at', 'desc')->take(5)->get();

        // Active announcements school-wide
        $announcements = \App\Models\Announcement::active()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboards.front-desk', compact(
            'totalAdmissions', 'pendingAdmissions', 'acceptedAdmissions', 'declinedAdmissions', 'recentAdmissions', 'announcements'
        ));
    }

    public function parent()
    {
        $user = auth()->user();
        $guardian = $user->userable;
        $children = collect();
        $announcements = collect();

        if ($guardian && $guardian instanceof \App\Models\Guardian) {
            $students = $guardian->students()->where('status', 'active')->get();
            $currentYear = AcademicYear::current();

            foreach ($students as $student) {
                $enrollment = $student->currentClassEnrollment($currentYear);
                $className = $enrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned';

                // Attendance stats
                $attendanceRate = 0;
                if ($currentYear && $enrollment) {
                    $total = \App\Models\Attendance::where('student_id', $student->id)
                        ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                        ->count();
                    $present = \App\Models\Attendance::where('student_id', $student->id)
                        ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    $attendanceRate = $total > 0 ? round(($present / $total) * 100) : 0;
                }

                // Fee balance
                $feeBalance = Invoice::where('student_id', $student->id)
                    ->sum('balance');

                $children->push([
                    'student' => $student,
                    'className' => $className,
                    'attendanceRate' => $attendanceRate,
                    'feeBalance' => $feeBalance,
                ]);
            }

            // Active announcements for parents
            $announcements = \App\Models\Announcement::active()
                ->forAudience('parents')
                ->orderBy('is_pinned', 'desc')
                ->orderBy('published_at', 'desc')
                ->take(10)
                ->get();
        }

        return view('dashboards.parent', compact('children', 'announcements'));
    }

    public function student()
    {
        $user = auth()->user();
        $student = $user->userable;
        $currentYear = AcademicYear::current();
        
        $className = 'Unassigned';
        $attendanceRate = 100;
        $feeBalance = 0;
        $timetable = collect();
        
        if ($student && $student instanceof \App\Models\Student) {
            $enrollment = $student->currentClassEnrollment($currentYear);
            if ($enrollment) {
                $className = $enrollment->classAcademicYear->schoolClass->name;
                
                // Attendance
                $total = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->count();
                $present = \App\Models\Attendance::where('student_id', $student->id)
                    ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->whereIn('status', ['present', 'late'])
                    ->count();
                $attendanceRate = $total > 0 ? round(($present / $total) * 100) : 100;
                
                // Timetable slots
                $timetable = \App\Models\TimetableSlot::where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->with('subject')
                    ->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->get();
            }
            
            // Fee balance
            $feeBalance = Invoice::where('student_id', $student->id)->sum('balance');
        }
        
        // Active announcements for students
        $announcements = \App\Models\Announcement::active()
            ->where(function ($q) {
                $q->where('audience', 'students')
                  ->orWhere('audience', 'all');
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();
            
        return view('dashboards.student', compact(
            'student', 'className', 'attendanceRate', 'feeBalance', 'timetable', 'announcements', 'currentYear'
        ));
    }
}
