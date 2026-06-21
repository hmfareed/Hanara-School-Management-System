@extends('layouts.app')

@section('title', 'Student Directory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Student Directory</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">View and manage student academic profiles, contact details, and records.</p>
        </div>
        @unless($isReadOnly ?? false)
        <div class="flex items-center gap-3">
            <a href="{{ route('students.import') }}" class="btn-ghost !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">publish</span>
                Bulk Import
            </a>
            <a href="{{ route('students.promotions') }}" class="btn-ghost !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">moving</span>
                Class Promotions
            </a>
            <a href="{{ route('students.create') }}" class="btn-primary !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                Add Student
            </a>
            <a href="{{ route('admissions.apply') }}" class="btn-accent !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Apply Admission
            </a>
        </div>
        @endunless
    </div>

    <!-- Filters Card -->
    <div class="card p-4">
        <form method="GET" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="form-label text-xs" for="search">Search</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input type="text" name="search" id="search" value="{{ $search }}" 
                           class="form-input-custom !pl-10 !py-2" 
                           placeholder="Search by name or student ID...">
                </div>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="form-label text-xs" for="class_id">Class</label>
                <select name="class_id" id="class_id" class="form-input-custom !py-2">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ ucfirst($class->level) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 !py-2 text-xs flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Filter
                </button>
                <a href="{{ route('students.index') }}" class="btn-ghost !py-2 text-xs flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Student List Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
            <span class="font-label-lg text-label-lg font-semibold text-on-surface">Roster ({{ $students->total() }} students)</span>
            <div class="flex gap-2">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}" 
                   class="px-3 py-1 rounded-full text-xs font-medium {{ $status === 'active' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                    Active
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'graduated']) }}" 
                   class="px-3 py-1 rounded-full text-xs font-medium {{ $status === 'graduated' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                    Graduated
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'transferred']) }}" 
                   class="px-3 py-1 rounded-full text-xs font-medium {{ $status === 'transferred' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                    Transferred
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'withdrawn']) }}" 
                   class="px-3 py-1 rounded-full text-xs font-medium {{ $status === 'withdrawn' ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                    Withdrawn
                </a>
            </div>
        </div>

        @if($students->isEmpty())
            <div class="p-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-outline text-5xl mb-3">group_off</span>
                <p class="font-body-md">No students found matching your filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="p-4">Student</th>
                            <th class="p-4">Student ID</th>
                            <th class="p-4">Class</th>
                            <th class="p-4">Gender</th>
                            <th class="p-4">Primary Contact</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                        @foreach ($students as $student)
                            @php
                                $enrollment = $student->currentClassEnrollment();
                                $primaryGuardian = $student->guardians->where('pivot.is_primary', true)->first() 
                                    ?? $student->guardians->first();
                            @endphp
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 flex items-center gap-3">
                                    @if($student->photo)
                                        <img src="{{ asset('storage/' . $student->photo) }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('students.show', $student) }}" class="font-medium text-primary hover:underline text-body-sm">
                                            {{ $student->full_name }}
                                        </a>
                                        <div class="text-xs text-on-surface-variant">DOB: {{ $student->date_of_birth->format('d M, Y') }}</div>
                                    </div>
                                </td>
                                <td class="p-4 font-mono text-xs text-on-surface-variant">{{ $student->student_id_number }}</td>
                                <td class="p-4">
                                    @if($enrollment)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-secondary-container text-on-secondary-container text-xs font-medium">
                                            {{ $enrollment->classAcademicYear->schoolClass->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="p-4 capitalize">{{ $student->gender }}</td>
                                <td class="p-4">
                                    @if($primaryGuardian)
                                        <div>{{ $primaryGuardian->first_name }} {{ $primaryGuardian->last_name }}</div>
                                        <div class="text-xs text-on-surface-variant">{{ $primaryGuardian->phone }}</div>
                                    @else
                                        <span class="text-xs text-outline italic">No contacts</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($student->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-xs font-medium">Active</span>
                                    @elseif($student->status === 'graduated')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-container text-on-primary-container text-xs font-medium">Graduated</span>
                                    @elseif($student->status === 'transferred')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-xs font-medium">Transferred</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-xs font-medium">Withdrawn</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('students.show', $student) }}" class="btn-ghost !py-1 !px-2.5 text-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        Profile
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-outline-variant bg-surface-container-low">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
