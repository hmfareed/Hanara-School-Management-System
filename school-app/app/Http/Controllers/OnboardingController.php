<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Show the teacher onboarding form.
     * Teachers with 0 assignments are redirected here to configure their class and subjects.
     */
    public function showForm()
    {
        $user = auth()->user();

        // Only ClassTeacher and SubjectTeacher need onboarding
        if (!$user->hasAnyRole(['ClassTeacher', 'SubjectTeacher'])) {
            return redirect()->route('dashboard');
        }

        // If user already has assignments, skip onboarding
        if ($user->teacherAssignments()->count() > 0) {
            return redirect()->route('dashboard');
        }

        $classes = SchoolClass::orderBy('display_order')->get();
        $subjects = Subject::orderBy('name')->get();
        $isFormTeacher = $user->hasRole('ClassTeacher');

        return view('auth.onboarding-teacher', compact('classes', 'subjects', 'isFormTeacher'));
    }

    /**
     * Handle submission of the onboarding form.
     */
    public function submitForm(Request $request)
    {
        $user = auth()->user();
        $isFormTeacher = $user->hasRole('ClassTeacher');

        $rules = [
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ];

        if ($isFormTeacher) {
            $rules['form_class_id'] = ['required', 'exists:school_classes,id'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($user, $isFormTeacher, $validated) {
            // Clear any existing assignments (safety check)
            $user->teacherAssignments()->delete();

            // 1. If form teacher, create the form-teacher assignment
            if ($isFormTeacher && !empty($validated['form_class_id'])) {
                TeacherAssignment::create([
                    'user_id' => $user->id,
                    'class_id' => $validated['form_class_id'],
                    'subject_id' => null,
                    'is_form_teacher' => true,
                ]);
            }

            // 2. Create subject-teaching assignments from cross-product of class_ids and subject_ids
            if (!empty($validated['class_ids']) && !empty($validated['subject_ids'])) {
                foreach ($validated['class_ids'] as $classId) {
                    foreach ($validated['subject_ids'] as $subjectId) {
                        TeacherAssignment::create([
                            'user_id' => $user->id,
                            'class_id' => $classId,
                            'subject_id' => $subjectId,
                            'is_form_teacher' => false,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('dashboard')
            ->with('success', 'Your teaching profile has been configured successfully! Welcome to Hanara Schools.');
    }
}
