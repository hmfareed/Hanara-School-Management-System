@extends('layouts.guest')

@section('title', 'Register - Hanara Schools')

@section('content')
<div class="flex min-h-screen w-full">
    <!-- Left: Registration Form -->
    <div class="flex flex-1 flex-col justify-center px-6 py-8 lg:flex-none lg:w-[500px] xl:w-[600px] bg-surface relative z-10 shadow-[20px_0_25px_-5px_rgba(0,0,0,0.05)] overflow-y-auto max-h-screen">
        <div class="mx-auto w-full max-w-md lg:w-[420px] py-4">
            <!-- Brand -->
            <div class="flex flex-col items-center lg:items-start text-center lg:text-left mb-6">
                <div class="h-14 w-14 bg-primary rounded-xl flex items-center justify-center mb-4 shadow-sm">
                    <span class="text-on-primary font-bold text-2xl">H</span>
                </div>
                <h1 class="font-headline-md text-headline-md text-on-surface mb-1">Create Account</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Join the Hanara Schools Portal</p>
            </div>

            <!-- Registration Form -->
            <form action="{{ route('register.attempt') }}" method="POST" class="space-y-4" id="register-form">
                @csrf

                @if($errors->any())
                    <div class="p-3 bg-error-container text-on-error-container rounded-xl text-body-md" id="register-error">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Role Selection -->
                <div>
                    <label class="form-label" for="role-select">Select your Role</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                            <span class="material-symbols-outlined" style="font-size: 20px;">account_circle</span>
                        </span>
                        <select name="role" id="role-select" required
                                class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                            <option value="">-- Choose Role --</option>
                            <option value="Staff" {{ old('role') === 'Staff' ? 'selected' : '' }}>Staff Member</option>
                            <option value="Parent" {{ old('role') === 'Parent' ? 'selected' : '' }}>Parent / Guardian</option>
                            <option value="Student" {{ old('role') === 'Student' ? 'selected' : '' }}>Student</option>
                        </select>
                    </div>
                </div>

                <!-- Shared: Name Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                               class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Kofi">
                    </div>
                    <div>
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                               class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Addo">
                    </div>
                </div>

                <!-- Shared: Email -->
                <div>
                    <label class="form-label" for="email">Email Address</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                            <span class="material-symbols-outlined" style="font-size: 20px;">mail</span>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="name@example.com">
                    </div>
                </div>

                <!-- Shared: Phone (Only for Parent or general) -->
                <div id="phone-field" class="hidden">
                    <label class="form-label" for="phone">Phone Number</label>
                    <div class="mt-1 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                            <span class="material-symbols-outlined" style="font-size: 20px;">call</span>
                        </span>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                               class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="e.g. +233 24 123 4567">
                    </div>
                </div>

                <!-- Dynamic: Staff PIN and Position -->
                <div id="staff-section" class="hidden space-y-4 pt-2 border-t border-outline-variant/60">
                    <div>
                        <label class="form-label" for="staff_pin">Staff Registration PIN</label>
                        <div class="mt-1 relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                                <span class="material-symbols-outlined" style="font-size: 20px;">key</span>
                            </span>
                            <input type="text" name="staff_pin" id="staff_pin" value="{{ old('staff_pin') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono tracking-widest shadow-sm" placeholder="Enter 6-digit PIN">
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">Get this PIN from the Proprietor/Super Admin.</p>
                    </div>

                    <div>
                        <label class="form-label" for="position">Staff Position / Role</label>
                        <div class="mt-1 relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                                <span class="material-symbols-outlined" style="font-size: 20px;">badge</span>
                            </span>
                            <select name="position" id="position"
                                    class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                                <option value="">-- Select Position --</option>
                                <option value="Principal" {{ old('position') === 'Principal' ? 'selected' : '' }}>Principal / Head Teacher</option>
                                <option value="Form Master" {{ old('position') === 'Form Master' ? 'selected' : '' }}>Form Master / Class Teacher</option>
                                <option value="Subject Teacher" {{ old('position') === 'Subject Teacher' ? 'selected' : '' }}>Subject Teacher</option>
                                <option value="Accountant" {{ old('position') === 'Accountant' ? 'selected' : '' }}>Accountant / Bursar</option>
                                <option value="Supervisor" {{ old('position') === 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="staff_gender">Gender</label>
                            <select name="gender" id="staff_gender"
                                    class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="staff_dob">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="staff_dob" value="{{ old('date_of_birth') }}"
                                   class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Dynamic: Parent Fields -->
                <div id="parent-section" class="hidden space-y-4 pt-2 border-t border-outline-variant/60">
                    <div>
                        <label class="form-label" for="relationship">Relationship to Student</label>
                        <select name="relationship" id="relationship"
                                class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                            <option value="">-- Choose Relationship --</option>
                            <option value="Father" {{ old('relationship') === 'Father' ? 'selected' : '' }}>Father</option>
                            <option value="Mother" {{ old('relationship') === 'Mother' ? 'selected' : '' }}>Mother</option>
                            <option value="Guardian" {{ old('relationship') === 'Guardian' ? 'selected' : '' }}>Guardian / Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="address">Residential Address</label>
                        <textarea name="address" id="address" rows="2"
                                  class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="House No. 12, Accra, Ghana">{{ old('address') }}</textarea>
                    </div>

                    <div class="bg-surface-container-low p-4 rounded-xl space-y-3 border border-outline-variant/40">
                        <h4 class="font-title-md text-sm font-semibold text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">family_restroom</span>
                            Link Child (Optional)
                        </h4>
                        <p class="text-xs text-on-surface-variant">Enter your child's student details to automatically link their reports.</p>
                        
                        <div>
                            <label class="text-xs font-semibold text-on-surface-variant block mb-1" for="parent_student_id">Child's Student ID</label>
                            <input type="text" name="student_id_number" id="parent_student_id" value="{{ old('student_id_number') }}"
                                   class="block w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-on-surface text-xs focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary" placeholder="e.g. HAN-2026-0001">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-on-surface-variant block mb-1" for="parent_student_dob">Child's Date of Birth</label>
                            <input type="date" name="student_date_of_birth" id="parent_student_dob" value="{{ old('student_date_of_birth') }}"
                                   class="block w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-on-surface text-xs focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>

                <!-- Dynamic: Student Fields -->
                <div id="student-section" class="hidden space-y-4 pt-2 border-t border-outline-variant/60">
                    <div class="bg-surface-container-low p-4 rounded-xl space-y-4 border border-outline-variant/40">
                        <h4 class="font-title-md text-sm font-semibold text-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">verified_user</span>
                            Student Identity Verification
                        </h4>
                        <p class="text-xs text-on-surface-variant">Enter your school-issued Student ID and Date of Birth to verify your identity.</p>

                        <div>
                            <label class="text-xs font-semibold text-on-surface-variant block mb-1" for="student_id_number">Your Student ID</label>
                            <input type="text" name="student_id_number" id="student_id_number" value="{{ old('student_id_number') }}"
                                   class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="e.g. HAN-2026-0001">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-on-surface-variant block mb-1" for="student_date_of_birth">Your Date of Birth</label>
                            <input type="date" name="student_date_of_birth" id="student_date_of_birth" value="{{ old('student_date_of_birth') }}"
                                   class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Password fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input type="password" name="password" id="password" required
                               class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="block w-full px-3 py-2.5 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="••••••••">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm font-title-lg text-title-lg text-on-secondary-container bg-secondary-container hover:bg-secondary-fixed transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-container"
                            type="submit">
                        Create Account
                    </button>
                </div>

                <div class="text-center mt-4">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">
                            Log in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Right: Illustration Panel -->
    <div class="hidden lg:flex flex-1 bg-surface-container-low relative items-center justify-center p-12 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-transparent to-surface-variant/30 pointer-events-none"></div>
        <div class="relative w-full max-w-lg flex flex-col items-center justify-center text-center">
            <!-- School illustration placeholder -->
            <div class="w-40 h-40 bg-primary-container rounded-3xl flex items-center justify-center mb-6 shadow-level-1">
                <span class="material-symbols-outlined text-on-primary-container" style="font-size: 80px;">app_registration</span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Portal Registration</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-sm">
                Get connected to the Hanara School portal to track learning, timetables, and billing.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role-select');
        const staffSection = document.getElementById('staff-section');
        const parentSection = document.getElementById('parent-section');
        const studentSection = document.getElementById('student-section');
        const phoneField = document.getElementById('phone-field');
        const staffPin = document.getElementById('staff_pin');
        const position = document.getElementById('position');
        const staffGender = document.getElementById('staff_gender');
        const staffDob = document.getElementById('staff_dob');
        const relationship = document.getElementById('relationship');
        const address = document.getElementById('address');
        const phone = document.getElementById('phone');

        function toggleSections() {
            const role = roleSelect.value;

            // Hide all first
            staffSection.classList.add('hidden');
            parentSection.classList.add('hidden');
            studentSection.classList.add('hidden');
            phoneField.classList.add('hidden');

            // Remove required/optional flags
            staffPin.removeAttribute('required');
            position.removeAttribute('required');
            staffGender.removeAttribute('required');
            staffDob.removeAttribute('required');
            relationship.removeAttribute('required');
            address.removeAttribute('required');
            phone.removeAttribute('required');

            if (role === 'Staff') {
                staffSection.classList.remove('hidden');
                phoneField.classList.remove('hidden');
                staffPin.setAttribute('required', 'required');
                position.setAttribute('required', 'required');
                staffGender.setAttribute('required', 'required');
                staffDob.setAttribute('required', 'required');
                phone.setAttribute('required', 'required');
            } else if (role === 'Parent') {
                parentSection.classList.remove('hidden');
                phoneField.classList.remove('hidden');
                relationship.setAttribute('required', 'required');
                address.setAttribute('required', 'required');
                phone.setAttribute('required', 'required');
            } else if (role === 'Student') {
                studentSection.classList.remove('hidden');
            }
        }

        roleSelect.addEventListener('change', toggleSections);
        // Trigger on load for keeping values after validation errors
        toggleSections();
    });
</script>
@endsection
