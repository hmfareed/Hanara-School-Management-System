@extends('layouts.app')
@section('title', 'Enter BECE Mock Scores')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('academics.bece.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to BECE Dashboard
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">Enter Mock Exam Scores</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Record mock BECE scores for JHS 3 students. Enter raw percentage scores — BECE grades (1-9) are computed automatically.</p>
    </div>
</div>

@if($students->isNotEmpty() && $subjects->isNotEmpty() && $classAcademicYearId)
<form action="{{ route('academics.bece.store-scores') }}" method="POST" id="form-bece-scores">
    @csrf
    <input type="hidden" name="class_academic_year_id" value="{{ $classAcademicYearId }}">

    {{-- Mock Label --}}
    <div class="card p-4 mb-6 flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1">
            <label for="mock_exam_label" class="block font-label-md text-label-md text-on-surface-variant mb-1">Mock Exam Label</label>
            <select name="mock_exam_label" id="mock_exam_label" class="w-full md:w-48 px-4 py-2.5 border border-outline-variant rounded-lg bg-surface text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                <option value="Mock 1">Mock 1</option>
                <option value="Mock 2">Mock 2</option>
                <option value="Mock 3">Mock 3</option>
            </select>
        </div>
        <p class="font-label-md text-label-md text-on-surface-variant bg-primary-container/20 px-3 py-1.5 rounded-lg">
            <span class="material-symbols-outlined text-[16px] align-middle">info</span>
            Enter raw % scores (0-100). BECE grades are auto-calculated.
        </p>
    </div>

    {{-- Score Entry Table --}}
    @foreach($students as $student)
    <div class="card overflow-hidden mb-4">
        <div class="p-3 bg-primary-container/10 border-b border-outline-variant flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-bold text-sm">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
            <div>
                <span class="font-title-md text-title-md font-semibold text-on-surface">{{ $student->full_name }}</span>
                <span class="font-label-md text-label-md text-on-surface-variant ml-2">({{ $student->student_id_number }})</span>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 p-4">
            @foreach($subjects as $subject)
            <div>
                <label class="block font-label-md text-label-md text-on-surface-variant mb-1 truncate" title="{{ $subject->name }}">{{ $subject->name }}</label>
                <input type="hidden" name="scores[{{ $loop->parent->index }}_{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                <input type="hidden" name="scores[{{ $loop->parent->index }}_{{ $loop->index }}][subject_id]" value="{{ $subject->id }}">
                <input type="number" name="scores[{{ $loop->parent->index }}_{{ $loop->index }}][raw_score]" min="0" max="100" step="0.5"
                    class="w-full px-3 py-2 border border-outline-variant rounded-lg bg-surface text-body-md text-center focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    placeholder="—">
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex items-center gap-3 mt-6">
        <button type="submit" class="btn-primary py-2.5 px-6 flex items-center gap-2" id="btn-save-scores">
            <span class="material-symbols-outlined text-[18px]">save</span>
            Save Mock Scores
        </button>
        <a href="{{ route('academics.bece.index') }}" class="btn-outlined py-2.5 px-6">Cancel</a>
    </div>
</form>
@else
<div class="card p-8 text-center">
    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">school</span>
    <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No JHS 3 Data</h3>
    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto">
        No JHS 3 students or subjects are available for the current academic year. Ensure students are enrolled and JHS-level subjects exist.
    </p>
</div>
@endif
@endsection
