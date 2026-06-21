<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\GradeScale;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Services\AcademicService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AcademicsController extends Controller
{
    protected AcademicService $academicService;

    public function __construct(AcademicService $academicService)
    {
        $this->academicService = $academicService;
    }

    /**
     * Gradebook workspace (Livewire-powered).
     */
    public function gradebook()
    {
        return view('academics.gradebook');
    }

    /**
     * Teacher-subject assignments (Livewire-powered).
     */
    public function assignments()
    {
        return view('academics.assignments');
    }

    /**
     * Weekly timetable viewer + builder.
     */
    public function timetable(Request $request)
    {
        $currentYear = AcademicYear::current();
        $user = auth()->user();

        if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')
                    ->where('academic_year_id', $currentYear->id)
                    ->get()
                : collect();
        } else {
            $assignedClassIds = $user->assignedClassIds();
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')
                    ->where('academic_year_id', $currentYear->id)
                    ->whereIn('school_class_id', $assignedClassIds)
                    ->get()
                : collect();
        }

        $selectedClassId = $request->input('class_id', $classes->first()?->id);

        if ($selectedClassId && !$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classAY = ClassAcademicYear::find($selectedClassId);
            if (!$classAY || !$user->canAccessClass($classAY->school_class_id)) {
                abort(403, 'Unauthorized to view timetable for this class.');
            }
        }

        $slots = collect();
        if ($selectedClassId) {
            $slots = TimetableSlot::with(['subject', 'teacher'])
                ->where('class_academic_year_id', $selectedClassId)
                ->orderBy('start_time')
                ->get();
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        // Build timetable grid: group slots by day
        $timetableGrid = [];
        foreach ($days as $day) {
            $timetableGrid[$day] = $slots->where('day_of_week', $day)->values();
        }

        return view('academics.timetable', compact('classes', 'selectedClassId', 'timetableGrid', 'days'));
    }

    /**
     * Bulk report cards page — list students and allow PDF generation.
     */
    public function reportCards(Request $request)
    {
        $currentYear = AcademicYear::current();
        $currentTerm = Term::current();
        $classId = $request->input('class_id');
        $user = auth()->user();

        if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')
                    ->where('academic_year_id', $currentYear->id)
                    ->get()
                : collect();
        } else {
            $assignedClassIds = $user->assignedClassIds();
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')
                    ->where('academic_year_id', $currentYear->id)
                    ->whereIn('school_class_id', $assignedClassIds)
                    ->get()
                : collect();
        }

        $students = collect();
        if ($classId) {
            if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
                $classYear = ClassAcademicYear::find($classId);
                if (!$classYear || !$user->canAccessClass($classYear->school_class_id)) {
                    abort(403, 'Unauthorized to view report cards for this class.');
                }
            } else {
                $classYear = ClassAcademicYear::find($classId);
            }

            if ($classYear) {
                $students = $classYear->students()
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        return view('academics.report-cards', compact('classes', 'students', 'classId', 'currentTerm'));
    }

    /**
     * Generate and download a PDF report card for a single student.
     */
    public function reportCard(Student $student, Request $request)
    {
        $currentYear = AcademicYear::current();
        $currentTerm = Term::current();

        if (!$currentYear || !$currentTerm) {
            return back()->with('warning', 'No active academic year/term configured.');
        }

        // Find the student's enrollment
        $enrollment = ClassStudent::where('student_id', $student->id)
            ->where('status', 'enrolled')
            ->whereHas('classAcademicYear', function ($q) use ($currentYear) {
                $q->where('academic_year_id', $currentYear->id);
            })
            ->with('classAcademicYear.schoolClass')
            ->first();

        if (!$enrollment) {
            return back()->with('warning', 'Student is not enrolled in any class for the current academic year.');
        }

        $classAcYear = $enrollment->classAcademicYear;
        $user = auth()->user();

        if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            if (!$user->canAccessClass($classAcYear->school_class_id)) {
                abort(403, 'Unauthorized to view this student\'s report card.');
            }
        }

        $schoolClass = $classAcYear->schoolClass;

        // Determine education level for grading
        $level = $schoolClass->level ?? 'primary';

        // Get all subjects that have scores for this student in this class
        $subjectIds = AssessmentScore::where('student_id', $student->id)
            ->where('class_academic_year_id', $classAcYear->id)
            ->distinct()
            ->pluck('subject_id');

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        // Get assessment components for this year
        $components = AssessmentComponent::where('academic_year_id', $currentYear->id)->get();

        // Build results table
        $results = [];
        $totalScore = 0;
        $subjectCount = 0;

        foreach ($subjects as $subject) {
            $row = [
                'subject' => $subject->name,
                'scores' => [],
                'total' => 0,
                'grade' => 'N/A',
                'remarks' => '',
            ];

            foreach ($components as $component) {
                $score = AssessmentScore::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('assessment_component_id', $component->id)
                    ->where('class_academic_year_id', $classAcYear->id)
                    ->first();

                $row['scores'][$component->id] = $score ? $score->score : null;
            }

            // Calculate weighted total
            $subjectTotal = $this->academicService->calculateSubjectTotal(
                $student->id, $subject->id, $classAcYear->id
            );
            $row['total'] = $subjectTotal;

            // Look up grade
            $gradeScale = GradeScale::lookup($subjectTotal, $level);
            if ($gradeScale) {
                $row['grade'] = $gradeScale->grade;
                $row['remarks'] = $gradeScale->remarks;
            }

            $results[] = $row;
            $totalScore += $subjectTotal;
            $subjectCount++;
        }

        $averageScore = $subjectCount > 0 ? round($totalScore / $subjectCount, 2) : 0;

        // Rank in class
        $classStudents = $classAcYear->students;
        $classAverages = [];
        foreach ($classStudents as $classStudent) {
            $sTotal = 0;
            $sCount = 0;
            foreach ($subjectIds as $sId) {
                $st = $this->academicService->calculateSubjectTotal($classStudent->id, $sId, $classAcYear->id);
                $sTotal += $st;
                $sCount++;
            }
            $classAverages[$classStudent->id] = $sCount > 0 ? $sTotal / $sCount : 0;
        }
        arsort($classAverages);
        $position = array_search($student->id, array_keys($classAverages)) + 1;
        $totalStudents = count($classAverages);

        $data = compact(
            'student', 'schoolClass', 'currentYear', 'currentTerm',
            'components', 'results', 'averageScore', 'position', 'totalStudents', 'level'
        );

        $pdf = Pdf::loadView('academics.report-card', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("report-card-{$student->student_id_number}-{$currentTerm->name}.pdf");
    }
}
