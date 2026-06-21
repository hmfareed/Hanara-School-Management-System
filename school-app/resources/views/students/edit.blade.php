@extends('layouts.app')

@section('title', 'Edit Student - ' . $student->full_name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('students.show', $student) }}" class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Edit Student Profile</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Update details for <strong>{{ $student->full_name }}</strong> ({{ $student->student_id_number }})</p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="card border-l-4 border-error bg-error-container/10 p-4 space-y-2">
            <div class="flex items-center gap-2 text-error font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">error_outline</span>
                Validation Errors Detected
            </div>
            <ul class="list-disc list-inside space-y-1 font-body-sm text-body-sm text-error">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="card border-l-4 border-success bg-success-container/10 p-4">
            <div class="flex items-center gap-2 text-success font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <form action="{{ route('students.update', $student) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left & Middle: Student Details Card -->
            <div class="lg:col-span-2 card p-6 space-y-6">
                <h3 class="font-title-lg text-title-lg font-semibold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[22px]">person</span>
                    Student Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="first_name">First Name <span class="text-error">*</span></label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}" required
                               class="form-input-custom !py-2.5 @error('first_name') border-error @enderror" placeholder="e.g. Kwame">
                        @error('first_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="last_name">Last Name <span class="text-error">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $student->last_name) }}" required
                               class="form-input-custom !py-2.5 @error('last_name') border-error @enderror" placeholder="e.g. Mensah">
                        @error('last_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Other Names -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="other_names">Other Names</label>
                        <input type="text" name="other_names" id="other_names" value="{{ old('other_names', $student->other_names) }}"
                               class="form-input-custom !py-2.5" placeholder="e.g. Osei">
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="date_of_birth">Date of Birth <span class="text-error">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" required
                               class="form-input-custom !py-2.5 @error('date_of_birth') border-error @enderror">
                        @error('date_of_birth')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="gender">Gender <span class="text-error">*</span></label>
                        <select name="gender" id="gender" required class="form-input-custom !py-2.5 @error('gender') border-error @enderror">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $student->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Admission Date -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="admission_date">Admission Date <span class="text-error">*</span></label>
                        <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', $student->admission_date->format('Y-m-d')) }}" required
                               class="form-input-custom !py-2.5 @error('admission_date') border-error @enderror">
                        @error('admission_date')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="nationality">Nationality <span class="text-error">*</span></label>
                        <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $student->nationality) }}" required
                               class="form-input-custom !py-2.5 @error('nationality') border-error @enderror">
                        @error('nationality')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Religion -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="religion">Religion</label>
                        @php $currentReligion = old('religion', $student->religion); @endphp
                        <select name="religion" id="religion" class="form-input-custom !py-2.5">
                            <option value="">Select Religion</option>
                            <option value="Christianity" {{ $currentReligion === 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Islam" {{ $currentReligion === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Traditional" {{ $currentReligion === 'Traditional' ? 'selected' : '' }}>Traditional</option>
                            <option value="Hindu" {{ $currentReligion === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddhist" {{ $currentReligion === 'Buddhist' ? 'selected' : '' }}>Buddhist</option>
                            <option value="No Religion" {{ $currentReligion === 'No Religion' ? 'selected' : '' }}>No Religion</option>
                            <option value="Other" {{ $currentReligion === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Blood Group -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="blood_group">Blood Group</label>
                        @php $currentBlood = old('blood_group', $student->blood_group); @endphp
                        <select name="blood_group" id="blood_group" class="form-input-custom !py-2.5">
                            <option value="">Unknown</option>
                            <option value="A+" {{ $currentBlood === 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ $currentBlood === 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ $currentBlood === 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ $currentBlood === 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ $currentBlood === 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ $currentBlood === 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ $currentBlood === 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ $currentBlood === 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="status">Status <span class="text-error">*</span></label>
                        <select name="status" id="status" required class="form-input-custom !py-2.5 @error('status') border-error @enderror">
                            <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="transferred" {{ old('status', $student->status) === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="withdrawn" {{ old('status', $student->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        </select>
                        @error('status')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Residential Address -->
                <div>
                    <label class="form-label text-xs font-semibold" for="address">Residential Address</label>
                    <textarea name="address" id="address" rows="2" class="form-input-custom !py-2 @error('address') border-error @enderror"
                              placeholder="House number, Street, Suburb/Town...">{{ old('address', $student->address) }}</textarea>
                    @error('address')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Medical Notes -->
                <div>
                    <label class="form-label text-xs font-semibold" for="medical_notes">Medical Notes / Allergies</label>
                    <textarea name="medical_notes" id="medical_notes" rows="2" class="form-input-custom !py-2"
                              placeholder="Any medical issues, allergies, or emergency directives...">{{ old('medical_notes', $student->medical_notes) }}</textarea>
                </div>
            </div>

            <!-- Right Column: Summary & Actions -->
            <div class="space-y-6">
                <!-- Student Summary Card -->
                <div class="card p-6 space-y-4 text-center">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" class="w-24 h-24 rounded-full object-cover border-4 border-primary/20 shadow-level-1 mx-auto">
                    @else
                        <div class="w-24 h-24 rounded-full bg-primary-container text-on-primary-container text-3xl font-bold flex items-center justify-center border-4 border-primary/10 shadow-level-1 mx-auto">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">{{ $student->full_name }}</h4>
                        <p class="font-mono text-xs text-on-surface-variant mt-1">{{ $student->student_id_number }}</p>
                    </div>

                    @if($currentEnrollment)
                        <div class="p-3 bg-surface-container-low rounded-xl text-left">
                            <p class="text-xs text-on-surface-variant font-label-md">Current Class</p>
                            <p class="font-title-medium text-title-medium font-semibold text-primary mt-1">
                                {{ $currentEnrollment->classAcademicYear->schoolClass->name }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="card p-6 space-y-4">
                    <h3 class="font-title-medium text-title-medium font-semibold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]">save</span>
                        Actions
                    </h3>

                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary w-full justify-center !py-2.5 text-xs flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            Save Changes
                        </button>
                        <a href="{{ route('students.show', $student) }}" class="btn-ghost w-full justify-center !py-2.5 text-xs flex items-center gap-1.5 border border-outline-variant">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
