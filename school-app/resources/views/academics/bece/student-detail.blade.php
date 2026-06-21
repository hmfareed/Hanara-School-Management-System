@extends('layouts.app')
@section('title', $student->full_name . ' — BECE Detail')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('academics.bece.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to BECE Dashboard
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">{{ $student->full_name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            {{ $enrollment?->classAcademicYear?->schoolClass?->name ?? 'JHS 3' }} — BECE Readiness Detail
        </p>
    </div>
</div>

{{-- Aggregate Summary --}}
@if($aggregateData)
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-section-gap">
    <div class="card p-5 text-center {{ $aggregateData['aggregate'] <= 24 ? 'border-2 border-[#166534]' : ($aggregateData['aggregate'] <= 36 ? 'border-2 border-warning' : 'border-2 border-error') }}">
        <p class="font-label-md text-label-md text-on-surface-variant mb-1">BECE Aggregate</p>
        <p class="font-display-md text-[48px] font-bold {{ $aggregateData['aggregate'] <= 24 ? 'text-[#166534]' : ($aggregateData['aggregate'] <= 36 ? 'text-warning' : 'text-error') }}">
            {{ $aggregateData['aggregate'] }}
        </p>
        <p class="font-label-md text-label-md mt-1">
            @if($aggregateData['aggregate'] <= 24)
                <span class="badge badge-success">Excellent</span>
            @elseif($aggregateData['aggregate'] <= 36)
                <span class="badge badge-warning">Moderate</span>
            @else
                <span class="badge badge-error">At Risk</span>
            @endif
        </p>
    </div>
    <div class="card p-5 text-center">
        <p class="font-label-md text-label-md text-on-surface-variant mb-1">Total Subjects</p>
        <p class="font-display-md text-[48px] font-bold text-on-surface">{{ $aggregateData['total_subjects'] }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Subjects graded</p>
    </div>
    <div class="card p-5 text-center">
        <p class="font-label-md text-label-md text-on-surface-variant mb-1">Best 6 Avg Grade</p>
        <p class="font-display-md text-[48px] font-bold text-primary">{{ $aggregateData['total_subjects'] > 0 ? round($aggregateData['aggregate'] / min(6, $aggregateData['total_subjects']), 1) : '—' }}</p>
        <p class="font-label-md text-label-md text-on-surface-variant">Average per subject</p>
    </div>
</div>
@endif

{{-- Mock Exam Scores by Label --}}
@forelse($mockScores as $mockLabel => $scores)
<div class="card overflow-hidden mb-6">
    <div class="p-4 bg-primary-container/10 border-b border-outline-variant">
        <h3 class="font-title-lg text-title-lg font-semibold text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">quiz</span>
            {{ $mockLabel }}
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-lowest">
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Subject</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant text-center">Raw Score (%)</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant text-center">BECE Grade</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant">Interpretation</th>
                    <th class="px-4 py-3 font-label-md text-label-md text-on-surface-variant text-center">In Best 6?</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @php
                    $best6Ids = $aggregateData ? $aggregateData['best6']->pluck('id')->toArray() : [];
                @endphp
                @foreach($scores->sortBy('bece_grade') as $score)
                <tr class="hover:bg-surface-container-lowest/50 transition-colors {{ in_array($score->id, $best6Ids) ? 'bg-primary-container/5' : '' }}">
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface font-medium">{{ $score->subject->name }}</td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface text-center">{{ $score->raw_score }}%</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex w-8 h-8 rounded-full items-center justify-center font-bold text-sm
                            {{ $score->bece_grade <= 3 ? 'bg-[#dcfce7] text-[#166534]' : ($score->bece_grade <= 6 ? 'bg-primary-container/30 text-primary' : ($score->bece_grade <= 8 ? 'bg-warning-container/30 text-warning' : 'bg-error-container/30 text-error')) }}">
                            {{ $score->bece_grade }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-body-md text-body-md text-on-surface-variant">
                        {{ \App\Models\BeceMockScore::gradeInterpretation($score->bece_grade) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if(in_array($score->id, $best6Ids))
                            <span class="material-symbols-outlined text-[#166534]">check_circle</span>
                        @else
                            <span class="material-symbols-outlined text-outline">remove</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">quiz</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No Mock Scores</h3>
    <p class="font-body-md text-body-md text-on-surface-variant">No mock exam scores have been entered for this student yet.</p>
</div>
@endforelse
@endsection
