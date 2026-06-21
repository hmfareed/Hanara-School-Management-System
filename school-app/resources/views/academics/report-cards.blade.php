@extends('layouts.app')

@section('title', 'Report Cards')

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

    {{-- Filters --}}
    <div class="card p-6">
        <form method="GET" action="{{ route('academics.report-cards') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="font-label-md text-on-surface-variant mb-1 block">Filter by Class</label>
                <select name="class_id" class="input-field w-full" onchange="this.form.submit()">
                    <option value="">Select class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->schoolClass->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="button-primary w-full justify-center">
                    <span class="material-symbols-outlined text-[20px]">filter_list</span>
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Students Grid/Table --}}
    @if($classId)
        @if($students->count() > 0)
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
                    <div>
                        <h3 class="font-title-md text-on-surface">Generate Report Cards</h3>
                        <p class="font-label-md text-on-surface-variant mt-1">Active Term: {{ $currentTerm?->name ?: 'None' }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-lowest">
                                <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider w-12 text-center">#</th>
                                <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Student ID</th>
                                <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Student Name</th>
                                <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($students as $index => $student)
                                <tr class="hover:bg-surface-container-lowest transition-colors">
                                    <td class="p-4 font-body-md text-outline text-center">{{ $index + 1 }}</td>
                                    <td class="p-4 font-body-md text-on-surface-variant">{{ $student->student_id_number }}</td>
                                    <td class="p-4">
                                        <div class="font-body-md font-medium text-on-surface">
                                            {{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ route('academics.report-card', $student) }}" class="button-secondary inline-flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px]">download_for_offline</span>
                                            Download PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card p-12 text-center">
                <span class="material-symbols-outlined text-outline text-[48px] mb-4">group_off</span>
                <p class="font-body-lg text-on-surface-variant">No students enrolled in this class.</p>
            </div>
        @endif
    @else
        <div class="card p-12 text-center">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">description</span>
            <p class="font-body-lg text-on-surface-variant">Please select a class to view and generate student report cards.</p>
        </div>
    @endif
</div>
@endsection
