@extends('layouts.app')
@section('title', $student->full_name . ' — Attendance')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('dashboard.parent') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Dashboard
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">{{ $student->full_name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            {{ $enrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned' }} — Attendance Record
        </p>
    </div>
</div>

{{-- Attendance Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-section-gap">
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-primary mb-2" style="font-size: 32px;">event_available</span>
        <p class="font-headline-md text-headline-md font-bold text-primary">{{ $stats['rate'] }}%</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Attendance Rate</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-[#166534] mb-2" style="font-size: 32px;">check_circle</span>
        <p class="font-headline-md text-headline-md font-bold text-[#166534]">{{ $stats['present'] }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Days Present</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-error mb-2" style="font-size: 32px;">cancel</span>
        <p class="font-headline-md text-headline-md font-bold text-error">{{ $stats['absent'] }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Days Absent</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-warning mb-2" style="font-size: 32px;">schedule</span>
        <p class="font-headline-md text-headline-md font-bold text-warning">{{ $stats['late'] }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Days Late</p>
    </div>
</div>

{{-- Attendance Log --}}
<div class="card overflow-hidden">
    <div class="p-4 border-b border-outline-variant">
        <h3 class="font-title-lg text-title-lg text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">history</span>
            Attendance History
        </h3>
    </div>
    @if($attendanceRecords->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-lowest">
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Date</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Day</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Status</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @foreach($attendanceRecords as $record)
                <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface">{{ $record->date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface-variant">{{ $record->date->format('l') }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ match($record->status) { 'present' => 'badge-success', 'absent' => 'badge-error', 'late' => 'badge-warning', default => 'badge-info' } }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface-variant">{{ $record->remarks ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="p-8 text-center text-on-surface-variant">
        <span class="material-symbols-outlined text-outline mb-2" style="font-size: 48px;">event_busy</span>
        <p class="font-body-md text-body-md">No attendance records found for this term.</p>
    </div>
    @endif
</div>
@endsection
