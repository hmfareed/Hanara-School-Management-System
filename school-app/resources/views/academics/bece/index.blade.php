@extends('layouts.app')
@section('title', 'BECE Readiness')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">BECE Readiness Dashboard</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">JHS 3 mock exam performance and aggregate tracking.</p>
    </div>
    <a href="{{ route('academics.bece.enter-scores') }}" class="btn-primary py-2 px-4 flex items-center gap-2" id="btn-enter-scores">
        <span class="material-symbols-outlined text-[18px]">edit_note</span>
        Enter Mock Scores
    </a>
</div>

{{-- Summary Cards --}}
@php
    $totalStudents = $students->count();
    $atRisk = $students->where('is_at_risk', true)->count();
    $scored = $students->whereNotNull('aggregate')->count();
    $avgAggregate = $scored > 0 ? round($students->whereNotNull('aggregate')->avg('aggregate'), 1) : 0;
@endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-section-gap">
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-primary mb-2" style="font-size: 32px;">groups</span>
        <p class="font-headline-md text-headline-md font-bold text-on-surface">{{ $totalStudents }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">JHS 3 Students</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-[#166534] mb-2" style="font-size: 32px;">fact_check</span>
        <p class="font-headline-md text-headline-md font-bold text-[#166534]">{{ $scored }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Scores Entered</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-error mb-2" style="font-size: 32px;">warning</span>
        <p class="font-headline-md text-headline-md font-bold text-error">{{ $atRisk }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">At Risk (>36)</p>
    </div>
    <div class="card p-4 text-center">
        <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 32px;">analytics</span>
        <p class="font-headline-md text-headline-md font-bold text-on-surface">{{ $avgAggregate }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Avg Aggregate</p>
    </div>
</div>

{{-- Grading Scale Reference --}}
<div class="card p-4 mb-6 bg-surface-container-lowest">
    <div class="flex items-center gap-2 mb-2">
        <span class="material-symbols-outlined text-primary text-[18px]">info</span>
        <span class="font-label-md text-label-md text-on-surface font-semibold">WAEC BECE Grading: </span>
    </div>
    <div class="flex flex-wrap gap-2 font-label-md text-label-md">
        <span class="px-2 py-0.5 rounded bg-[#dcfce7] text-[#166534]">1 (80-100)</span>
        <span class="px-2 py-0.5 rounded bg-[#dcfce7] text-[#166534]">2 (70-79)</span>
        <span class="px-2 py-0.5 rounded bg-[#dcfce7] text-[#166534]">3 (65-69)</span>
        <span class="px-2 py-0.5 rounded bg-primary-container/30 text-primary">4 (60-64)</span>
        <span class="px-2 py-0.5 rounded bg-primary-container/30 text-primary">5 (55-59)</span>
        <span class="px-2 py-0.5 rounded bg-primary-container/30 text-primary">6 (50-54)</span>
        <span class="px-2 py-0.5 rounded bg-warning-container/30 text-warning">7 (40-49)</span>
        <span class="px-2 py-0.5 rounded bg-warning-container/30 text-warning">8 (30-39)</span>
        <span class="px-2 py-0.5 rounded bg-error-container/30 text-error">9 (0-29)</span>
    </div>
    <p class="font-label-md text-label-md text-on-surface-variant mt-2">Aggregate = Sum of best 6 subject grades. Best possible = 6, worst = 54. At-risk threshold: >36.</p>
</div>

{{-- Student Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-lowest">
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">#</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Student</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">ID</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Subjects</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Aggregate</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Status</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($students as $idx => $entry)
                <tr class="hover:bg-surface-container-lowest/50 transition-colors {{ $entry['is_at_risk'] ? 'bg-error-container/5' : '' }}">
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface-variant">{{ $idx + 1 }}</td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface font-medium">{{ $entry['student']->full_name }}</td>
                    <td class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">{{ $entry['student']->student_id_number }}</td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface-variant">{{ $entry['total_subjects'] }}</td>
                    <td class="px-4 py-3">
                        @if($entry['aggregate'] !== null)
                        <span class="font-title-md text-title-md font-bold {{ $entry['aggregate'] <= 24 ? 'text-[#166534]' : ($entry['aggregate'] <= 36 ? 'text-warning' : 'text-error') }}">
                            {{ $entry['aggregate'] }}
                        </span>
                        @else
                        <span class="text-outline">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($entry['aggregate'] !== null)
                            @if($entry['aggregate'] <= 24)
                                <span class="badge badge-success">Excellent</span>
                            @elseif($entry['aggregate'] <= 36)
                                <span class="badge badge-warning">Moderate</span>
                            @else
                                <span class="badge badge-error">At Risk</span>
                            @endif
                        @else
                            <span class="badge badge-info">No Scores</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('academics.bece.student-detail', $entry['student']) }}" class="text-primary hover:underline font-label-md text-label-md flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">visibility</span> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-on-surface-variant">
                        <p class="font-body-md text-body-md">No JHS 3 students found for the current academic year.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
