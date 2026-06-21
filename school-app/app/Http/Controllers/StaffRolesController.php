<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Staff;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class StaffRolesController extends Controller
{
    /**
     * Display all staff members with their roles and assignments.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter', 'all'); // all, pending, active

        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor']);
        })->with(['roles', 'teacherAssignments.schoolClass', 'teacherAssignments.subject', 'userable']);

        // Filter by approval status
        if ($filter === 'pending') {
            $query->whereHasMorph('userable', [Staff::class], function ($q) {
                $q->where('status', 'pending');
            });
        } elseif ($filter === 'active') {
            $query->whereHasMorph('userable', [Staff::class], function ($q) {
                $q->where('status', 'active');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        // Count for tab badges
        $pendingCount = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor']);
        })->whereHasMorph('userable', [Staff::class], function ($q) {
            $q->where('status', 'pending');
        })->count();

        return view('staff-roles.index', compact('users', 'search', 'filter', 'pendingCount'));
    }

    /**
     * Show the waitlist of pending staff awaiting approval.
     */
    public function waitlist()
    {
        $pendingStaff = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor']);
        })->whereHasMorph('userable', [Staff::class], function ($q) {
            $q->where('status', 'pending');
        })->with(['roles', 'userable'])->orderBy('created_at', 'desc')->get();

        return view('staff-roles.waitlist', compact('pendingStaff'));
    }

    /**
     * Approve a pending staff member.
     */
    public function approve(User $user)
    {
        $staff = $user->userable;

        if (!$staff || !($staff instanceof Staff)) {
            return back()->with('error', 'Invalid staff profile.');
        }

        $staff->update(['status' => 'active']);

        return back()->with('success', "{$user->name} has been approved and can now access the system.");
    }

    /**
     * Reject/remove a pending staff member.
     */
    public function reject(User $user)
    {
        $staff = $user->userable;

        if (!$staff || !($staff instanceof Staff)) {
            return back()->with('error', 'Invalid staff profile.');
        }

        // Delete the user and staff record
        DB::transaction(function () use ($user, $staff) {
            $user->teacherAssignments()->delete();
            $user->delete();
            $staff->delete();
        });

        return back()->with('success', 'Staff member has been rejected and removed from the system.');
    }

    /**
     * Show the edit form for a staff member's role and assignments.
     */
    public function edit(User $user)
    {
        $user->load(['roles', 'teacherAssignments.schoolClass', 'teacherAssignments.subject', 'userable']);
        $classes = SchoolClass::orderBy('display_order')->get();
        $subjects = Subject::orderBy('name')->get();
        $availableRoles = ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor'];

        return view('staff-roles.edit', compact('user', 'classes', 'subjects', 'availableRoles'));
    }

    /**
     * Update a staff member's role and teaching assignments.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:HeadTeacher,ClassTeacher,SubjectTeacher,Accounts,Supervisor'],
            'form_class_id' => ['nullable', 'exists:school_classes,id'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $newRole = $validated['role'];

            // 1. Sync Spatie role
            $user->syncRoles([$newRole]);

            // 2. Update position on Staff profile
            $positionMap = [
                'HeadTeacher' => 'Head Teacher',
                'ClassTeacher' => 'Class Teacher',
                'SubjectTeacher' => 'Subject Teacher',
                'Accounts' => 'Bursar',
                'Supervisor' => 'Supervisor',
            ];

            if ($user->userable_type === Staff::class && $user->userable) {
                $user->userable->update(['position' => $positionMap[$newRole] ?? $newRole]);
            }

            // 3. Clear previous teacher assignments
            $user->teacherAssignments()->delete();

            // 4. Create form teacher assignment (only for ClassTeacher)
            if ($newRole === 'ClassTeacher' && !empty($validated['form_class_id'])) {
                TeacherAssignment::create([
                    'user_id' => $user->id,
                    'class_id' => $validated['form_class_id'],
                    'subject_id' => null,
                    'is_form_teacher' => true,
                ]);
            }

            // 5. Create subject teaching assignments from cross-product of class_ids and subject_ids
            if (($newRole === 'ClassTeacher' || $newRole === 'SubjectTeacher') &&
                !empty($validated['class_ids']) && !empty($validated['subject_ids'])) {
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

        return redirect()->route('staff-roles.index')
            ->with('success', "Assignments for {$user->name} have been updated successfully.");
    }
}
