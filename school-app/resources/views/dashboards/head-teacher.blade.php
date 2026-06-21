@extends('layouts.app')

@section('title', 'Head Teacher Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
            {{ $isSupervisor ? 'Supervisor Dashboard' : 'Head Teacher Dashboard' }}
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Academic oversight, scheduling, and student analytics.</p>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
    </div>
    @if($isSupervisor)
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-tertiary-container text-on-tertiary-container rounded-full text-label-md font-medium">
        <span class="material-symbols-outlined text-[16px]">visibility</span>
        Read-Only Access
    </span>
    @endif
</div>

<!-- KPI Metrics Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-section-gap" id="kpi-cards">
    <!-- KPI 1: Enrolled Students -->
    <div class="kpi-card" id="kpi-total-students">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Enrolled Students</p>
            <span class="material-symbols-outlined text-outline text-[20px]">groups</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $totalStudents }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Active in school database</p>
        </div>
    </div>

    <!-- KPI 2: Attendance Rate -->
    <div class="kpi-card" id="kpi-attendance">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Avg. Attendance Rate</p>
            <span class="material-symbols-outlined text-outline text-[20px]">event_available</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $avgAttendance }}%</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Current term average</p>
        </div>
    </div>

    <!-- KPI 3: Active Staff -->
    <div class="kpi-card" id="kpi-active-staff">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Active Staff</p>
            <span class="material-symbols-outlined text-outline text-[20px]">badge</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $activeStaff }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Teachers and support personnel</p>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-section-gap">
    @unless($isSupervisor)
    <!-- Academic Quick Actions (1 col) -->
    <div class="card p-6 h-fit space-y-4">
        <h3 class="font-title-lg text-on-surface border-b border-outline-variant pb-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">bolt</span>
            Quick Commands
        </h3>
        <div class="flex flex-col gap-2">
            <a href="{{ route('academics.gradebook') }}" class="button-primary w-full justify-center">
                <span class="material-symbols-outlined text-[18px]">edit_note</span>
                Gradebook Workspace
            </a>
            <a href="{{ route('academics.assignments') }}" class="button-secondary w-full justify-center">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Teacher Assignments
            </a>
            <a href="{{ route('academics.timetable') }}" class="button-secondary w-full justify-center">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                Timetable Builder
            </a>
            <a href="{{ route('academics.report-cards') }}" class="button-secondary w-full justify-center">
                <span class="material-symbols-outlined text-[18px]">download</span>
                Report Card Center
            </a>
        </div>
    </div>
    @endunless

    <!-- Class Registry Overview -->
    <div class="{{ $isSupervisor ? 'lg:col-span-3' : 'lg:col-span-2' }} card overflow-hidden flex flex-col">
        <div class="p-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
            <h3 class="font-title-lg text-on-surface">Class Registry & Rosters</h3>
            <span class="text-xs text-outline font-medium">Term: {{ $currentYear?->terms()->where('is_current', true)->first()?->name ?: 'None' }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body-sm text-body-sm">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant font-label-md text-label-md">
                        <th class="p-4">Class</th>
                        <th class="p-4">Level</th>
                        <th class="p-4">Enrolled Students</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($classes as $class)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4 font-semibold text-on-surface">{{ $class->name }}</td>
                            <td class="p-4">
                                <span class="text-xs px-2 py-0.5 rounded bg-primary-container text-on-primary-container font-medium uppercase">
                                    {{ $class->level }}
                                </span>
                            </td>
                            <td class="p-4 text-on-surface font-medium">{{ $class->students_count }} Students</td>
                            <td class="p-4 text-right">
                                <a href="{{ route('students.index', ['class_id' => $class->id]) }}" class="text-primary font-medium hover:underline inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    Roster
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="h-8"></div>
@endsection
