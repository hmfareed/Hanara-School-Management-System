@extends('layouts.app')

@section('title', 'Subject Teacher Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
            Subject Teacher Dashboard
        </h2>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Subject assignments, classroom syllabus coverage, and academic gradebook access.
        </p>
    </div>
    @if(auth()->user()->teacherAssignments->count() > 1)
    <div class="flex items-center gap-3">
        <label for="context-switcher" class="font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Context Switcher:</label>
        <select id="context-switcher" onchange="window.location = this.value"
                class="px-4 py-2 bg-surface-container-low border border-outline-variant rounded-xl text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-on-surface font-semibold">
            <option value="">Select Class & Subject</option>
            @foreach($assignments as $a)
                @php
                    $classAY = $a->schoolClass->classAcademicYears()->where('academic_year_id', $currentYear->id)->first();
                @endphp
                @if($classAY)
                    <option value="{{ route('academics.gradebook', ['class_id' => $classAY->id, 'subject_id' => $a->subject_id]) }}">
                        {{ $a->schoolClass->name }} — {{ $a->subject->name }}
                    </option>
                @endif
            @endforeach
        </select>
    </div>
    @endif
</div>

@if($assignments->isEmpty())
    <!-- No Assignments Warning -->
    <div class="card p-12 text-center max-w-xl mx-auto space-y-4">
        <span class="material-symbols-outlined text-outline" style="font-size: 64px;">import_contacts</span>
        <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">No Subject Assignments</h3>
        <p class="font-body-md text-body-md text-on-surface-variant">
            You are currently not assigned to teach any subjects to classes for the active academic year **{{ $currentYear->name ?? 'N/A' }}**. Please check with the Academic Head or Administrator to map your class-subject schedules.
        </p>
    </div>
@else
    <!-- Top Row: KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-section-gap" id="kpi-cards">
        <!-- KPI 1: Assigned Classes -->
        <div class="kpi-card" id="kpi-assigned-classes">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Assigned Classes</p>
                <span class="material-symbols-outlined text-outline text-[20px]">meeting_room</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $classCount }}</h3>
                <span class="text-xs text-on-surface-variant">Active classes</span>
            </div>
        </div>

        <!-- KPI 2: Assigned Subjects -->
        <div class="kpi-card" id="kpi-assigned-subjects">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Assigned Subjects</p>
                <span class="material-symbols-outlined text-outline text-[20px]">book</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $subjectCount }}</h3>
                <span class="text-xs text-on-surface-variant">Syllabus streams</span>
            </div>
        </div>

        <!-- KPI 3: Total Students -->
        <div class="kpi-card" id="kpi-total-students">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Students</p>
                <span class="material-symbols-outlined text-outline text-[20px]">groups</span>
            </div>
            <div class="mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $totalStudents }}</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Across all teaching pools</p>
            </div>
        </div>

        <!-- KPI 4: Staff Bulletins -->
        <div class="kpi-card" id="kpi-bulletins">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Staff Notices</p>
                <span class="material-symbols-outlined text-outline text-[20px]">campaign</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $announcements->count() }}</h3>
                <span class="text-xs text-on-surface-variant">Active notices</span>
            </div>
        </div>
    </div>

    <!-- Quick Action Section -->
    <div class="card p-6 mb-section-gap flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-container-low/50">
        <div>
            <h3 class="font-title-medium text-title-medium font-semibold text-on-surface">Record Grades & Assessments</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">Access the centralized gradebook to manage continuous assessment scores and exam marks.</p>
        </div>
        <div>
            <a href="{{ route('academics.gradebook') }}" class="btn-primary flex items-center gap-1.5 text-xs py-2 px-4 shadow-level-1">
                <span class="material-symbols-outlined text-[18px]">menu_book</span>
                Open Gradebook Workspace
            </a>
        </div>
    </div>

    <!-- Main Layout: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Assigned Class Cards Grid (60% / 7 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            <h3 class="font-title-lg text-title-lg text-on-background font-semibold mb-2">My Teaching Assignments</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($assignments as $assign)
                    @php
                        $classAY = $assign->schoolClass->classAcademicYears()->where('academic_year_id', $currentYear->id)->first();
                        $studentsInClass = $classAY 
                            ? \App\Models\ClassStudent::where('class_academic_year_id', $classAY->id)->where('status', 'enrolled')->count()
                            : 0;
                    @endphp
                    <div class="card p-5 space-y-4 hover:shadow-level-2 transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-container text-on-primary-container flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">import_contacts</span>
                                </div>
                                <div>
                                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">
                                        {{ $assign->subject->name }}
                                    </h4>
                                    <p class="text-xs text-on-surface-variant">Class: {{ $assign->schoolClass->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center text-xs text-on-surface-variant pt-2 border-t border-outline-variant">
                            <span>Students enrolled: <strong class="text-on-surface font-semibold">{{ $studentsInClass }}</strong></span>
                            @if($classAY)
                                <a href="{{ route('academics.gradebook', ['class_id' => $classAY->id, 'subject_id' => $assign->subject_id]) }}" class="text-primary hover:underline font-semibold flex items-center gap-0.5">
                                    Grades
                                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                </a>
                            @else
                                <span class="text-neutral-400">Grades N/A</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Notices (40% / 4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Bulletins/Announcements -->
            <div class="card overflow-hidden flex flex-col">
                <div class="p-4 border-b border-outline-variant bg-surface-container-low/50">
                    <h3 class="font-title-lg text-title-lg text-on-background font-semibold">Staff Notices & Bulletins</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-outline-variant">
                        @forelse($announcements as $bulletin)
                            <li class="p-4 hover:bg-surface-container-low transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $bulletin->is_pinned ? 'bg-error-container text-on-error-container' : 'bg-surface-container-high text-on-surface-variant' }}">
                                        {{ $bulletin->is_pinned ? 'Pinned' : 'General' }}
                                    </span>
                                    <span class="text-xs text-on-surface-variant">
                                        {{ $bulletin->published_at ? $bulletin->published_at->diffForHumans() : $bulletin->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <h4 class="font-body-md text-body-md font-semibold text-on-surface">{{ $bulletin->title }}</h4>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 line-clamp-2">{{ $bulletin->body }}</p>
                            </li>
                        @empty
                            <li class="p-6 text-center text-on-surface-variant text-sm">
                                <span class="material-symbols-outlined text-outline text-[32px] mb-2 block">campaign</span>
                                No notices posted for staff.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="h-8"></div>
@endsection
