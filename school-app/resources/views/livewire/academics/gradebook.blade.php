<div class="space-y-6">
    {{-- Filter Bar --}}
    <div class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="font-label-md text-on-surface-variant mb-1 block">Class</label>
            <select wire:model.live="selectedClassId" class="input-field w-full">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->schoolClass->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="font-label-md text-on-surface-variant mb-1 block">Subject</label>
            <select wire:model.live="selectedSubjectId" class="input-field w-full">
                <option value="">Select subject...</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="font-label-md text-on-surface-variant mb-1 block">Assessment Component</label>
            <select wire:model.live="selectedComponentId" class="input-field w-full">
                <option value="">Select component...</option>
                @foreach($components as $component)
                    <option value="{{ $component->id }}">{{ $component->name }} (Max: {{ $component->max_score }}, Weight: {{ $component->weight }}%)</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Score Entry Grid --}}
    @if($selectedClassId && $selectedSubjectId && $selectedComponentId && count($students) > 0)
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-outline-variant bg-surface-container-low">
                <h3 class="font-title-md text-on-surface">Score Entry</h3>
                <p class="font-label-md text-on-surface-variant mt-1">Enter scores below. Changes save automatically on blur.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container-lowest">
                            <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider w-12">#</th>
                            <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Student ID</th>
                            <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Student Name</th>
                            <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider w-36">Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($students as $index => $student)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-body-md text-outline">{{ $index + 1 }}</td>
                                <td class="p-4 font-body-md text-on-surface-variant">{{ $student->student_id_number }}</td>
                                <td class="p-4">
                                    <div class="font-body-md font-medium text-on-surface">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                </td>
                                <td class="p-4">
                                    <input type="number"
                                           step="0.5"
                                           min="0"
                                           wire:model.blur="scores.{{ $student->id }}"
                                           wire:change="saveScore({{ $student->id }})"
                                           class="input-field w-full max-w-[120px] text-center {{ $errors->has('scores.'.$student->id) ? 'border-error text-error' : '' }}"
                                           placeholder="—">
                                    @error('scores.'.$student->id)
                                        <p class="text-error font-label-md mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedClassId && $selectedSubjectId && $selectedComponentId)
        <div class="card p-12 text-center">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">group_off</span>
            <p class="font-body-lg text-on-surface-variant">No students enrolled in this class.</p>
        </div>
    @else
        <div class="card p-12 text-center">
            <span class="material-symbols-outlined text-outline text-[48px] mb-4">edit_note</span>
            <p class="font-body-lg text-on-surface-variant">Select a class, subject, and assessment component to start entering scores.</p>
        </div>
    @endif

    {{-- Auto-save Indicator --}}
    <div wire:loading class="fixed bottom-4 right-4 bg-primary text-on-primary px-4 py-2 rounded-full shadow-lg font-label-md flex items-center gap-2 z-50">
        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Saving...
    </div>
</div>
