@extends('layouts.app')

@section('title', 'Gradebook')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-headline-md text-on-background">Academics</h1>
            <p class="font-body-md text-on-surface-variant">Manage students' grades, class timetables, and teacher assignments.</p>
        </div>
    </div>

    {{-- Sub Navigation --}}
    <div class="flex border-b border-outline-variant mb-6 gap-2">
        <a href="{{ route('academics.gradebook') }}" class="px-4 py-2 font-label-md transition-all {{ request()->routeIs('academics.gradebook') ? 'border-b-2 border-primary text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">Gradebook</a>
        @if(auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']))
            <a href="{{ route('academics.assignments') }}" class="px-4 py-2 font-label-md transition-all {{ request()->routeIs('academics.assignments') ? 'border-b-2 border-primary text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">Teacher Assignments</a>
        @endif
        <a href="{{ route('academics.timetable') }}" class="px-4 py-2 font-label-md transition-all {{ request()->routeIs('academics.timetable') ? 'border-b-2 border-primary text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">Weekly Timetable</a>
        <a href="{{ route('academics.report-cards') }}" class="px-4 py-2 font-label-md transition-all {{ request()->routeIs('academics.report-cards') ? 'border-b-2 border-primary text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">Report Cards</a>
    </div>

    {{-- Livewire component --}}
    @livewire('academics.gradebook')
</div>
@endsection
