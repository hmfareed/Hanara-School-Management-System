@extends('layouts.app')
@section('title', $student->full_name . ' — Grades')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('dashboard.parent') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Dashboard
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">{{ $student->full_name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            {{ $enrollment?->classAcademicYear?->schoolClass?->name ?? 'Unassigned' }} — Academic Performance
        </p>
    </div>
    <a href="{{ route('parent.child.report-card', $student) }}" class="btn-primary inline-flex items-center gap-2" id="btn-download-report">
        <span class="material-symbols-outlined text-[18px]">download</span>
        Download Report Card
    </a>
</div>

@if($grades->isNotEmpty())
<div class="space-y-4">
    @foreach($grades as $subjectName => $scores)
    <div class="card overflow-hidden">
        <div class="p-4 bg-primary-container/20 border-b border-outline-variant flex items-center justify-between">
            <h3 class="font-title-md text-title-md font-semibold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">menu_book</span>
                {{ $subjectName }}
            </h3>
            @php
                $totalWeight = $scores->sum(fn($s) => $s->component?->weight ?? 0);
                $weightedScore = $scores->sum(function ($s) {
                    $maxScore = $s->component?->max_score ?? 100;
                    $weight = $s->component?->weight ?? 100;
                    return $maxScore > 0 ? ($s->score / $maxScore) * $weight : 0;
                });
                $percentage = $totalWeight > 0 ? round(($weightedScore / $totalWeight) * 100, 1) : 0;
            @endphp
            <div class="text-right">
                <span class="font-headline-md text-headline-md font-bold {{ $percentage >= 70 ? 'text-[#166534]' : ($percentage >= 50 ? 'text-warning' : 'text-error') }}">
                    {{ $percentage }}%
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-lowest">
                        <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant">Assessment</th>
                        <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant">Score</th>
                        <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant">Max</th>
                        <th class="px-4 py-2.5 font-label-md text-label-md text-on-surface-variant">Weight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($scores as $score)
                    <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                        <td class="px-4 py-2.5 font-body-md text-body-md text-on-surface">{{ $score->component?->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-body-md text-body-md font-semibold text-on-surface">{{ $score->score }}</td>
                        <td class="px-4 py-2.5 font-body-md text-body-md text-on-surface-variant">{{ $score->component?->max_score ?? '—' }}</td>
                        <td class="px-4 py-2.5 font-body-md text-body-md text-on-surface-variant">{{ $score->component?->weight ?? '—' }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">school</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No Grades Available</h3>
    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
        Assessment scores for this term have not been published yet.
    </p>
</div>
@endif
@endsection
