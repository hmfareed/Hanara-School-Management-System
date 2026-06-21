@extends('layouts.app')

@section('title', 'Review Application - Admissions')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-on-surface-variant font-label-md text-label-md mb-2">
        <a href="{{ route('admissions.index') }}" class="hover:text-primary transition-colors">Admissions Queue</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-on-surface">Review Application</span>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">
                {{ $admission->full_name }}
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Review applicant details and choose admission action.</p>
        </div>
        <div>
            @if ($admission->status === 'accepted')
                <span class="badge badge-success uppercase">ACCEPTED / ENROLLED</span>
            @elseif ($admission->status === 'declined')
                <span class="badge badge-error uppercase">DECLINED</span>
            @else
                <span class="badge badge-warning uppercase">PENDING REVIEW</span>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-error-container text-on-error-container rounded-xl border border-error/20">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Applicant Details Panel -->
        <div class="md:col-span-2 space-y-6">
            <div class="card p-6 md:p-8 space-y-6">
                <!-- Student Info Section -->
                <div>
                    <h3 class="font-title-md text-title-md text-primary font-semibold border-b border-outline-variant pb-2 mb-4">Applicant Bio-data</h3>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Full Name</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $admission->full_name }}</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Gender</span>
                            <span class="font-body-md text-body-md text-on-surface capitalize">{{ $admission->gender }}</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Date of Birth</span>
                            <span class="font-body-md text-body-md text-on-surface">{{ $admission->date_of_birth->format('M d, Y') }} ({{ $admission->date_of_birth->age }} years old)</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Target Level</span>
                            <span class="font-body-md text-body-md text-on-surface capitalize font-medium">{{ $admission->level }}</span>
                        </div>
                    </div>
                </div>

                <!-- Guardian Info Section -->
                <div>
                    <h3 class="font-title-md text-title-md text-primary font-semibold border-b border-outline-variant pb-2 mb-4">Guardian Contact Details</h3>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Guardian Name</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $admission->guardian_name }}</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Relationship</span>
                            <span class="font-body-md text-body-md text-on-surface">{{ $admission->guardian_relationship }}</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Phone Number</span>
                            <span class="font-body-md text-body-md text-on-surface font-mono font-medium">{{ $admission->guardian_phone }}</span>
                        </div>
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Email Address</span>
                            <span class="font-body-md text-body-md text-on-surface">{{ $admission->guardian_email ?? 'Not provided' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Workspace Panel -->
        <div class="space-y-6">
            @if ($admission->status === 'pending')
                <!-- Approval Card -->
                <div class="card p-6 border-success/20 bg-success-container/5 space-y-4">
                    <div class="flex items-center gap-2 text-success font-semibold">
                        <span class="material-symbols-outlined">how_to_reg</span>
                        <h4 class="font-title-md text-title-md">Approve & Enroll</h4>
                    </div>
                    <p class="font-body-xs text-body-xs text-on-surface-variant">Approving this application will register the student and prompt class allocation.</p>
                    
                    <form action="{{ route('admissions.approve', $admission->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label text-xs" for="assigned_class_id">Enroll Into Class</label>
                            <select name="assigned_class_id" id="assigned_class_id" class="form-input-custom !py-2 !text-xs" required>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" {{ $admission->assigned_class_id == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->level }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" for="approve_notes">Enrollment Notes</label>
                            <textarea name="notes" id="approve_notes" class="form-input-custom !py-2 !text-xs h-20" placeholder="e.g. Needs transport plan..."></textarea>
                        </div>
                        <button type="submit" class="btn-primary w-full !py-2 text-xs flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">check</span>
                            Approve Application
                        </button>
                    </form>
                </div>

                <!-- Decline Card -->
                <div class="card p-6 border-error/20 bg-error-container/5 space-y-4">
                    <div class="flex items-center gap-2 text-error font-semibold">
                        <span class="material-symbols-outlined">cancel</span>
                        <h4 class="font-title-md text-title-md">Decline Application</h4>
                    </div>
                    
                    <form action="{{ route('admissions.decline', $admission->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label text-xs" for="decline_notes">Decline Reason</label>
                            <textarea name="notes" id="decline_notes" class="form-input-custom !py-2 !text-xs h-20" placeholder="Provide reason..."></textarea>
                        </div>
                        <button type="submit" class="btn-ghost !border-error !text-error hover:bg-error-container/10 w-full !py-2 text-xs flex justify-center items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                            Decline Application
                        </button>
                    </form>
                </div>
            @else
                <!-- Completed Review Details Card -->
                <div class="card p-6 space-y-4">
                    <h4 class="font-title-md text-title-md font-semibold text-on-surface">Review Summary</h4>
                    <div>
                        <span class="font-label-md text-label-md text-on-surface-variant block">Status</span>
                        <span class="capitalize font-semibold {{ $admission->status === 'accepted' ? 'text-success' : 'text-error' }}">
                            {{ $admission->status }}
                        </span>
                    </div>
                    @if($admission->status === 'accepted')
                        <div>
                            <span class="font-label-md text-label-md text-on-surface-variant block">Assigned Class</span>
                            <span class="font-body-md text-body-md font-medium text-on-surface">
                                {{ $admission->assignedClass ? $admission->assignedClass->name : 'N/A' }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <span class="font-label-md text-label-md text-on-surface-variant block">Review Notes</span>
                        <p class="font-body-sm text-body-sm text-on-surface-variant whitespace-pre-wrap">
                            {{ $admission->notes ?? 'No review notes provided.' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
