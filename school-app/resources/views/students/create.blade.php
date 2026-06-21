@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ guardianMode: 'new' }">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('students.index') }}" class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Add Student Profile</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Register a new student and assign them to an active class for the current academic year.</p>
        </div>
    </div>

    <!-- Error/Success Alerts -->
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

    <form action="{{ route('students.store') }}" method="POST" class="space-y-6">
        @csrf

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
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                               class="form-input-custom !py-2.5 @error('first_name') border-error @enderror" placeholder="e.g. Kwame">
                        @error('first_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="last_name">Last Name <span class="text-error">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                               class="form-input-custom !py-2.5 @error('last_name') border-error @enderror" placeholder="e.g. Mensah">
                        @error('last_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Other Names -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="other_names">Other Names</label>
                        <input type="text" name="other_names" id="other_names" value="{{ old('other_names') }}"
                               class="form-input-custom !py-2.5" placeholder="e.g. Osei">
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="date_of_birth">Date of Birth <span class="text-error">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}" required
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
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Class Placement -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="school_class_id">Enroll in Class <span class="text-error">*</span></label>
                        <select name="school_class_id" id="school_class_id" required class="form-input-custom !py-2.5 @error('school_class_id') border-error @enderror">
                            <option value="">Select Class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ ucfirst($class->level) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-on-surface-variant mt-1">Student will be enrolled for active academic year: {{ $currentYear->name ?? 'None Configured' }}</p>
                        @error('school_class_id')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Admission Date -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="admission_date">Admission Date <span class="text-error">*</span></label>
                        <input type="date" name="admission_date" id="admission_date" value="{{ old('admission_date', now()->toDateString()) }}" required
                               class="form-input-custom !py-2.5 @error('admission_date') border-error @enderror">
                        @error('admission_date')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nationality -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="nationality">Nationality <span class="text-error">*</span></label>
                        <input type="text" name="nationality" id="nationality" value="{{ old('nationality', 'Ghanaian') }}" required
                               class="form-input-custom !py-2.5 @error('nationality') border-error @enderror">
                        @error('nationality')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Religion -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="religion">Religion</label>
                        <select name="religion" id="religion" class="form-input-custom !py-2.5">
                            <option value="">Select Religion</option>
                            <option value="Christianity" {{ old('religion') === 'Christianity' ? 'selected' : '' }}>Christianity</option>
                            <option value="Islam" {{ old('religion') === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Traditional" {{ old('religion') === 'Traditional' ? 'selected' : '' }}>Traditional</option>
                            <option value="Hindu" {{ old('religion') === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddhist" {{ old('religion') === 'Buddhist' ? 'selected' : '' }}>Buddhist</option>
                            <option value="No Religion" {{ old('religion') === 'No Religion' ? 'selected' : '' }}>No Religion</option>
                            <option value="Other" {{ old('religion') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Blood Group -->
                    <div>
                        <label class="form-label text-xs font-semibold" for="blood_group">Blood Group</label>
                        <select name="blood_group" id="blood_group" class="form-input-custom !py-2.5">
                            <option value="">Unknown</option>
                            <option value="A+" {{ old('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>
                </div>

                <!-- Residential Address -->
                <div>
                    <label class="form-label text-xs font-semibold" for="address">Residential Address</label>
                    <textarea name="address" id="address" rows="2" class="form-input-custom !py-2 @error('address') border-error @enderror"
                              placeholder="House number, Street, Suburb/Town...">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Medical Notes -->
                <div>
                    <label class="form-label text-xs font-semibold" for="medical_notes">Medical Notes / Allergies</label>
                    <textarea name="medical_notes" id="medical_notes" rows="2" class="form-input-custom !py-2"
                              placeholder="Any medical issues, allergies, or emergency directives...">{{ old('medical_notes') }}</textarea>
                </div>
            </div>

            <!-- Right Column: Guardian Details Card -->
            <div class="card p-6 space-y-6 flex flex-col justify-between">
                <div class="space-y-6">
                    <h3 class="font-title-lg text-title-lg font-semibold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]">family_history</span>
                        Guardian Profile
                    </h3>

                    <!-- Toggle Mode Header -->
                    <div class="flex bg-surface-container rounded-lg p-1">
                        <button type="button" @click="guardianMode = 'new'" 
                                :class="guardianMode === 'new' ? 'bg-surface text-primary font-semibold shadow-sm' : 'text-on-surface-variant'"
                                class="flex-1 py-1.5 text-xs text-center rounded-md font-medium transition-all">
                            New Guardian
                        </button>
                        <button type="button" @click="guardianMode = 'existing'" 
                                :class="guardianMode === 'existing' ? 'bg-surface text-primary font-semibold shadow-sm' : 'text-on-surface-variant'"
                                class="flex-1 py-1.5 text-xs text-center rounded-md font-medium transition-all">
                            Existing Guardian
                        </button>
                    </div>

                    <!-- Hidden Guardian Mode input -->
                    <input type="hidden" name="guardian_mode" :value="guardianMode">

                    <!-- NEW GUARDIAN FORM -->
                    <div x-show="guardianMode === 'new'" class="space-y-4" x-transition>
                        <!-- Guardian First Name -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_first_name">Guardian First Name <span class="text-error">*</span></label>
                            <input type="text" name="guardian_first_name" id="guardian_first_name" value="{{ old('guardian_first_name') }}"
                                   ::required="guardianMode === 'new'"
                                   class="form-input-custom !py-2.5 @error('guardian_first_name') border-error @enderror" placeholder="e.g. Ama">
                            @error('guardian_first_name')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guardian Last Name -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_last_name">Guardian Last Name <span class="text-error">*</span></label>
                            <input type="text" name="guardian_last_name" id="guardian_last_name" value="{{ old('guardian_last_name') }}"
                                   ::required="guardianMode === 'new'"
                                   class="form-input-custom !py-2.5 @error('guardian_last_name') border-error @enderror" placeholder="e.g. Mensah">
                            @error('guardian_last_name')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guardian Phone -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_phone">Guardian Phone (SMS) <span class="text-error">*</span></label>
                            <input type="text" name="guardian_phone" id="guardian_phone" value="{{ old('guardian_phone') }}"
                                   ::required="guardianMode === 'new'"
                                   class="form-input-custom !py-2.5 @error('guardian_phone') border-error @enderror" placeholder="e.g. 0244123456">
                            @error('guardian_phone')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Guardian Email -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_email">Guardian Email</label>
                            <input type="email" name="guardian_email" id="guardian_email" value="{{ old('guardian_email') }}"
                                   class="form-input-custom !py-2.5" placeholder="e.g. parent@example.com">
                        </div>

                        <!-- Guardian Relationship -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_relationship">Relationship <span class="text-error">*</span></label>
                            <select name="guardian_relationship" id="guardian_relationship" ::required="guardianMode === 'new'"
                                    class="form-input-custom !py-2.5 @error('guardian_relationship') border-error @enderror">
                                <option value="">Select Relationship</option>
                                <option value="Father" {{ old('guardian_relationship') === 'Father' ? 'selected' : '' }}>Father</option>
                                <option value="Mother" {{ old('guardian_relationship') === 'Mother' ? 'selected' : '' }}>Mother</option>
                                <option value="Uncle" {{ old('guardian_relationship') === 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                <option value="Aunt" {{ old('guardian_relationship') === 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                <option value="Grandparent" {{ old('guardian_relationship') === 'Grandparent' ? 'selected' : '' }}>Grandparent</option>
                                <option value="Guardian" {{ old('guardian_relationship') === 'Guardian' ? 'selected' : '' }}>Other Guardian</option>
                            </select>
                            @error('guardian_relationship')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Occupation -->
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_occupation">Occupation</label>
                            <input type="text" name="guardian_occupation" id="guardian_occupation" value="{{ old('guardian_occupation') }}"
                                   class="form-input-custom !py-2.5" placeholder="e.g. Teacher, Trader">
                        </div>
                    </div>

                    <!-- EXISTING GUARDIAN FORM -->
                    <div x-show="guardianMode === 'existing'" class="space-y-4" x-transition>
                        <div>
                            <label class="form-label text-xs font-semibold" for="guardian_id">Select Existing Guardian <span class="text-error">*</span></label>
                            <select name="guardian_id" id="guardian_id" ::required="guardianMode === 'existing'"
                                    class="form-input-custom !py-2.5 @error('guardian_id') border-error @enderror">
                                <option value="">Choose Guardian</option>
                                @foreach ($guardians as $g)
                                    <option value="{{ $g->id }}" {{ old('guardian_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->last_name }}, {{ $g->first_name }} ({{ $g->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-on-surface-variant mt-1">Useful to link siblings to the exact same parent profile.</p>
                            @error('guardian_id')
                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-end gap-3 pt-6 border-t border-outline-variant">
                    <a href="{{ route('students.index') }}" class="btn-ghost !py-2 px-4 text-xs">Cancel</a>
                    <button type="submit" class="btn-primary !py-2 px-6 text-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Save Profile
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
