<?php

namespace App\Livewire\Academics;

use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\TimetableSlot;
use App\Services\AcademicService;
use Livewire\Component;

class TimetableBuilder extends Component
{
    public $selectedClassId;
    public $day = 'monday';
    public $subjectId;
    public $startTime;
    public $endTime;
    public $room;

    public function mount()
    {
        $currentYear = AcademicYear::current();
        if ($currentYear) {
            $firstClass = ClassAcademicYear::where('academic_year_id', $currentYear->id)->first();
            if ($firstClass) {
                $this->selectedClassId = $firstClass->id;
            }
        }
    }

    public function addSlot(AcademicService $academicService)
    {
        $this->validate([
            'selectedClassId' => 'required',
            'subjectId' => 'required',
            'startTime' => 'required',
            'endTime' => 'required|after:startTime',
            'day' => 'required',
        ]);

        // Find assigned teacher
        $assignment = ClassSubjectTeacher::where('class_academic_year_id', $this->selectedClassId)
            ->where('subject_id', $this->subjectId)
            ->first();

        if (!$assignment) {
            $this->addError('subjectId', 'No teacher assigned to this subject. Please assign a teacher first.');
            return;
        }

        if ($academicService->hasClash($this->selectedClassId, $assignment->staff_id, $this->day, $this->startTime, $this->endTime)) {
            $this->addError('startTime', 'Clash detected: Teacher or Class is already scheduled at this time.');
            return;
        }

        TimetableSlot::create([
            'class_academic_year_id' => $this->selectedClassId,
            'subject_id' => $this->subjectId,
            'staff_id' => $assignment->staff_id,
            'day_of_week' => $this->day,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'room' => $this->room,
        ]);

        $this->reset(['startTime', 'endTime', 'room', 'subjectId']);
        session()->flash('success', 'Slot added successfully.');
    }

    public function deleteSlot($id)
    {
        TimetableSlot::findOrFail($id)->delete();
    }

    public function render()
    {
        $currentYear = AcademicYear::current();
        $classes = $currentYear
            ? ClassAcademicYear::with('schoolClass')->where('academic_year_id', $currentYear->id)->get()
            : collect();

        $subjects = Subject::orderBy('name')->get();

        $slots = collect();
        if ($this->selectedClassId) {
            $slots = TimetableSlot::with(['subject', 'teacher'])
                ->where('class_academic_year_id', $this->selectedClassId)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        return view('livewire.academics.timetable-builder', [
            'classes' => $classes,
            'subjects' => $subjects,
            'slots' => $slots,
            'days' => $days,
        ]);
    }
}
