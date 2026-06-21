<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\BeceMockScore;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class BeceController extends Controller
{
    /**
     * BECE readiness dashboard — all JHS3 students with aggregate scores.
     */
    public function index()
    {
        $currentYear = AcademicYear::current();
        $students = collect();
        $classAcademicYearId = null;

        if ($currentYear) {
            // Find JHS3 class for the current academic year
            $jhs3Class = SchoolClass::where('name', 'like', '%JHS 3%')
                ->orWhere('name', 'like', '%JHS3%')
                ->first();

            if ($jhs3Class) {
                $classAY = ClassAcademicYear::where('school_class_id', $jhs3Class->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->first();

                if ($classAY) {
                    $classAcademicYearId = $classAY->id;

                    // Get all enrolled JHS3 students
                    $enrolledStudents = Student::whereHas('classEnrollments', function ($q) use ($classAY) {
                        $q->where('class_academic_year_id', $classAY->id)
                          ->where('status', 'enrolled');
                    })->get();

                    foreach ($enrolledStudents as $student) {
                        $aggregateData = BeceMockScore::calculateAggregate(
                            $student->id,
                            $classAY->id,
                            'Mock 1'
                        );

                        $students->push([
                            'student' => $student,
                            'aggregate' => $aggregateData['aggregate'] ?? null,
                            'is_at_risk' => $aggregateData['is_at_risk'] ?? false,
                            'total_subjects' => $aggregateData['total_subjects'] ?? 0,
                            'best6' => $aggregateData['best6'] ?? collect(),
                        ]);
                    }

                    // Sort by aggregate (lowest = best)
                    $students = $students->sortBy('aggregate');
                }
            }
        }

        return view('academics.bece.index', compact('students', 'classAcademicYearId', 'currentYear'));
    }

    /**
     * Show form to enter mock exam scores for JHS3 students.
     */
    public function enterScores()
    {
        $currentYear = AcademicYear::current();
        $students = collect();
        $subjects = collect();
        $classAcademicYearId = null;

        if ($currentYear) {
            $jhs3Class = SchoolClass::where('name', 'like', '%JHS 3%')
                ->orWhere('name', 'like', '%JHS3%')
                ->first();

            if ($jhs3Class) {
                $classAY = ClassAcademicYear::where('school_class_id', $jhs3Class->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->first();

                if ($classAY) {
                    $classAcademicYearId = $classAY->id;

                    $students = Student::whereHas('classEnrollments', function ($q) use ($classAY) {
                        $q->where('class_academic_year_id', $classAY->id)
                          ->where('status', 'enrolled');
                    })->orderBy('last_name')->get();

                    // Get JHS-level subjects
                    $subjects = Subject::where('level', 'jhs')
                        ->orWhere('level', 'all')
                        ->orderBy('name')
                        ->get();
                }
            }
        }

        return view('academics.bece.enter-scores', compact(
            'students', 'subjects', 'classAcademicYearId', 'currentYear'
        ));
    }

    /**
     * Store mock exam scores.
     */
    public function storeScores(Request $request)
    {
        $request->validate([
            'class_academic_year_id' => 'required|exists:class_academic_years,id',
            'mock_exam_label' => 'required|string|max:50',
            'scores' => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.subject_id' => 'required|exists:subjects,id',
            'scores.*.raw_score' => 'required|numeric|min:0|max:100',
        ]);

        $savedCount = 0;

        foreach ($request->scores as $scoreData) {
            if (empty($scoreData['raw_score']) && $scoreData['raw_score'] !== '0') {
                continue;
            }

            $beceGrade = BeceMockScore::rawScoreToBECEGrade((float) $scoreData['raw_score']);

            BeceMockScore::updateOrCreate(
                [
                    'student_id' => $scoreData['student_id'],
                    'subject_id' => $scoreData['subject_id'],
                    'class_academic_year_id' => $request->class_academic_year_id,
                    'mock_exam_label' => $request->mock_exam_label,
                ],
                [
                    'raw_score' => $scoreData['raw_score'],
                    'bece_grade' => $beceGrade,
                    'recorded_by' => auth()->id(),
                ]
            );

            $savedCount++;
        }

        $classAY = ClassAcademicYear::findOrFail($request->class_academic_year_id);
        AuditLog::log(
            'bece_scores_entered',
            $classAY,
            null,
            [
                'mock_exam_label' => $request->mock_exam_label,
                'saved_count' => $savedCount,
            ]
        );

        return redirect()->route('academics.bece.index')
            ->with('success', "Saved {$savedCount} BECE mock score(s) for {$request->mock_exam_label}.");
    }

    /**
     * Individual student BECE detail view.
     */
    public function studentDetail(Student $student)
    {
        $currentYear = AcademicYear::current();
        $mockScores = collect();
        $aggregateData = null;

        if ($currentYear) {
            $enrollment = $student->currentClassEnrollment($currentYear);
            if ($enrollment) {
                $mockScores = BeceMockScore::where('student_id', $student->id)
                    ->where('class_academic_year_id', $enrollment->class_academic_year_id)
                    ->with('subject')
                    ->orderBy('mock_exam_label')
                    ->orderBy('bece_grade', 'asc')
                    ->get()
                    ->groupBy('mock_exam_label');

                $aggregateData = BeceMockScore::calculateAggregate(
                    $student->id,
                    $enrollment->class_academic_year_id,
                    'Mock 1'
                );
            }
        }

        $enrollment = $student->currentClassEnrollment();

        return view('academics.bece.student-detail', compact(
            'student', 'mockScores', 'aggregateData', 'enrollment'
        ));
    }
}
