@extends('layouts.app')

@section('title', 'Complete Your Profile - Hanara Schools')

@section('content')
<div class="max-w-3xl mx-auto py-8" x-data="onboardingForm()">
    <!-- Welcome Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-gradient-to-br from-primary to-primary/70 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <span class="material-symbols-outlined text-on-primary" style="font-size: 40px;">school</span>
        </div>
        <h1 class="font-headline-lg text-headline-lg text-on-background font-bold">Welcome to Hanara Schools</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mt-2 max-w-lg mx-auto">
            Let's set up your teaching profile. Select your form class and the subjects you teach.
        </p>
    </div>

    <!-- Onboarding Form -->
    <form action="{{ route('onboarding.teacher.submit') }}" method="POST" class="space-y-6">
        @csrf

        @if($errors->any())
            <div class="p-4 bg-error-container text-on-error-container rounded-xl text-body-md">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Step 1: Form Class (Only for ClassTeacher) -->
        @if($isFormTeacher)
        <div class="card p-6 border border-primary/20 bg-surface shadow-level-1">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-primary-container text-[22px]">class</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Form Class Assignment</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Select the class you are the form master of</p>
                </div>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline">
                    <span class="material-symbols-outlined" style="font-size: 20px;">door_open</span>
                </span>
                <select name="form_class_id" id="form_class_id" required
                        class="block w-full pl-10 pr-3 py-3 border border-outline-variant rounded-xl bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                    <option value="">-- Select Your Form Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <!-- Step 2: Teaching Assignment -->
        <div class="card p-6 border border-secondary/20 bg-surface shadow-level-1">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-secondary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-secondary-container text-[22px]">menu_book</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Teaching Assignments</h3>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Select the classes and subjects you teach (all combinations will be assigned)</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Select Classes -->
                <div>
                    <h4 class="font-title-medium text-on-surface font-semibold mb-3">1. Select Class(es) you teach</h4>
                    <div class="space-y-1 max-h-64 overflow-y-auto p-3 border border-outline-variant rounded-xl bg-surface-container-lowest">
                        @foreach($classes as $class)
                            <label class="flex items-center gap-3 p-2 hover:bg-surface-container-low rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                <span class="font-body-md text-on-surface">{{ $class->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Select Subjects -->
                <div>
                    <h4 class="font-title-medium text-on-surface font-semibold mb-3">2. Select Subject(s) you teach</h4>
                    <div class="space-y-1 max-h-64 overflow-y-auto p-3 border border-outline-variant rounded-xl bg-surface-container-lowest">
                        @foreach($subjects as $subject)
                            <label class="flex items-center gap-3 p-2 hover:bg-surface-container-low rounded-lg cursor-pointer transition-colors">
                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary">
                                <span class="font-body-md text-on-surface">{{ $subject->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <button type="submit"
                    class="btn-primary px-8 py-3 rounded-xl flex items-center gap-2 font-medium text-base shadow-level-1 hover:shadow-level-2 transition-shadow">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                Complete Setup
            </button>
        </div>
    </form>
</div>
@endsection
