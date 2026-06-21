<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\ClassAcademicYear;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Livewire\Component;

class MarkAttendance extends Component
{
    public $selectedClassId = '';
    public $date;
    public $students = [];
    public $statuses = [];
    public $remarks = [];
    public $classAcademicYearId = null;
    public $saved = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSelectedClassId()
    {
        $this->loadStudents();
    }

    public function updatedDate()
    {
        $this->loadStudents();
    }

    public function loadStudents()
    {
        $this->saved = false;
        $this->students = [];
        $this->statuses = [];
        $this->remarks = [];
        $this->classAcademicYearId = null;

        if (!$this->selectedClassId || !$this->date) {
            return;
        }

        $user = auth()->user();
        if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $isFormTeacher = $user->teacherAssignments()
                ->where('class_id', $this->selectedClassId)
                ->where('is_form_teacher', true)
                ->exists();
            if (!$isFormTeacher) {
                $this->selectedClassId = '';
                return;
            }
        } else {
            if (!$user->canAccessClass($this->selectedClassId)) {
                $this->selectedClassId = '';
                return;
            }
        }

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) return;

        $classAcYear = ClassAcademicYear::where('school_class_id', $this->selectedClassId)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if (!$classAcYear) return;

        $this->classAcademicYearId = $classAcYear->id;

        // Load enrolled students
        $enrolledStudents = $classAcYear->students()->orderBy('last_name')->orderBy('first_name')->get();

        // Load existing attendance for this date
        $existingAttendance = Attendance::where('class_academic_year_id', $classAcYear->id)
            ->whereDate('date', Carbon::parse($this->date)->toDateString())
            ->get()
            ->keyBy('student_id');

        $this->students = $enrolledStudents->toArray();

        foreach ($enrolledStudents as $student) {
            $existing = $existingAttendance->get($student->id);
            $this->statuses[$student->id] = $existing ? $existing->status : 'present';
            $this->remarks[$student->id] = $existing ? $existing->remarks : '';
        }
    }

    public function save()
    {
        if (!$this->classAcademicYearId || empty($this->students)) {
            return;
        }

        $user = auth()->user();
        $classAY = ClassAcademicYear::find($this->classAcademicYearId);
        if (!$classAY) {
            return;
        }

        if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $isFormTeacher = $user->teacherAssignments()
                ->where('class_id', $classAY->school_class_id)
                ->where('is_form_teacher', true)
                ->exists();
            if (!$isFormTeacher) {
                return;
            }
        } else {
            if (!$user->canAccessClass($classAY->school_class_id)) {
                return;
            }
        }

        $parsedDate = Carbon::parse($this->date)->toDateString();

        foreach ($this->students as $student) {
            $studentId = $student['id'];
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $parsedDate,
                ],
                [
                    'class_academic_year_id' => $this->classAcademicYearId,
                    'status' => $this->statuses[$studentId] ?? 'present',
                    'remarks' => $this->remarks[$studentId] ?? null,
                ]
            );
        }

        $this->saved = true;
    }

    public function markAllPresent()
    {
        foreach ($this->students as $student) {
            $this->statuses[$student['id']] = 'present';
        }
    }

    public function render()
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            $classes = SchoolClass::orderBy('display_order')->get();
        } else {
            $formClassIds = $user->teacherAssignments()
                ->where('is_form_teacher', true)
                ->pluck('class_id')
                ->toArray();
            $classes = SchoolClass::whereIn('id', $formClassIds)->orderBy('display_order')->get();
        }

        $summary = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
        ];

        foreach ($this->statuses as $status) {
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        return view('livewire.mark-attendance', compact('classes', 'summary'));
    }
}
