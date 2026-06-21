@extends('layouts.app')
@section('title', 'Parent Dashboard')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Welcome, {{ auth()->user()->name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Your children's overview at a glance.</p>
    </div>
</div>

{{-- Children Cards --}}
@if($children->isNotEmpty())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-section-gap">
    @foreach($children as $child)
    <div class="card p-0 overflow-hidden" id="child-card-{{ $child['student']->id }}">
        {{-- Header with gradient --}}
        <div class="bg-gradient-to-r from-primary to-primary/80 p-5 text-on-primary">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold backdrop-blur-sm">
                    {{ strtoupper(substr($child['student']->first_name, 0, 1) . substr($child['student']->last_name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-semibold">{{ $child['student']->full_name }}</h3>
                    <p class="font-body-sm text-body-sm opacity-90">{{ $child['className'] }}</p>
                    <p class="font-label-md text-label-md opacity-75">ID: {{ $child['student']->student_id_number }}</p>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-px bg-outline-variant">
            <div class="bg-surface p-4 text-center">
                <span class="material-symbols-outlined text-primary mb-1" style="font-size: 28px;">calendar_today</span>
                <p class="font-headline-md text-headline-md font-bold text-on-surface">{{ $child['attendanceRate'] }}%</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Attendance</p>
            </div>
            <div class="bg-surface p-4 text-center">
                <span class="material-symbols-outlined mb-1 {{ $child['feeBalance'] > 0 ? 'text-error' : 'text-[#166534]' }}" style="font-size: 28px;">account_balance_wallet</span>
                <p class="font-headline-md text-headline-md font-bold {{ $child['feeBalance'] > 0 ? 'text-error' : 'text-[#166534]' }}">GH₵{{ number_format($child['feeBalance'], 2) }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant">{{ $child['feeBalance'] > 0 ? 'Balance Due' : 'Paid Up' }}</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="p-4 flex flex-wrap gap-2">
            <a href="{{ route('parent.child.attendance', $child['student']) }}" class="flex-1 py-2 px-3 bg-primary-container text-on-primary-container font-label-md text-label-md rounded-lg text-center hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">event_available</span>
                Attendance
            </a>
            <a href="{{ route('parent.child.grades', $child['student']) }}" class="flex-1 py-2 px-3 bg-secondary-container text-on-secondary-container font-label-md text-label-md rounded-lg text-center hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">grade</span>
                Grades
            </a>
            <a href="{{ route('parent.child.fees', $child['student']) }}" class="flex-1 py-2 px-3 bg-tertiary-container text-on-tertiary-container font-label-md text-label-md rounded-lg text-center hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">payments</span>
                Fees
            </a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card p-8 text-center mb-section-gap">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">family_restroom</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No Children Linked</h3>
    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
        Your account is not yet linked to any students. Please contact the school administration to set up your account.
    </p>
</div>
@endif

{{-- Announcements Section --}}
@if($announcements->isNotEmpty())
<div class="mb-section-gap">
    <h3 class="font-title-lg text-title-lg text-on-surface mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">campaign</span>
        School Announcements
    </h3>
    <div class="space-y-3">
        @foreach($announcements as $announcement)
        <div class="card p-4 {{ $announcement->type === 'emergency' ? 'border-l-4 border-error bg-error-container/20' : '' }} {{ $announcement->is_pinned ? 'border-l-4 border-primary' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        @if($announcement->type === 'emergency')
                            <span class="badge badge-error font-label-md">⚠ Emergency</span>
                        @elseif($announcement->is_pinned)
                            <span class="material-symbols-outlined text-primary text-[16px]">push_pin</span>
                        @endif
                        <span class="badge {{ match($announcement->type) { 'academic' => 'badge-info', 'financial' => 'badge-warning', 'emergency' => 'badge-error', default => 'badge-primary' } }} font-label-md">
                            {{ ucfirst($announcement->type) }}
                        </span>
                    </div>
                    <h4 class="font-title-md text-title-md font-semibold text-on-surface">{{ $announcement->title }}</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ Str::limit($announcement->body, 200) }}</p>
                    <p class="font-label-md text-label-md text-outline mt-2">{{ $announcement->published_at?->diffForHumans() }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
