<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Staff;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StaffCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        // General common validations
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:Staff,Parent,Student'],
        ];

        // Conditional validations based on Role
        if ($request->input('role') === 'Staff') {
            $rules['staff_pin'] = ['required', 'string'];
            $rules['position'] = ['required', 'string', 'in:Principal,Form Master,Subject Teacher,Accountant,Supervisor'];
            $rules['gender'] = ['required', 'string', 'in:male,female'];
            $rules['date_of_birth'] = ['required', 'date'];
            $rules['phone'] = ['required', 'string', 'max:20'];
        } elseif ($request->input('role') === 'Parent') {
            $rules['phone'] = ['required', 'string', 'max:20'];
            $rules['address'] = ['required', 'string', 'max:500'];
            $rules['relationship'] = ['required', 'string', 'in:Father,Mother,Guardian'];
            $rules['student_id_number'] = ['nullable', 'string'];
            $rules['student_date_of_birth'] = ['required_with:student_id_number', 'nullable', 'date'];
        } elseif ($request->input('role') === 'Student') {
            $rules['student_id_number'] = ['required', 'string'];
            $rules['student_date_of_birth'] = ['required', 'date'];
        }

        $validated = $request->validate($rules);

        return DB::transaction(function () use ($request, $validated) {
            $roleType = $request->input('role');
            $user = null;

            if ($roleType === 'Staff') {
                // 1. Verify staff PIN code
                $pinCode = StaffCode::where('code', $validated['staff_pin'])
                    ->where('is_used', false)
                    ->first();

                if (!$pinCode) {
                    return back()->withErrors(['staff_pin' => 'The provided registration PIN is invalid or has already been used.'])->withInput();
                }

                // 2. Create Staff record
                $positionMap = [
                    'Principal' => 'Head Teacher',
                    'Form Master' => 'Class Teacher',
                    'Subject Teacher' => 'Subject Teacher',
                    'Accountant' => 'Bursar',
                    'Supervisor' => 'Supervisor',
                ];

                $position = $positionMap[$validated['position']] ?? 'Subject Teacher';

                $staff = Staff::create([
                    'staff_id_number' => Staff::generateStaffId(),
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'position' => $position,
                    'gender' => $validated['gender'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'date_joined' => now()->toDateString(),
                    'status' => 'pending',
                ]);

                // 3. Create User account
                $user = User::create([
                    'name' => "{$validated['first_name']} {$validated['last_name']}",
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'userable_type' => Staff::class,
                    'userable_id' => $staff->id,
                    'must_change_password' => false,
                ]);

                // 4. Determine Spatie Role & Assign
                $roleMap = [
                    'Head Teacher' => 'HeadTeacher',
                    'Class Teacher' => 'ClassTeacher',
                    'Subject Teacher' => 'SubjectTeacher',
                    'Bursar' => 'Accounts',
                    'Supervisor' => 'Supervisor',
                ];

                $roleName = $roleMap[$position] ?? 'SubjectTeacher';

                // Dynamically ensure role exists
                if ($roleName === 'Supervisor') {
                    $supervisorRole = Role::findOrCreate('Supervisor');
                    $supervisorRole->givePermissionTo([
                        'students.view', 'attendance.view', 'attendance.report',
                        'grades.view', 'grades.report', 'staff.view', 'reports.dashboard',
                    ]);
                }

                $user->assignRole($roleName);

                // 6. Staff accounts go to the waitlist — do NOT auto-login
                return redirect()->route('login')->with('success', 'Your account has been created and is pending approval by the school administration. You will be notified once approved.');

            } elseif ($roleType === 'Parent') {
                // 1. If child details provided, check and verify first
                $student = null;
                if (!empty($validated['student_id_number'])) {
                    $student = Student::where('student_id_number', $validated['student_id_number'])->first();

                    if (!$student) {
                        return back()->withErrors(['student_id_number' => 'Student ID not found. Please contact the front desk.'])->withInput();
                    }

                    // Verify DOB matches (format comparisons can be sensitive, so format both to Y-m-d)
                    $inputDob = date('Y-m-d', strtotime($validated['student_date_of_birth']));
                    $studentDob = $student->date_of_birth->format('Y-m-d');

                    if ($inputDob !== $studentDob) {
                        return back()->withErrors(['student_date_of_birth' => 'Provided Date of Birth does not match our records for that student.'])->withInput();
                    }
                }

                // 2. Create Guardian record
                $guardian = Guardian::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'relationship' => $validated['relationship'],
                    'address' => $validated['address'],
                    'is_emergency_contact' => true,
                ]);

                // 3. Link child if verified
                if ($student) {
                    $guardian->students()->attach($student->id, ['is_primary' => true]);
                }

                // 4. Create User account
                $user = User::create([
                    'name' => "{$validated['first_name']} {$validated['last_name']}",
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'userable_type' => Guardian::class,
                    'userable_id' => $guardian->id,
                    'must_change_password' => false,
                ]);

                // 5. Assign Spatie Role
                $user->assignRole('Parent');

            } elseif ($roleType === 'Student') {
                // 1. Verify Student ID exists
                $student = Student::where('student_id_number', $validated['student_id_number'])->first();

                if (!$student) {
                    return back()->withErrors(['student_id_number' => 'Student ID not found. Please contact the front desk.'])->withInput();
                }

                // 2. Verify DOB matches
                $inputDob = date('Y-m-d', strtotime($validated['student_date_of_birth']));
                $studentDob = $student->date_of_birth->format('Y-m-d');

                if ($inputDob !== $studentDob) {
                    return back()->withErrors(['student_date_of_birth' => 'Provided Date of Birth does not match our records for this student ID.'])->withInput();
                }

                // 3. Verify student doesn't already have an active User account
                $existingUser = User::where('userable_type', Student::class)
                    ->where('userable_id', $student->id)
                    ->exists();

                if ($existingUser) {
                    return back()->withErrors(['student_id_number' => 'This student already has a registered login portal account.'])->withInput();
                }

                // 4. Create User account
                $user = User::create([
                    'name' => $student->full_name,
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'userable_type' => Student::class,
                    'userable_id' => $student->id,
                    'must_change_password' => false,
                ]);

                // 5. Assign Role (ensure role exists)
                Role::findOrCreate('Student');
                $user->assignRole('Student');
            }

            // Authenticate user and log in
            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard');
            }

            return redirect()->route('login');
        });
    }
}
