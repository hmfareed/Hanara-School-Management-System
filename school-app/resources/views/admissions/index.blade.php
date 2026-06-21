@extends('layouts.app')

@section('title', 'Admissions Queue')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Admissions Queue</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Review, assess, and enroll student applicants.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex border-b border-outline-variant gap-2 overflow-x-auto">
        <a href="{{ route('admissions.index', ['status' => 'pending', 'level' => $level]) }}" 
           class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap {{ $status === 'pending' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
            Pending Review
        </a>
        <a href="{{ route('admissions.index', ['status' => 'accepted', 'level' => $level]) }}" 
           class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap {{ $status === 'accepted' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
            Enrolled / Accepted
        </a>
        <a href="{{ route('admissions.index', ['status' => 'declined', 'level' => $level]) }}" 
           class="pb-3 px-4 border-b-2 font-body-md text-body-md transition-colors whitespace-nowrap {{ $status === 'declined' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
            Declined
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="card p-4">
        <form action="{{ route('admissions.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <input type="hidden" name="status" value="{{ $status }}">
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <span class="font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Filter Level:</span>
                <select name="level" class="form-input-custom !py-2" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    <option value="nursery" {{ $level === 'nursery' ? 'selected' : '' }}>Nursery / Crèche</option>
                    <option value="kindergarten" {{ $level === 'kindergarten' ? 'selected' : '' }}>Kindergarten</option>
                    <option value="primary" {{ $level === 'primary' ? 'selected' : '' }}>Primary</option>
                    <option value="jhs" {{ $level === 'jhs' ? 'selected' : '' }}>JHS</option>
                </select>
            </div>

            <div class="text-on-surface-variant font-label-md text-label-md">
                Showing {{ $admissions->firstItem() ?? 0 }}-{{ $admissions->lastItem() ?? 0 }} of {{ $admissions->total() }} applications
            </div>
        </form>
    </div>

    <!-- Applications Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="p-4">Applicant Name</th>
                        <th class="p-4">Gender</th>
                        <th class="p-4">DOB (Age)</th>
                        <th class="p-4">Applied Class</th>
                        <th class="p-4">Guardian Name / Phone</th>
                        <th class="p-4">Submission Date</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                    @forelse ($admissions as $admission)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4 font-medium text-on-surface">
                                {{ $admission->full_name }}
                            </td>
                            <td class="p-4 uppercase text-xs">{{ $admission->gender }}</td>
                            <td class="p-4">
                                {{ $admission->date_of_birth->format('M d, Y') }} 
                                <span class="text-on-surface-variant text-xs">({{ $admission->date_of_birth->age }} yrs)</span>
                            </td>
                            <td class="p-4">
                                <span class="badge badge-info uppercase">{{ $admission->level }}</span>
                                <span class="font-medium text-xs ml-1">{{ $admission->assignedClass ? $admission->assignedClass->name : 'Unassigned' }}</span>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-on-surface">{{ $admission->guardian_name }}</div>
                                <div class="text-on-surface-variant text-xs font-mono">{{ $admission->guardian_phone }}</div>
                            </td>
                            <td class="p-4 text-on-surface-variant">
                                {{ $admission->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('admissions.show', $admission->id) }}" class="btn-ghost !py-1.5 !px-3 text-xs flex inline-flex items-center gap-1.5 justify-center">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-on-surface-variant font-body-md">
                                <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">inbox</span>
                                <p>No admission applications found in this queue.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($admissions->hasPages())
            <div class="p-4 border-t border-outline-variant">
                {{ $admissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
