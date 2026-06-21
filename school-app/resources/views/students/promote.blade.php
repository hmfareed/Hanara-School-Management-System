@extends('layouts.app')

@section('title', 'Class Promotions')

@section('content')
<div class="space-y-6" x-data="{ confirmModal: false, promoteClass: '', promoteClassId: null, targetClass: '', studentCount: 0, isGraduation: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('students.index') }}" class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-[24px]">arrow_back</span>
            </a>
            <div>
                <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Class Promotions</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">Promote entire classes to their next level for the current academic year.</p>
            </div>
        </div>
    </div>

    <!-- Success/Error Alerts -->
    @if (session('success'))
        <div class="card border-l-4 border-success bg-success-container/10 p-4">
            <div class="flex items-center gap-2 text-success font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="card border-l-4 border-error bg-error-container/10 p-4 space-y-2">
            <div class="flex items-center gap-2 text-error font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">error_outline</span>
                Promotion Error
            </div>
            <ul class="list-disc list-inside space-y-1 font-body-sm text-body-sm text-error">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Info Card -->
    <div class="card p-4 bg-primary-container/10 border border-primary/20">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-[24px] mt-0.5">info</span>
            <div class="font-body-sm text-body-sm text-on-surface-variant space-y-1">
                <p><strong>How promotions work:</strong> When you promote a class, all enrolled students are moved to the next class (based on display order). Their old enrollment is marked as <code>promoted</code> and a new enrollment is created in the target class.</p>
                <p>Students in <strong>JHS3</strong> (the highest class) will be <strong>graduated</strong> instead — their status changes to <code>graduated</code> and they won't appear in active rosters.</p>
                <p><strong>Academic Year:</strong> {{ $currentYear->name ?? 'None configured' }}</p>
            </div>
        </div>
    </div>

    <!-- Class Cards Grid -->
    @if(!$currentYear)
        <div class="card p-12 text-center text-on-surface-variant">
            <span class="material-symbols-outlined text-outline text-5xl mb-3">event_busy</span>
            <p class="font-body-md">No active academic year found. Please configure one in Settings before promoting students.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($classData as $item)
                <div class="card p-5 space-y-4 hover:shadow-level-2 transition-shadow">
                    <!-- Class Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-bold
                                {{ $item['class']->level === 'nursery' ? 'bg-tertiary-container text-on-tertiary-container' : '' }}
                                {{ $item['class']->level === 'kindergarten' ? 'bg-secondary-container text-on-secondary-container' : '' }}
                                {{ $item['class']->level === 'primary' ? 'bg-primary-container text-on-primary-container' : '' }}
                                {{ $item['class']->level === 'jhs' ? 'bg-error-container text-on-error-container' : '' }}
                            ">
                                {{ str_replace(['Nursery 1', 'Nursery 2'], ['N1', 'N2'], $item['class']->name) }}
                            </div>
                            <div>
                                <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">{{ $item['class']->name }}</h4>
                                <p class="text-xs text-on-surface-variant capitalize">{{ $item['class']->level }} Level</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-surface-container-low rounded-xl text-center">
                            <p class="text-xs text-on-surface-variant font-label-md">Enrolled</p>
                            <p class="text-2xl font-bold text-primary mt-1">{{ $item['student_count'] }}</p>
                        </div>
                        <div class="p-3 bg-surface-container-low rounded-xl text-center">
                            <p class="text-xs text-on-surface-variant font-label-md">Target</p>
                            <p class="text-sm font-bold mt-1 {{ $item['is_last'] ? 'text-warning' : 'text-success' }}">
                                {{ $item['is_last'] ? 'Graduate' : $item['next_class']->name }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    @if($item['student_count'] > 0)
                        <button type="button"
                            @click="confirmModal = true; promoteClass = '{{ $item['class']->name }}'; promoteClassId = {{ $item['class']->id }}; targetClass = '{{ $item['is_last'] ? 'Graduated' : $item['next_class']->name }}'; studentCount = {{ $item['student_count'] }}; isGraduation = {{ $item['is_last'] ? 'true' : 'false' }}"
                            class="{{ $item['is_last'] ? 'bg-warning text-on-warning hover:bg-warning/90' : 'btn-primary' }} w-full justify-center !py-2.5 text-xs flex items-center gap-1.5 rounded-xl font-semibold transition-all">
                            <span class="material-symbols-outlined text-[16px]">{{ $item['is_last'] ? 'school' : 'arrow_upward' }}</span>
                            {{ $item['is_last'] ? 'Graduate All ('.$item['student_count'].')' : 'Promote All ('.$item['student_count'].')' }}
                        </button>
                    @else
                        <div class="w-full text-center py-2.5 text-xs text-on-surface-variant bg-surface-container-low rounded-xl font-medium">
                            No students enrolled
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Confirmation Modal -->
    <div x-show="confirmModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="confirmModal = false"></div>

        <!-- Modal Content -->
        <div class="card p-6 w-full max-w-md z-10 space-y-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                     :class="isGraduation ? 'bg-warning-container text-on-warning-container' : 'bg-primary-container text-on-primary-container'">
                    <span class="material-symbols-outlined text-[28px]" x-text="isGraduation ? 'school' : 'arrow_upward'"></span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-semibold text-on-surface" x-text="isGraduation ? 'Confirm Graduation' : 'Confirm Promotion'"></h3>
                    <p class="text-xs text-on-surface-variant">This action cannot be easily undone</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-container-low space-y-2 font-body-sm text-body-sm">
                <p class="text-on-surface">
                    You are about to <strong x-text="isGraduation ? 'graduate' : 'promote'"></strong>
                    <strong x-text="studentCount"></strong> student(s) from
                    <strong class="text-primary" x-text="promoteClass"></strong>
                    <span x-show="!isGraduation"> to <strong class="text-success" x-text="targetClass"></strong></span>.
                </p>
                <template x-if="isGraduation">
                    <p class="text-warning font-medium">These students will be marked as graduated and removed from active rosters.</p>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="confirmModal = false" class="btn-ghost !py-2 px-4 text-xs">Cancel</button>
                <form method="POST" action="{{ route('students.promote') }}">
                    @csrf
                    <input type="hidden" name="school_class_id" :value="promoteClassId">
                    <button type="submit"
                            class="!py-2 px-6 text-xs flex items-center gap-1.5 rounded-xl font-semibold transition-all text-white"
                            :class="isGraduation ? 'bg-warning hover:bg-warning/90' : 'bg-primary hover:bg-primary/90'">
                        <span class="material-symbols-outlined text-[16px]" x-text="isGraduation ? 'school' : 'arrow_upward'"></span>
                        <span x-text="isGraduation ? 'Graduate Students' : 'Promote Students'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
