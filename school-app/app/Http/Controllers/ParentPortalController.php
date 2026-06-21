<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AssessmentScore;
use App\Models\Attendance;
use App\Models\ClassAcademicYear;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Term;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentPortalController extends Controller
{
    /**
     * Ensure the logged-in parent is linked to the given student.
     */
    protected function authorizeParentAccess(Student $student): Guardian
    {
        $user = auth()->user();
        $guardian = $user->userable;

        if (!$guardian || !($guardian instanceof Guardian)) {
            abort(403, 'Your account is not linked to a guardian profile.');
        }

        $linked = $guardian->students()->where('students.id', $student->id)->exists();
        if (!$linked) {
            abort(403, 'You do not have access to this student\'s records.');
        }

        return $guardian;
    }

    /**
     * View child's attendance history.
     */
    public function childAttendance(Student $student)
    {
        $this->authorizeParentAccess($student);

        $currentYear = AcademicYear::current();
        $attendanceRecords = collect();
        $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0];

        if ($currentYear) {
            $enrollment = $student->currentClassEnrollment($currentYear);
            if ($enrollment) {
                $attendanceRecords = Attendance::where('student_id', $student->id)
                    ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->orderBy('date', 'desc')
                    ->get();

                $stats['total'] = $attendanceRecords->count();
                $stats['present'] = $attendanceRecords->where('status', 'present')->count();
                $stats['absent'] = $attendanceRecords->where('status', 'absent')->count();
                $stats['late'] = $attendanceRecords->where('status', 'late')->count();
                $stats['rate'] = $stats['total'] > 0
                    ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100)
                    : 0;
            }
        }

        $enrollment = $student->currentClassEnrollment();

        return view('parent.attendance', compact('student', 'attendanceRecords', 'stats', 'enrollment'));
    }

    /**
     * View child's grades summary.
     */
    public function childGrades(Student $student)
    {
        $this->authorizeParentAccess($student);

        $currentYear = AcademicYear::current();
        $grades = collect();

        if ($currentYear) {
            $enrollment = $student->currentClassEnrollment($currentYear);
            if ($enrollment) {
                $grades = AssessmentScore::where('student_id', $student->id)
                    ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->with(['subject', 'component'])
                    ->get()
                    ->groupBy('subject.name');
            }
        }

        $enrollment = $student->currentClassEnrollment();

        return view('parent.grades', compact('student', 'grades', 'enrollment'));
    }

    /**
     * View child's fee invoices and payment history.
     */
    public function childFees(Student $student)
    {
        $this->authorizeParentAccess($student);

        $invoices = Invoice::where('student_id', $student->id)
            ->with(['payments', 'term'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalOwed = $invoices->sum('balance');
        $totalPaid = $invoices->sum('amount_paid');

        return view('parent.fees', compact('student', 'invoices', 'totalOwed', 'totalPaid'));
    }

    /**
     * Download child's report card PDF (reuses existing AcademicsController logic).
     */
    public function childReportCard(Student $student)
    {
        $this->authorizeParentAccess($student);

        return app(\App\Http\Controllers\AcademicsController::class)->reportCard($student);
    }

    /**
     * Initialize a fee payment via Paystack from the parent portal.
     */
    public function payFee(Invoice $invoice)
    {
        $student = $invoice->student;
        $this->authorizeParentAccess($student);

        return app(\App\Http\Controllers\BillingController::class)->initializeOnlinePayment($invoice);
    }
}
