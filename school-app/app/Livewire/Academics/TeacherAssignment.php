<?php

namespace App\Livewire\Academics;

use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\ClassSubjectTeacher;
use App\Models\Staff;
use App\Models\Subject;
use Livewire\Component;

class TeacherAssignment extends Component
{
    public $selectedClassId;
    public $assignments = []; // subject_id => staff_id

    public function mount()
    {
        $currentYear = AcademicYear::current();
        if ($currentYear) {
            $firstClass = ClassAcademicYear::where('academic_year_id', $currentYear->id)->first();
            if ($firstClass) {
                $this->selectedClassId = $firstClass->id;
                $this->loadAssignments();
            }
        }
    }

    public function updatedSelectedClassId()
    {
        $this->loadAssignments();
    }

    public function loadAssignments()
    {
        $this->assignments = [];
        if (!$this->selectedClassId) return;

        $existing = ClassSubjectTeacher::where('class_academic_year_id', $this->selectedClassId)
            ->pluck('staff_id', 'subject_id')
            ->toArray();

        $this->assignments = $existing;
    }

    public function saveAssignment($subjectId, $staffId)
    {
        if (!$this->selectedClassId) return;

        if (empty($staffId)) {
            // Remove assignment
            ClassSubjectTeacher::where('class_academic_year_id', $this->selectedClassId)
                ->where('subject_id', $subjectId)
                ->delete();
            unset($this->assignments[$subjectId]);
            return;
        }

        ClassSubjectTeacher::updateOrCreate(
            [
                'class_academic_year_id' => $this->selectedClassId,
                'subject_id' => $subjectId,
            ],
            [
                'staff_id' => $staffId,
            ]
        );

        $this->assignments[$subjectId] = $staffId;
    }

    public function render()
    {
        $currentYear = AcademicYear::current();
        $classes = $currentYear
            ? ClassAcademicYear::with('schoolClass')->where('academic_year_id', $currentYear->id)->get()
            : collect();

        $subjects = Subject::orderBy('name')->get();
        $teachers = Staff::orderBy('last_name')->orderBy('first_name')->get();

        return view('livewire.academics.teacher-assignment', [
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
        ]);
    }
}
