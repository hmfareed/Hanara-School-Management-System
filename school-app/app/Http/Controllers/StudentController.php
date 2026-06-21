<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students with filters and search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $status = $request->input('status', 'active');
        $user = auth()->user();

        $query = Student::query();

        // RBAC: Scope student list based on role
        if ($user->hasAnyRole(['ClassTeacher'])) {
            // Form teachers see only students in their assigned class
            $assignedClassIds = $user->assignedClassIds();
            $currentYear = AcademicYear::where('is_current', true)->first();
            if ($currentYear && !empty($assignedClassIds)) {
                $query->whereHas('classEnrollments', function ($q) use ($assignedClassIds, $currentYear) {
                    $q->where('status', 'enrolled')
                      ->whereHas('classAcademicYear', function ($cQ) use ($assignedClassIds, $currentYear) {
                          $cQ->whereIn('school_class_id', $assignedClassIds)
                             ->where('academic_year_id', $currentYear->id);
                      });
                });
            }
        } elseif ($user->hasRole('SubjectTeacher') && !$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor'])) {
            // Subject teachers see only students in their assigned classes
            $assignedClassIds = $user->assignedClassIds();
            $currentYear = AcademicYear::where('is_current', true)->first();
            if ($currentYear && !empty($assignedClassIds)) {
                $query->whereHas('classEnrollments', function ($q) use ($assignedClassIds, $currentYear) {
                    $q->where('status', 'enrolled')
                      ->whereHas('classAcademicYear', function ($cQ) use ($assignedClassIds, $currentYear) {
                          $cQ->whereIn('school_class_id', $assignedClassIds)
                             ->where('academic_year_id', $currentYear->id);
                      });
                });
            }
        }
        // Proprietor, HeadTeacher, Supervisor, FrontDesk, Accounts: see all students (no scoping)

        // Search by ID or Name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_id_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('other_names', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by Class (current academic year)
        if ($classId) {
            $currentYear = AcademicYear::where('is_current', true)->first();
            $query->whereHas('classEnrollments', function ($q) use ($classId, $currentYear) {
                $q->where('status', 'enrolled')
                  ->whereHas('classAcademicYear', function ($cQ) use ($classId, $currentYear) {
                      $cQ->where('school_class_id', $classId);
                      if ($currentYear) {
                          $cQ->where('academic_year_id', $currentYear->id);
                      }
                  });
            });
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(15);
        $classes = SchoolClass::orderBy('display_order')->get();

        // Determine read-only mode for Supervisor
        $isReadOnly = $user->hasRole('Supervisor');

        return view('students.index', compact('students', 'classes', 'search', 'classId', 'status', 'isReadOnly'));
    }

    public function show(Student $student)
    {
        $user = auth()->user();
        $currentEnrollment = $student->currentClassEnrollment();

        // Authorize access for scoped roles (ClassTeacher, SubjectTeacher)
        if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'FrontDesk', 'Accounts'])) {
            if ($currentEnrollment) {
                if (!$user->canAccessClass($currentEnrollment->classAcademicYear->school_class_id)) {
                    abort(403, 'Unauthorized to view this student\'s profile.');
                }
            } else {
                abort(403, 'Unauthorized to view this student.');
            }
        }

        // Load guardians
        $student->load('guardians');

        // Load sibling list (other students linked to any of this student's guardians)
        $guardianIds = $student->guardians->pluck('id');
        $siblings = collect();
        if ($guardianIds->isNotEmpty()) {
            $siblings = Student::where('id', '!=', $student->id)
                ->whereHas('guardians', function ($q) use ($guardianIds) {
                    $q->whereIn('guardians.id', $guardianIds);
                })
                ->get();
        }

        // Load all class enrollments historically
        $enrollmentHistory = $student->classEnrollments()
            ->with('classAcademicYear.schoolClass', 'classAcademicYear.academicYear')
            ->orderBy('enrolled_at', 'desc')
            ->get();

        // Load invoices and their payments
        $invoices = $student->invoices()
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        // Load attendance log (grouped or paginated)
        $attendanceRecords = \App\Models\Attendance::where('student_id', $student->id)
            ->with('classAcademicYear.schoolClass')
            ->orderBy('date', 'desc')
            ->paginate(15, ['*'], 'attendance_page');

        // Calculate attendance summary
        $totalDays = \App\Models\Attendance::where('student_id', $student->id)->count();
        $presentDays = \App\Models\Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $lateDays = \App\Models\Attendance::where('student_id', $student->id)->where('status', 'late')->count();
        $absentDays = \App\Models\Attendance::where('student_id', $student->id)->where('status', 'absent')->count();
        
        $attendanceRate = $totalDays > 0 
            ? round((($presentDays + $lateDays) / $totalDays) * 100, 1) 
            : 100.0;

        // Load classes, academic years and terms for transfer modal
        $classes = SchoolClass::orderBy('display_order')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return view('students.show', compact(
            'student', 
            'currentEnrollment', 
            'siblings', 
            'enrollmentHistory', 
            'invoices', 
            'attendanceRecords',
            'attendanceRate',
            'totalDays',
            'presentDays',
            'lateDays',
            'absentDays',
            'classes',
            'academicYears'
        ));
    }

    /**
     * Show the roster import form.
     */
    public function importForm()
    {
        return view('students.import');
    }

    /**
     * Handle the roster import upload.
     */
    public function import(Request $request)
    {
        $request->validate([
            'roster_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
        ]);

        $file = $request->file('roster_file');
        $importer = new StudentsImport();

        if ($importer->import($file->getRealPath())) {
            return redirect()->route('students.index')
                ->with('success', "Import completed successfully! {$importer->getSuccessCount()} student(s) imported.");
        }

        return back()->withErrors($importer->getErrors())->withInput();
    }

    /**
     * Generate and download a high-fidelity PDF student ID card.
     */
    public function printIdCard(Student $student)
    {
        $user = auth()->user();
        $student->load(['classEnrollments.classAcademicYear.schoolClass']);
        $currentEnrollment = $student->currentClassEnrollment();

        // Authorize access for scoped roles (ClassTeacher, SubjectTeacher)
        if (!$user->hasAnyRole(['Proprietor', 'HeadTeacher', 'Supervisor', 'FrontDesk', 'Accounts'])) {
            if ($currentEnrollment) {
                if (!$user->canAccessClass($currentEnrollment->classAcademicYear->school_class_id)) {
                    abort(403, 'Unauthorized to print ID card for this student.');
                }
            } else {
                abort(403, 'Unauthorized to access this student.');
            }
        }

        $className = $currentEnrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.id-card', compact('student', 'className'));
        $pdf->setPaper([0, 0, 240, 380], 'portrait');

        return $pdf->download("student-id-{$student->student_id_number}.pdf");
    }

    /**
     * Generate and download a full academic transcript PDF.
     */
    public function generateTranscript(Student $student)
    {
        $student->load(['guardians', 'classEnrollments.classAcademicYear.schoolClass', 'classEnrollments.classAcademicYear.academicYear']);

        // Gather all assessment scores grouped by academic year and term
        $academicHistory = [];
        foreach ($student->classEnrollments as $enrollment) {
            $cay = $enrollment->classAcademicYear;
            if (!$cay) continue;

            $yearLabel = $cay->academicYear->name ?? 'Unknown';
            $className = $cay->schoolClass->name ?? 'Unknown';

            $scores = \App\Models\AssessmentScore::where('student_id', $student->id)
                ->where('class_academic_year_id', $cay->id)
                ->with(['subject', 'component'])
                ->get()
                ->groupBy('subject.name');

            $subjectResults = [];
            foreach ($scores as $subjectName => $subjectScores) {
                $totalWeight = $subjectScores->sum(fn($s) => $s->component?->weight ?? 0);
                $weightedScore = $subjectScores->sum(function ($s) {
                    $maxScore = $s->component?->max_score ?? 100;
                    $weight = $s->component?->weight ?? 100;
                    return $maxScore > 0 ? ($s->score / $maxScore) * $weight : 0;
                });

                $percentage = $totalWeight > 0 ? round(($weightedScore / $totalWeight) * 100, 1) : 0;

                // Look up grade
                $level = $cay->schoolClass->level ?? 'Primary';
                $grade = \App\Models\GradeScale::lookup($percentage, $level);

                $subjectResults[] = [
                    'subject' => $subjectName,
                    'score' => $percentage,
                    'grade' => $grade?->grade ?? 'N/A',
                    'remarks' => $grade?->remarks ?? '',
                ];
            }

            if (!empty($subjectResults)) {
                $academicHistory[] = [
                    'year' => $yearLabel,
                    'class' => $className,
                    'subjects' => $subjectResults,
                ];
            }
        }

        // Save transcript record
        \App\Models\Transcript::create([
            'student_id' => $student->id,
            'generated_by' => auth()->id(),
            'type' => 'transcript',
            'data' => $academicHistory,
            'generated_at' => now(),
        ]);

        $currentEnrollment = $student->currentClassEnrollment();
        $schoolName = \App\Models\Setting::get('school_name', 'Hanara Schools');
        $schoolMotto = \App\Models\Setting::get('school_motto', 'Excellence in Education');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.transcript', compact(
            'student', 'academicHistory', 'currentEnrollment', 'schoolName', 'schoolMotto'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("transcript-{$student->student_id_number}.pdf");
    }

    /**
     * Generate and download a testimonial PDF for a leaver.
     */
    public function generateTestimonial(Student $student)
    {
        $student->load(['guardians', 'classEnrollments.classAcademicYear.schoolClass']);

        $currentEnrollment = $student->currentClassEnrollment();
        $className = $currentEnrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned';

        $schoolName = \App\Models\Setting::get('school_name', 'Hanara Schools');
        $schoolMotto = \App\Models\Setting::get('school_motto', 'Excellence in Education');
        $headTeacher = \App\Models\Setting::get('head_teacher_name', 'Head Teacher');

        // Calculate years attended
        $yearsAttended = $student->admission_date
            ? $student->admission_date->diffInYears(now())
            : 0;

        // Save testimonial record
        \App\Models\Transcript::create([
            'student_id' => $student->id,
            'generated_by' => auth()->id(),
            'type' => 'testimonial',
            'data' => [
                'class' => $className,
                'years_attended' => $yearsAttended,
                'generated_date' => now()->toDateString(),
            ],
            'generated_at' => now(),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.testimonial', compact(
            'student', 'className', 'schoolName', 'schoolMotto', 'headTeacher', 'yearsAttended'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("testimonial-{$student->student_id_number}.pdf");
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $classes = SchoolClass::orderBy('display_order')->get();
        $guardians = \App\Models\Guardian::orderBy('last_name')->orderBy('first_name')->get();
        $currentYear = AcademicYear::where('is_current', true)->first();

        return view('students.create', compact('classes', 'guardians', 'currentYear'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Student details
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'admission_date' => 'required|date',
            'school_class_id' => 'required|exists:school_classes,id',
            'nationality' => 'required|string|max:255',
            'religion' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'medical_notes' => 'nullable|string',
            // Guardian details
            'guardian_mode' => 'required|in:new,existing',
            'guardian_first_name' => 'required_if:guardian_mode,new|nullable|string|max:255',
            'guardian_last_name' => 'required_if:guardian_mode,new|nullable|string|max:255',
            'guardian_phone' => 'required_if:guardian_mode,new|nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'guardian_relationship' => 'required_if:guardian_mode,new|nullable|string|max:255',
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_id' => 'required_if:guardian_mode,existing|nullable|exists:guardians,id',
        ]);

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            return back()->withErrors(['school_class_id' => 'No active academic year found. Please configure one.'])->withInput();
        }

        $classAcademicYear = \App\Models\ClassAcademicYear::where('school_class_id', $request->school_class_id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if (!$classAcademicYear) {
            return back()->withErrors(['school_class_id' => 'The selected class is not activated for the current academic year.'])->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Create or resolve guardian
            if ($request->guardian_mode === 'new') {
                $guardian = \App\Models\Guardian::create([
                    'first_name' => $request->guardian_first_name,
                    'last_name' => $request->guardian_last_name,
                    'phone' => $request->guardian_phone,
                    'email' => $request->guardian_email,
                    'relationship' => $request->guardian_relationship,
                    'occupation' => $request->guardian_occupation,
                    'address' => $request->address, // default to student address
                    'is_emergency_contact' => true,
                ]);
            } else {
                $guardian = \App\Models\Guardian::findOrFail($request->guardian_id);
            }

            // 2. Create student
            $student = Student::create([
                'student_id_number' => Student::generateStudentId(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'nationality' => $request->nationality,
                'religion' => $request->religion,
                'blood_group' => $request->blood_group,
                'medical_notes' => $request->medical_notes,
                'admission_date' => $request->admission_date,
                'status' => 'active',
            ]);

            // 3. Connect student to guardian
            $student->guardians()->attach($guardian->id, [
                'is_primary' => true,
            ]);

            // 4. Enroll student
            \App\Models\ClassStudent::create([
                'student_id' => $student->id,
                'class_academic_year_id' => $classAcademicYear->id,
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]);

            // 5. Audit Log
            \App\Models\AuditLog::log(
                'student_created',
                $student,
                null,
                $student->toArray()
            );

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('students.show', $student)
                ->with('success', "Student {$student->full_name} manually registered and enrolled in {$classAcademicYear->schoolClass->name} successfully. ID: {$student->student_id_number}.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Error registering student: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing a student.
     */
    public function edit(Student $student)
    {
        $classes = SchoolClass::orderBy('display_order')->get();
        $currentEnrollment = $student->currentClassEnrollment();

        return view('students.edit', compact('student', 'classes', 'currentEnrollment'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'admission_date' => 'required|date',
            'nationality' => 'required|string|max:255',
            'religion' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'medical_notes' => 'nullable|string',
            'status' => 'required|in:active,graduated,transferred,withdrawn',
        ]);

        $oldValues = $student->toArray();

        $student->update($request->only([
            'first_name', 'last_name', 'other_names', 'date_of_birth',
            'gender', 'address', 'admission_date', 'nationality',
            'religion', 'blood_group', 'medical_notes', 'status',
        ]));

        \App\Models\AuditLog::log(
            'student_updated',
            $student,
            $oldValues,
            $student->fresh()->toArray()
        );

        return redirect()->route('students.show', $student)
            ->with('success', "Student {$student->full_name}'s profile has been updated successfully.");
    }

    /**
     * Show the class promotions management page.
     */
    public function promotionForm()
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $classes = SchoolClass::orderBy('display_order')->get();

        $classData = [];
        foreach ($classes as $index => $class) {
            $studentCount = 0;
            if ($currentYear) {
                $cay = \App\Models\ClassAcademicYear::where('school_class_id', $class->id)
                    ->where('academic_year_id', $currentYear->id)
                    ->first();

                if ($cay) {
                    $studentCount = \App\Models\ClassStudent::where('class_academic_year_id', $cay->id)
                        ->where('status', 'enrolled')
                        ->count();
                }
            }

            $nextClass = $classes->where('display_order', '>', $class->display_order)
                ->sortBy('display_order')
                ->first();

            $classData[] = [
                'class' => $class,
                'student_count' => $studentCount,
                'next_class' => $nextClass,
                'is_last' => $nextClass === null,
            ];
        }

        return view('students.promote', compact('classData', 'currentYear'));
    }

    /**
     * Promote all students in a class to the next class.
     */
    public function promoteClass(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            return back()->withErrors(['error' => 'No active academic year found.']);
        }

        $sourceClass = SchoolClass::findOrFail($request->school_class_id);
        $sourceCay = \App\Models\ClassAcademicYear::where('school_class_id', $sourceClass->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if (!$sourceCay) {
            return back()->withErrors(['error' => "Class {$sourceClass->name} is not activated for {$currentYear->name}."]);
        }

        // Find all enrolled students in this class for the current year
        $enrollments = \App\Models\ClassStudent::where('class_academic_year_id', $sourceCay->id)
            ->where('status', 'enrolled')
            ->with('student')
            ->get();

        if ($enrollments->isEmpty()) {
            return back()->withErrors(['error' => "No enrolled students found in {$sourceClass->name}."]);
        }

        // Find the next class by display_order
        $nextClass = SchoolClass::where('display_order', '>', $sourceClass->display_order)
            ->orderBy('display_order')
            ->first();

        $isGraduation = ($nextClass === null);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            if ($isGraduation) {
                // Graduate all students
                foreach ($enrollments as $enrollment) {
                    $enrollment->update(['status' => 'promoted']);
                    $enrollment->student->update(['status' => 'graduated']);
                }

                \Illuminate\Support\Facades\DB::commit();

                return redirect()->route('students.promotions')
                    ->with('success', "{$enrollments->count()} student(s) from {$sourceClass->name} have been graduated successfully.");
            }

            // Ensure ClassAcademicYear exists for the target class
            $targetCay = \App\Models\ClassAcademicYear::firstOrCreate(
                [
                    'school_class_id' => $nextClass->id,
                    'academic_year_id' => $currentYear->id,
                ],
                ['class_teacher_id' => null]
            );

            $promotedCount = 0;
            foreach ($enrollments as $enrollment) {
                // Mark old enrollment as promoted
                $enrollment->update(['status' => 'promoted']);

                // Create new enrollment in next class
                \App\Models\ClassStudent::create([
                    'student_id' => $enrollment->student_id,
                    'class_academic_year_id' => $targetCay->id,
                    'enrolled_at' => now(),
                    'status' => 'enrolled',
                ]);

                $promotedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('students.promotions')
                ->with('success', "{$promotedCount} student(s) promoted from {$sourceClass->name} to {$nextClass->name} successfully.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Error during promotion: ' . $e->getMessage()]);
        }
    }

    /**
     * Transfer an individual student to a different class.
     */
    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'target_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $selectedYear = AcademicYear::findOrFail($request->academic_year_id);
        $selectedTerm = Term::findOrFail($request->term_id);
        $targetClass = SchoolClass::findOrFail($request->target_class_id);

        // Ensure ClassAcademicYear exists for the target class under the selected year
        $targetCay = \App\Models\ClassAcademicYear::firstOrCreate(
            [
                'school_class_id' => $targetClass->id,
                'academic_year_id' => $selectedYear->id,
            ],
            ['class_teacher_id' => null]
        );

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Find current enrollment of the student in the selected academic year
            $currentEnrollment = $student->classEnrollments()
                ->whereHas('classAcademicYear', fn($q) => $q->where('academic_year_id', $selectedYear->id))
                ->first();

            $oldClassName = 'Unassigned';
            if ($currentEnrollment) {
                $oldClassName = $currentEnrollment->classAcademicYear->schoolClass->name;
                $currentEnrollment->update(['status' => 'transferred']);
            }

            // Create new enrollment
            \App\Models\ClassStudent::create([
                'student_id' => $student->id,
                'class_academic_year_id' => $targetCay->id,
                'enrolled_at' => $selectedTerm->start_date ?? now(),
                'status' => 'enrolled',
            ]);

            // Audit log with full academic year and term details
            \App\Models\AuditLog::log(
                'student_transferred',
                $student,
                [
                    'class' => $oldClassName,
                    'academic_year' => $currentEnrollment?->classAcademicYear?->academicYear?->name ?? $selectedYear->name
                ],
                [
                    'class' => $targetClass->name,
                    'academic_year' => $selectedYear->name,
                    'term' => $selectedTerm->name
                ]
            );

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('students.show', $student)
                ->with('success', "{$student->full_name} has been transferred from {$oldClassName} to {$targetClass->name} successfully for {$selectedYear->name} - {$selectedTerm->name}.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Error during transfer: ' . $e->getMessage()]);
        }
    }

    /**
     * Revert the latest class transfer or promotion.
     */
    public function revertTransfer(Student $student)
    {
        $history = \App\Models\ClassStudent::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->get();

        if ($history->count() < 2) {
            return back()->withErrors(['error' => 'No previous class enrollment history found to revert to.']);
        }

        $latestEnrollment = $history->first();
        $previousEnrollment = $history->skip(1)->first();

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $latestClassName = $latestEnrollment->classAcademicYear->schoolClass->name;
            $previousClassName = $previousEnrollment->classAcademicYear->schoolClass->name;

            // Delete the incorrect latest enrollment
            $latestEnrollment->delete();

            // Revert the previous enrollment's status back to 'enrolled'
            $previousEnrollment->update(['status' => 'enrolled']);

            // Ensure student is active (in case the last action had changed student status)
            if ($student->status !== 'active') {
                $student->update(['status' => 'active']);
            }

            // Audit log the reversion
            \App\Models\AuditLog::log(
                'student_transfer_reverted',
                $student,
                ['class' => $latestClassName],
                ['class' => $previousClassName]
            );

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('students.show', $student)
                ->with('success', "Transfer reverted successfully. {$student->full_name} is now returned back to {$previousClassName}.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Error reverting transfer: ' . $e->getMessage()]);
        }
    }
}
