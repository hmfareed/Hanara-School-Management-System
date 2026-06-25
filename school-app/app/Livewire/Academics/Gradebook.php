<?php

namespace App\Livewire\Academics;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\ClassAcademicYear;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Services\AcademicService;
use Livewire\Component;

class Gradebook extends Component
{
    public $selectedClassId;
    public $selectedSubjectId;
    public $selectedComponentId;
    public $scores = []; // student_id => [component_id => score] OR student_id => score

    protected $rules = [
        'scores.*' => 'nullable',
    ];

    public function mount()
    {
        $currentYear = AcademicYear::current();
        if (!$currentYear) return;

        $user = auth()->user();
        if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classAY = ClassAcademicYear::where('academic_year_id', $currentYear->id)->first();
            if ($classAY) {
                $this->selectedClassId = $classAY->id;
                $subject = Subject::first();
                $this->selectedSubjectId = $subject?->id;
            }
        } else {
            $assignment = \App\Models\TeacherAssignment::where('user_id', $user->id)->first();
            if ($assignment) {
                $classAY = ClassAcademicYear::where('school_class_id', $assignment->class_id)
                    ->where('academic_year_id', $currentYear->id)
                    ->first();
                if ($classAY) {
                    $this->selectedClassId = $classAY->id;
                }
                $this->selectedSubjectId = $assignment->subject_id;
            }
        }
        $this->loadScores();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedClassId', 'selectedSubjectId', 'selectedComponentId'])) {
            $this->loadScores();
        }
    }

    public function loadScores()
    {
        if (!$this->selectedClassId || !$this->selectedSubjectId) {
            $this->scores = [];
            return;
        }

        $user = auth()->user();
        $classAY = ClassAcademicYear::find($this->selectedClassId);
        if (!$classAY) return;

        if (!$user->canAccessClass($classAY->school_class_id)) {
            $this->scores = [];
            return;
        }

        if (!$user->canAccessSubject($this->selectedSubjectId, $classAY->school_class_id)) {
            $this->scores = [];
            return;
        }

        $currentYear = AcademicYear::current();

        if ($this->selectedComponentId) {
            // Single-component mode (backward compatibility for old tests)
            $existingScores = AssessmentScore::where('class_academic_year_id', $this->selectedClassId)
                ->where('subject_id', $this->selectedSubjectId)
                ->where('assessment_component_id', $this->selectedComponentId)
                ->pluck('score', 'student_id')
                ->toArray();

            $this->scores = [];
            foreach ($classAY->students as $student) {
                $this->scores[$student->id] = $existingScores[$student->id] ?? '';
            }
        } else {
            // Multi-column mode (the new design)
            $components = AssessmentComponent::where('academic_year_id', $currentYear->id)->get();
            $existingScores = AssessmentScore::where('class_academic_year_id', $this->selectedClassId)
                ->where('subject_id', $this->selectedSubjectId)
                ->get()
                ->groupBy('student_id');

            $this->scores = [];
            foreach ($classAY->students as $student) {
                $studentScores = $existingScores->get($student->id) ?? collect();
                $this->scores[$student->id] = [];
                foreach ($components as $component) {
                    $scoreModel = $studentScores->firstWhere('assessment_component_id', $component->id);
                    $this->scores[$student->id][$component->id] = $scoreModel ? (float)$scoreModel->score : '';
                }
            }
        }
    }

    public function saveScore($studentId, $componentId = null)
    {
        $user = auth()->user();
        
        // Resolve component ID
        if ($componentId === null) {
            $componentId = $this->selectedComponentId;
        }

        if (!$componentId) {
            return;
        }

        $errorKey = $this->selectedComponentId 
            ? "scores.{$studentId}" 
            : "scores.{$studentId}.{$componentId}";

        if ($user->hasRole('Supervisor')) {
            $this->addError($errorKey, "Supervisors have read-only access and cannot modify grades.");
            return;
        }

        $classAY = ClassAcademicYear::find($this->selectedClassId);
        if (!$classAY || !$user->canAccessClass($classAY->school_class_id) || !$user->canAccessSubject($this->selectedSubjectId, $classAY->school_class_id)) {
            $this->addError($errorKey, "Unauthorized to edit grades for this class/subject.");
            return;
        }

        $component = AssessmentComponent::findOrFail($componentId);
        
        // Get the score value depending on mode
        if ($this->selectedComponentId) {
            $scoreValue = $this->scores[$studentId] ?? null;
        } else {
            $scoreValue = $this->scores[$studentId][$componentId] ?? null;
        }

        if ($scoreValue === '' || $scoreValue === null) {
            AssessmentScore::where([
                'student_id' => $studentId,
                'subject_id' => $this->selectedSubjectId,
                'assessment_component_id' => $componentId,
                'class_academic_year_id' => $this->selectedClassId,
            ])->delete();
            $this->resetErrorBag($errorKey);
            return;
        }

        if (!is_numeric($scoreValue) || $scoreValue < 0) {
            $this->addError($errorKey, "Invalid score.");
            return;
        }

        if ($scoreValue > $component->max_score) {
            $this->addError($errorKey, "Cannot exceed max score of {$component->max_score}");
            return;
        }

        $existing = AssessmentScore::where([
            'student_id' => $studentId,
            'subject_id' => $this->selectedSubjectId,
            'assessment_component_id' => $componentId,
            'class_academic_year_id' => $this->selectedClassId,
        ])->first();

        $oldValues = $existing ? ['score' => $existing->score] : null;

        $score = AssessmentScore::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $this->selectedSubjectId,
                'assessment_component_id' => $componentId,
                'class_academic_year_id' => $this->selectedClassId,
            ],
            [
                'score' => $scoreValue,
                'recorded_by' => auth()->id(),
            ]
        );

        \App\Models\AuditLog::log(
            $existing ? 'update_grade' : 'create_grade',
            $score,
            $oldValues,
            ['score' => $score->score]
        );

        $this->resetErrorBag($errorKey);
    }

    public function render(AcademicService $academicService)
    {
        $currentYear = AcademicYear::current();
        $user = auth()->user();

        if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')->where('academic_year_id', $currentYear->id)->get()
                : collect();
            $subjects = Subject::orderBy('name')->get();
        } else {
            $assignedClassIds = $user->assignedClassIds();
            $classes = $currentYear
                ? ClassAcademicYear::with('schoolClass')
                    ->where('academic_year_id', $currentYear->id)
                    ->whereIn('school_class_id', $assignedClassIds)
                    ->get()
                    : collect();

            if ($user->isFormTeacher()) {
                $subjects = Subject::orderBy('name')->get();
            } else {
                $assignedSubjectIds = $user->assignedSubjectIds();
                $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
            }
        }

        $components = $currentYear
            ? AssessmentComponent::where('academic_year_id', $currentYear->id)->get()
            : collect();

        $students = collect();
        $gradeScales = [];
        $level = 'primary';
        
        if ($this->selectedClassId) {
            $classYear = ClassAcademicYear::with('schoolClass')->find($this->selectedClassId);
            if ($classYear) {
                $students = $classYear->students()->orderBy('last_name')->orderBy('first_name')->get();
                if ($classYear->schoolClass) {
                    $level = $classYear->schoolClass->level ?? 'primary';
                    $gradeScales = \App\Models\GradeScale::where('level', $level)->get()->toArray();
                }
            }
        }

        return view('livewire.academics.gradebook', [
            'classes' => $classes,
            'subjects' => $subjects,
            'components' => $components,
            'students' => $students,
            'gradeScales' => $gradeScales,
            'level' => $level,
        ]);
    }
}
