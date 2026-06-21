@extends('layouts.guest')

@section('title', 'Apply for Admission - Hanara Schools')

@section('content')
<div class="flex min-h-screen w-full bg-background justify-center items-center py-12 px-4">
    <div class="w-full max-w-2xl card p-8">
        <!-- Brand/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-primary items-center justify-center text-on-primary font-bold text-3xl mb-4 shadow-level-1">H</div>
            <h1 class="font-headline-md text-headline-md font-semibold text-primary mb-2">Hanara Schools</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Online Admission Application Portal</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-success-container text-on-success-container rounded-xl flex items-center gap-3 border border-success/20">
                <span class="material-symbols-outlined text-success">check_circle</span>
                <p class="font-body-sm text-body-sm">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-error-container text-on-error-container rounded-xl border border-error/20">
                <div class="flex items-center gap-2 font-medium mb-2">
                    <span class="material-symbols-outlined text-error">error</span>
                    <span>Application errors detected:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 pl-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admissions.apply.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Section 1: Student Details -->
            <div>
                <h3 class="font-title-md text-title-md text-primary font-semibold border-b border-outline-variant pb-2 mb-4">1. Student Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label" for="first_name">First Name <span class="text-error">*</span></label>
                        <input class="form-input-custom" type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="last_name">Last Name <span class="text-error">*</span></label>
                        <input class="form-input-custom" type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="other_names">Middle/Other Names</label>
                        <input class="form-input-custom" type="text" name="other_names" id="other_names" value="{{ old('other_names') }}">
                    </div>
                    <div>
                        <label class="form-label" for="date_of_birth">Date of Birth <span class="text-error">*</span></label>
                        <input class="form-input-custom" type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="gender">Gender <span class="text-error">*</span></label>
                        <select class="form-input-custom" name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="level">School Level <span class="text-error">*</span></label>
                        <select class="form-input-custom" name="level" id="level" required>
                            <option value="">Select Level</option>
                            <option value="nursery" {{ old('level') === 'nursery' ? 'selected' : '' }}>Nursery / Crèche</option>
                            <option value="kindergarten" {{ old('level') === 'kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                            <option value="primary" {{ old('level') === 'primary' ? 'selected' : '' }}>Primary (P1 - P6)</option>
                            <option value="jhs" {{ old('level') === 'jhs' ? 'selected' : '' }}>Junior High School (JHS1 - JHS3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="assigned_class_id">Applying For Class <span class="text-error">*</span></label>
                        <select class="form-input-custom" name="assigned_class_id" id="assigned_class_id" required>
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ old('assigned_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Guardian Details -->
            <div>
                <h3 class="font-title-md text-title-md text-primary font-semibold border-b border-outline-variant pb-2 mb-4">2. Guardian Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label" for="guardian_name">Full Name <span class="text-error">*</span></label>
                        <input class="form-input-custom" type="text" name="guardian_name" id="guardian_name" placeholder="e.g. Kwame Mensah" value="{{ old('guardian_name') }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="guardian_phone">Primary Phone Number <span class="text-error">*</span></label>
                        <input class="form-input-custom" type="tel" name="guardian_phone" id="guardian_phone" placeholder="e.g. +233 24 412 3456" value="{{ old('guardian_phone') }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="guardian_email">Email Address</label>
                        <input class="form-input-custom" type="email" name="guardian_email" id="guardian_email" placeholder="e.g. parent@example.com" value="{{ old('guardian_email') }}">
                    </div>
                    <div>
                        <label class="form-label" for="guardian_relationship">Relationship to Student <span class="text-error">*</span></label>
                        <select class="form-input-custom" name="guardian_relationship" id="guardian_relationship" required>
                            <option value="">Select Relationship</option>
                            <option value="Father" {{ old('guardian_relationship') === 'Father' ? 'selected' : '' }}>Father</option>
                            <option value="Mother" {{ old('guardian_relationship') === 'Mother' ? 'selected' : '' }}>Mother</option>
                            <option value="Guardian" {{ old('guardian_relationship') === 'Guardian' ? 'selected' : '' }}>Guardian</option>
                            <option value="Uncle" {{ old('guardian_relationship') === 'Uncle' ? 'selected' : '' }}>Uncle</option>
                            <option value="Aunt" {{ old('guardian_relationship') === 'Aunt' ? 'selected' : '' }}>Aunt</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="flex flex-col gap-3 pt-4 border-t border-outline-variant">
                <button type="submit" class="btn-primary w-full py-3 text-base flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">send</span>
                    Submit Application
                </button>
                <a href="{{ route('login') }}" class="text-center font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-2">
                    Return to Login
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
