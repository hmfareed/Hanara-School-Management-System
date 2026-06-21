@extends('layouts.app')

@section('title', 'Mark Daily Attendance')

@section('content')
<div class="space-y-6">
    <!-- Sub-navigation Tabs -->
    <div class="border-b border-outline-variant flex gap-1 overflow-x-auto">
        <a href="{{ route('attendance.mark') }}" 
           class="px-4 py-2.5 font-label-md text-label-md border-b-2 border-primary text-primary font-semibold transition-all">
            Daily marking
        </a>
        <a href="{{ route('attendance.register') }}" 
           class="px-4 py-2.5 font-label-md text-label-md border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-all">
            Monthly register
        </a>
    </div>

    <!-- Livewire marking grid -->
    @livewire('mark-attendance')
</div>
@endsection
