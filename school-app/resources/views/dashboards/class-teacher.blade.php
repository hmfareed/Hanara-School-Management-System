@extends('layouts.app')

@section('title', 'Class Teacher Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
            Class Teacher Dashboard
        </h2>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
        @if($classAcademicYear)
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Assigned Class: <strong class="text-primary">{{ $classAcademicYear->schoolClass->name }}</strong> ({{ ucfirst($classAcademicYear->schoolClass->level) }} Level) • Academic Year: {{ $currentYear->name ?? 'None' }}
            </p>
        @else
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                No class assigned for the current academic year.
            </p>
        @endif
    </div>
</div>

@if(!$classAcademicYear)
    <!-- No Class Assigned Warning -->
    <div class="card p-12 text-center max-w-xl mx-auto space-y-4">
        <span class="material-symbols-outlined text-outline" style="font-size: 64px;">event_busy</span>
        <h3 class="font-title-lg text-title-lg text-on-surface font-semibold">No Class Assignment Found</h3>
        <p class="font-body-md text-body-md text-on-surface-variant">
            You are currently not assigned as a Class Teacher to any class for the active academic year **{{ $currentYear->name ?? 'N/A' }}**. Please contact the Head Teacher or system administrator to update your class assignment.
        </p>
    </div>
@else
    <!-- Top Row: KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-section-gap" id="kpi-cards">
        <!-- KPI 1: Enrolled Students -->
        <div class="kpi-card" id="kpi-enrolled-students">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Enrolled Students</p>
                <span class="material-symbols-outlined text-outline text-[20px]">groups</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $studentsCount }}</h3>
                <span class="text-xs text-on-surface-variant">Active roster</span>
            </div>
        </div>

        <!-- KPI 2: Today's Attendance -->
        <div class="kpi-card" id="kpi-attendance-today">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Today's Attendance</p>
                <span class="material-symbols-outlined text-outline text-[20px]">done_all</span>
            </div>
            <div class="mt-2">
                @if($attendanceToday)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-success-container text-on-success-container text-sm font-semibold mt-1">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                        Completed
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-warning-container text-on-warning-container text-sm font-semibold mt-1">
                        <span class="material-symbols-outlined text-[16px]">pending</span>
                        Not Marked
                    </span>
                @endif
            </div>
        </div>

        <!-- KPI 3: Attendance Rate -->
        <div class="kpi-card" id="kpi-attendance-rate">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Class Attendance Rate</p>
                <span class="material-symbols-outlined text-outline text-[20px]">trending_up</span>
            </div>
            <div class="mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $attendanceRate }}%</h3>
                <div class="w-full bg-surface-container-high h-2 rounded-full mt-3 overflow-hidden">
                    <div class="bg-primary h-full rounded-full transition-all duration-1000" style="width: {{ $attendanceRate }}%;"></div>
                </div>
            </div>
        </div>

        <!-- KPI 4: Staff Announcements -->
        <div class="kpi-card" id="kpi-announcements">
            <div class="flex justify-between items-start mb-2">
                <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Staff Bulletins</p>
                <span class="material-symbols-outlined text-outline text-[20px]">campaign</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <h3 class="font-display-lg text-display-lg text-on-background">{{ $announcements->count() }}</h3>
                <span class="text-xs text-on-surface-variant">Active notices</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card p-6 mb-section-gap flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-container-low/50">
        <div>
            <h3 class="font-title-medium text-title-medium font-semibold text-on-surface">Daily Classroom Operations</h3>
            <p class="font-body-sm text-body-sm text-on-surface-variant">Mark attendance, update grades, or check student files quickly.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('attendance.mark', ['class_id' => $classAcademicYear->schoolClass->id]) }}" class="btn-primary flex items-center gap-1.5 text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                Mark Attendance
            </a>
            <a href="{{ route('academics.gradebook') }}" class="btn-ghost flex items-center gap-1.5 text-xs py-2 px-4 border border-outline-variant">
                <span class="material-symbols-outlined text-[18px]">school</span>
                Class Gradebook
            </a>
        </div>
    </div>

    <!-- Main Layout: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Class Roster Table (60% / 7 Cols) -->
        <div class="lg:col-span-8 card overflow-hidden flex flex-col">
            <div class="card-header flex justify-between items-center">
                <h3 class="font-title-lg text-title-lg text-on-background font-semibold">Class Roster ({{ $students->count() }})</h3>
                <a href="{{ route('students.index', ['class_id' => $classAcademicYear->schoolClass->id]) }}" class="text-primary font-label-md text-label-md hover:underline flex items-center gap-1">
                    Manage Roster
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            
            <div class="overflow-x-auto border-t border-outline-variant">
                <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container text-on-surface border-b border-outline-variant font-semibold">
                            <th class="p-3 pl-4">ID</th>
                            <th class="p-3">Student Name</th>
                            <th class="p-3">Gender</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($students as $student)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-3 pl-4 font-mono text-xs">{{ $student->student_id_number }}</td>
                                <td class="p-3 font-semibold text-on-surface">{{ $student->full_name }}</td>
                                <td class="p-3 capitalize text-on-surface-variant">{{ $student->gender }}</td>
                                <td class="p-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-container text-on-success-container">Active</span>
                                </td>
                                <td class="p-3 text-right pr-4">
                                    <a href="{{ route('students.show', $student) }}" class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1 text-primary hover:bg-primary-container/20 inline-flex items-center ml-auto">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        View Profile
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-on-surface-variant font-body-md">
                                    No students currently enrolled in this class.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Recent Attendance & Notices (40% / 4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Recent Attendance Logs -->
            <div class="card overflow-hidden flex flex-col">
                <div class="p-4 border-b border-outline-variant bg-surface-container-low/50">
                    <h3 class="font-title-lg text-title-lg text-on-background font-semibold">Recent Attendance Logs</h3>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-outline-variant max-h-[300px] overflow-y-auto">
                        @forelse($recentAttendance->unique('date')->take(5) as $log)
                            <li class="p-4 hover:bg-surface-container-low transition-colors flex justify-between items-center">
                                <div>
                                    <p class="font-body-md text-body-md font-medium text-on-background">{{ $log->date->format('l, d M Y') }}</p>
                                    <p class="font-label-md text-label-md text-on-surface-variant">Class daily log</p>
                                </div>
                                <span class="badge badge-success">Marked</span>
                            </li>
                        @empty
                            <li class="p-6 text-center text-on-surface-variant text-sm">
                                <span class="material-symbols-outlined text-outline text-[32px] mb-2 block">event_busy</span>
                                No attendance records logged this term.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

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
                                    <span class="text-xs text-on-surface-variant">{{ $bulletin->published_at ? $bulletin->published_at->diffForHumans() : $bulletin->created_at->diffForHumans() }}</span>
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
