@extends('layouts.app')

@section('title', 'Proprietor Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">Proprietor Dashboard</h2>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Overview of institutional performance and activity.</p>
    </div>
    <div class="flex gap-3">
        <button class="btn-ghost" id="btn-export-report">Export Report</button>
    </div>
</div>

<!-- Top Row: KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-section-gap" id="kpi-cards">
    <!-- KPI 1: Total Students -->
    <div class="kpi-card" id="kpi-total-students">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Students</p>
            <span class="material-symbols-outlined text-outline text-[20px]">groups</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $totalStudents }}</h3>
            <div class="flex items-center text-[#166534] bg-[#dcfce7] px-2 py-0.5 rounded text-xs font-medium">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span>+{{ rand(5, 15) }}</span>
            </div>
        </div>
    </div>

    <!-- KPI 2: Fee Collection -->
    <div class="kpi-card" id="kpi-fee-collection">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Fee Collection This Term</p>
            <span class="material-symbols-outlined text-outline text-[20px]">account_balance_wallet</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $feeCollection }}%</h3>
            <div class="w-full bg-surface-container-high h-2 rounded-full mt-3 overflow-hidden">
                <div class="bg-primary h-full rounded-full transition-all duration-1000" style="width: {{ $feeCollection }}%;"></div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Attendance Rate -->
    <div class="kpi-card" id="kpi-attendance">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Avg. Attendance Rate</p>
            <span class="material-symbols-outlined text-outline text-[20px]">event_available</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $avgAttendance }}%</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Across all departments</p>
        </div>
    </div>

    <!-- KPI 4: Active Staff -->
    <div class="kpi-card" id="kpi-active-staff">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Active Staff</p>
            <span class="material-symbols-outlined text-outline text-[20px]">badge</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $activeStaff }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Teachers & Admin</p>
        </div>
    </div>
</div>

<!-- Middle Section: Two-Column -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-section-gap">
    <!-- Left Column: Enrollment by Class Chart (60%) -->
    <div class="lg:col-span-7 card flex flex-col overflow-hidden" id="chart-enrollment">
        <div class="card-header flex justify-between items-center">
            <h3 class="font-title-lg text-title-lg text-on-background">Enrollment by Class</h3>
            <button class="text-primary font-label-md text-label-md hover:underline">View Full Report</button>
        </div>
        <div class="p-6 flex-1 flex items-end gap-2 md:gap-4 h-64 relative">
            <!-- Y-axis labels -->
            <div class="absolute left-4 top-4 bottom-8 flex flex-col justify-between text-xs text-outline font-medium w-6">
                <span>100</span><span>75</span><span>50</span><span>25</span><span>0</span>
            </div>
            <!-- Grid lines -->
            <div class="absolute left-12 right-4 top-6 bottom-8 flex flex-col justify-between z-0">
                @for($i = 0; $i < 5; $i++)
                    <div class="w-full border-t border-outline-variant border-dashed"></div>
                @endfor
            </div>
            <!-- Bars -->
            @php
                $levels = [
                    ['label' => 'Nursery', 'key' => 'nursery', 'delay' => '0.1s'],
                    ['label' => 'KG', 'key' => 'kindergarten', 'delay' => '0.2s'],
                    ['label' => 'Pri 1-6', 'key' => 'primary', 'delay' => '0.3s'],
                    ['label' => 'JHS 1-3', 'key' => 'jhs', 'delay' => '0.4s'],
                ];
                $maxEnroll = max(array_values($enrollmentByLevel) ?: [1]);
            @endphp
            <div class="flex-1 flex justify-around items-end h-full pt-6 pb-8 pl-8 z-10">
                @foreach($levels as $level)
                    @php $count = $enrollmentByLevel[$level['key']] ?? 0; $pct = $maxEnroll > 0 ? ($count / $maxEnroll) * 100 : 0; @endphp
                    <div class="flex flex-col items-center gap-2 group w-full">
                        <div class="w-full max-w-[40px] bg-primary-container rounded-t-sm bar-grow relative hover:bg-primary transition-colors cursor-pointer"
                             style="height: {{ max($pct, 5) }}%; animation-delay: {{ $level['delay'] }};">
                            <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-inverse-surface text-inverse-on-surface text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                {{ $count }} Students
                            </div>
                        </div>
                        <span class="text-xs font-medium text-on-surface-variant -rotate-45 md:rotate-0 mt-2 whitespace-nowrap">{{ $level['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Attention Card (40%) -->
    <div class="lg:col-span-5 card overflow-hidden flex flex-col" id="card-attention">
        <div class="p-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/50">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary-container">warning</span>
                <h3 class="font-title-lg text-title-lg text-on-background">Students Needing Attention</h3>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            <ul class="divide-y divide-outline-variant">
                @foreach($attentionList as $item)
                    <li class="p-4 hover:bg-surface-container-low transition-colors flex justify-between items-center cursor-pointer"
                        @if($item['student_id'] !== '#') onclick="window.location.href='{{ route('students.show', $item['student_id']) }}'" @endif>
                        <div>
                            <p class="font-body-md text-body-md font-medium text-on-background">{{ $item['name'] }}</p>
                            <p class="font-label-md text-label-md text-on-surface-variant">{{ $item['className'] }}</p>
                        </div>
                        <span class="{{ $item['badge'] }}">{{ $item['reason'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="p-3 border-t border-outline-variant text-center bg-surface-container-low/30">
            <button class="text-primary font-label-md text-label-md font-medium hover:underline">View All Alerts</button>
        </div>
    </div>
</div>

<!-- Bottom Section: Recent Activity -->
<div class="card overflow-hidden" id="card-recent-activity">
    <div class="card-header">
        <h3 class="font-title-lg text-title-lg text-on-background">Recent Activity</h3>
    </div>
    <div class="p-0">
        <ul class="divide-y divide-outline-variant">
            @foreach($recentActivities as $activity)
                <li class="p-4 hover:bg-surface-container-low transition-colors flex gap-4">
                    <div class="w-10 h-10 rounded-full {{ $activity['bg'] }} {{ $activity['color'] }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">{{ $activity['icon'] }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-body-md text-body-md text-on-background">
                            {{ $activity['text'] }}
                        </p>
                        <p class="font-label-md text-label-md text-on-surface-variant mt-1">{{ $activity['time'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<!-- Footer Spacing -->
<div class="h-8"></div>
@endsection
