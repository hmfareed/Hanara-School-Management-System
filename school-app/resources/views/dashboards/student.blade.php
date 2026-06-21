@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">
            Welcome, {{ $student->first_name }}!
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Track your class progress, timetable, and announcements.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-section-gap">
    <!-- Left: Profile & Metrics -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Student Details Card -->
        <div class="card p-6 border border-outline-variant bg-surface-container-low shadow-level-1">
            <div class="flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-primary-container text-on-primary-container text-2xl font-bold flex items-center justify-center border-4 border-surface shadow-level-1 mb-4">
                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                </div>
                <h3 class="font-title-lg text-title-lg font-bold text-on-surface">{{ $student->full_name }}</h3>
                <span class="font-mono text-sm text-on-surface-variant font-semibold mt-1">{{ $student->student_id_number }}</span>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-secondary-container text-on-secondary-container mt-3">
                    Class: {{ $className }}
                </span>
            </div>

            <div class="space-y-3 mt-6 pt-6 border-t border-outline-variant text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-on-surface-variant">Gender:</span>
                    <span class="font-medium text-on-surface">{{ ucfirst($student->gender) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-on-surface-variant">Date of Birth:</span>
                    <span class="font-medium text-on-surface">{{ $student->date_of_birth ? $student->date_of_birth->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-on-surface-variant">Admission Date:</span>
                    <span class="font-medium text-on-surface">{{ $student->admission_date ? $student->admission_date->format('d M, Y') : 'N/A' }}</span>
                </div>
                @if($student->blood_group)
                    <div class="flex justify-between items-center">
                        <span class="text-on-surface-variant">Blood Group:</span>
                        <span class="font-medium text-on-surface">{{ $student->blood_group }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 gap-4">
            <div class="card p-4 text-center border border-outline-variant bg-surface-container-low">
                <span class="material-symbols-outlined text-primary mb-1" style="font-size: 28px;">calendar_today</span>
                <p class="font-headline-md text-headline-md font-bold text-on-surface">{{ $attendanceRate }}%</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Attendance</p>
            </div>
            
            <div class="card p-4 text-center border border-outline-variant bg-surface-container-low">
                <span class="material-symbols-outlined mb-1 {{ $feeBalance > 0 ? 'text-error' : 'text-[#166534]' }}" style="font-size: 28px;">account_balance_wallet</span>
                <p class="font-headline-md text-headline-md font-bold {{ $feeBalance > 0 ? 'text-error' : 'text-[#166534]' }}">GH₵{{ number_format($feeBalance, 2) }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Outstanding</p>
            </div>
        </div>
    </div>

    <!-- Right: Timetable & Announcements -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Timetable Card -->
        <div class="card p-6 border border-outline-variant bg-surface-container-low shadow-level-1">
            <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary">schedule</span>
                Class Timetable
            </h3>

            @if($timetable->isNotEmpty())
                @php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                    $timetableByDay = $timetable->groupBy('day_of_week');
                @endphp

                <!-- Tabs header -->
                <div class="flex border-b border-outline-variant gap-2 overflow-x-auto pb-px" id="timetable-tabs">
                    @foreach($days as $index => $day)
                        <button onclick="switchTab('{{ $day }}')" id="tab-{{ $day }}" 
                                class="tab-btn px-4 py-2 font-label-lg text-label-lg text-on-surface-variant border-b-2 border-transparent hover:text-primary transition-all whitespace-nowrap {{ $index === 0 ? 'active-tab !text-primary !border-primary font-bold' : '' }}">
                            {{ ucfirst($day) }}
                        </button>
                    @endforeach
                </div>

                <!-- Tabs content -->
                <div class="mt-4 space-y-4">
                    @foreach($days as $index => $day)
                        <div id="content-{{ $day }}" class="tab-content {{ $index === 0 ? '' : 'hidden' }}">
                            @if(isset($timetableByDay[$day]) && $timetableByDay[$day]->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($timetableByDay[$day] as $slot)
                                        <div class="flex items-center justify-between p-3 border border-outline-variant rounded-xl bg-surface-container-lowest hover:shadow-level-1 transition-all">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-[20px]">book</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-title-md text-title-md font-bold text-on-surface">
                                                        {{ $slot->subject->name }}
                                                    </h4>
                                                    <p class="font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1 mt-0.5">
                                                        <span class="material-symbols-outlined text-[14px]">person</span>
                                                        {{ $slot->staff ? $slot->staff->full_name : 'No Teacher' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-title-md text-sm font-semibold text-primary">
                                                    {{ date('h:i A', strtotime($slot->start_time)) }} - {{ date('h:i A', strtotime($slot->end_time)) }}
                                                </p>
                                                @if($slot->room)
                                                    <p class="font-body-sm text-xs text-on-surface-variant mt-0.5">
                                                        Room: {{ $slot->room }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center border border-dashed border-outline-variant rounded-xl text-on-surface-variant bg-surface-container-lowest">
                                    <span class="material-symbols-outlined text-[32px] text-outline mb-1">hotel</span>
                                    <p class="text-sm">No scheduled classes for {{ ucfirst($day) }}.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center border border-dashed border-outline-variant rounded-xl text-on-surface-variant bg-surface-container-lowest">
                    <span class="material-symbols-outlined text-[48px] text-outline mb-2">table_rows_off</span>
                    <p class="text-sm">No timetable slots configured for your class yet.</p>
                </div>
            @endif
        </div>

        <!-- School Announcements Card -->
        <div class="card p-6 border border-outline-variant bg-surface-container-low shadow-level-1">
            <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary">campaign</span>
                School Announcements
            </h3>

            @if($announcements->isNotEmpty())
                <div class="space-y-3">
                    @foreach($announcements as $announcement)
                        <div class="p-4 border border-outline-variant rounded-xl bg-surface-container-lowest hover:shadow-level-1 transition-all {{ $announcement->type === 'emergency' ? 'border-l-4 border-error bg-error-container/10' : '' }} {{ $announcement->is_pinned ? 'border-l-4 border-primary' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="badge {{ match($announcement->type) { 'academic' => 'badge-info', 'financial' => 'badge-warning', 'emergency' => 'badge-error', default => 'badge-primary' } }} font-label-md">
                                    {{ ucfirst($announcement->type) }}
                                </span>
                                @if($announcement->is_pinned)
                                    <span class="material-symbols-outlined text-primary text-[16px] inline-flex items-center" title="Pinned Announcement">push_pin</span>
                                @endif
                            </div>
                            <h4 class="font-title-md text-title-md font-semibold text-on-surface">{{ $announcement->title }}</h4>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                {{ $announcement->body }}
                            </p>
                            <span class="text-xs text-outline block mt-3">
                                Published {{ $announcement->published_at ? $announcement->published_at->diffForHumans() : 'recently' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center border border-dashed border-outline-variant rounded-xl text-on-surface-variant bg-surface-container-lowest">
                    <span class="material-symbols-outlined text-[48px] text-outline mb-2">notifications_off</span>
                    <p class="text-sm">No announcements posted for students at this time.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function switchTab(day) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Deactivate all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active-tab', '!text-primary', '!border-primary', 'font-bold');
        });

        // Show selected tab content
        const targetContent = document.getElementById('content-' + day);
        if (targetContent) {
            targetContent.classList.remove('hidden');
        }

        // Activate selected tab button
        const targetBtn = document.getElementById('tab-' + day);
        if (targetBtn) {
            targetBtn.classList.add('active-tab', '!text-primary', '!border-primary', 'font-bold');
        }
    }
</script>
@endsection
