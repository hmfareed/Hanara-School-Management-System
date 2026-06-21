<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\ClassStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    /**
     * Show the public admissions form.
     */
    public function create()
    {
        $classes = SchoolClass::orderBy('display_order')->get();
        return view('admissions.apply', compact('classes'));
    }

    /**
     * Store a public admission application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'level' => ['required', 'in:nursery,kindergarten,primary,jhs'],
            'assigned_class_id' => ['required', 'exists:school_classes,id'],
            'guardian_name' => ['required', 'string', 'max:255'],
            'guardian_phone' => ['required', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['required', 'string', 'max:255'],
        ]);

        Admission::create($validated);

        return redirect()->route('admissions.apply')
            ->with('success', 'Your application has been submitted successfully. The school will contact you shortly.');
    }

    /**
     * Display admissions queue.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $level = $request->input('level');

        $query = Admission::query()->where('status', $status);

        if ($level) {
            $query->where('level', $level);
        }

        $admissions = $query->orderBy('created_at', 'desc')->paginate(15);
        $classes = SchoolClass::orderBy('display_order')->get();

        return view('admissions.index', compact('admissions', 'classes', 'status', 'level'));
    }

    /**
     * Show detailed admission.
     */
    public function show(Admission $admission)
    {
        $classes = SchoolClass::orderBy('display_order')->get();
        return view('admissions.show', compact('admission', 'classes'));
    }

    /**
     * Approve admission and enroll student.
     */
    public function approve(Request $request, Admission $admission)
    {
        $request->validate([
            'assigned_class_id' => ['required', 'exists:school_classes,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $currentYear = AcademicYear::where('is_current', true)->first();
        if (!$currentYear) {
            return back()->withErrors(['assigned_class_id' => 'No active academic year configured. Please set up the current year in Settings first.']);
        }

        $classAcYear = ClassAcademicYear::where('school_class_id', $request->assigned_class_id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        if (!$classAcYear) {
            return back()->withErrors(['assigned_class_id' => 'The selected class is not active for the current academic year.']);
        }

        DB::beginTransaction();

        try {
            // 1. Create or Find Guardian
            $phone = $admission->guardian_phone;
            $guardian = Guardian::where('phone', $phone)->first();

            if (!$guardian) {
                // Split guardian name to first/last name
                $nameParts = explode(' ', trim($admission->guardian_name), 2);
                $gFirst = $nameParts[0];
                $gLast = $nameParts[1] ?? $admission->last_name; // Fallback to student last name

                $guardian = Guardian::create([
                    'first_name' => $gFirst,
                    'last_name' => $gLast,
                    'phone' => $phone,
                    'email' => $admission->guardian_email,
                    'relationship' => $admission->guardian_relationship,
                    'is_emergency_contact' => true,
                ]);
            }

            // 2. Create Student
            $student = Student::create([
                'student_id_number' => Student::generateStudentId(),
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,
                'other_names' => $admission->other_names,
                'date_of_birth' => $admission->date_of_birth,
                'gender' => $admission->gender,
                'nationality' => 'Ghanaian',
                'admission_date' => now(),
                'status' => 'active',
            ]);

            // 3. Connect Student and Guardian
            $student->guardians()->attach($guardian->id, ['is_primary' => true]);

            // 4. Enroll Student in Class
            ClassStudent::create([
                'student_id' => $student->id,
                'class_academic_year_id' => $classAcYear->id,
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]);

            // 5. Update Admission Status
            $admission->update([
                'status' => 'accepted',
                'assigned_class_id' => $request->assigned_class_id,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admissions.index')
                ->with('success', "Admission approved successfully. Student {$student->full_name} has been enrolled in {$classAcYear->schoolClass->name} (ID: {$student->student_id_number}).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['assigned_class_id' => 'Error enrolling student: ' . $e->getMessage()]);
        }
    }

    /**
     * Decline admission application.
     */
    public function decline(Request $request, Admission $admission)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $admission->update([
            'status' => 'declined',
            'notes' => $request->notes,
        ]);

        return redirect()->route('admissions.index')
            ->with('success', 'Admission application has been marked as declined.');
    }
}
