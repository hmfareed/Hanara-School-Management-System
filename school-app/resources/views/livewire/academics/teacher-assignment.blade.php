<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-md text-on-background">Subject Teacher Assignments</h2>
            <p class="font-body-md text-on-surface-variant">Map teachers to subjects for the current academic year.</p>
        </div>

        <div class="w-full md:w-64">
            <label class="font-label-md text-on-surface-variant mb-1 block">Select Class</label>
            <select wire:model.live="selectedClassId" class="input-field w-full">
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->schoolClass->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant">
                        <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Subject</th>
                        <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Assigned Teacher
                        </th>
                        <th class="p-4 font-label-md text-on-surface-variant uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($subjects as $subject)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4">
                                <div class="font-body-md font-medium text-on-surface">{{ $subject->name }}</div>
                                <div class="font-label-md text-outline">{{ $subject->code }}</div>
                            </td>
                            <td class="p-4">
                                <select wire:change="saveAssignment({{ $subject->id }}, $event.target.value)"
                                    class="input-field w-full max-w-xs">
                                    <option value="">Unassigned</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ ($assignments[$subject->id] ?? '') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-4 text-center">
                                @if(isset($assignments[$subject->id]))
                                    <span class="material-symbols-outlined text-[#166534] text-[20px]">check_circle</span>
                                @else
                                    <span class="material-symbols-outlined text-outline text-[20px]">pending</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div wire:loading
        class="fixed bottom-4 right-4 bg-primary text-on-primary px-4 py-2 rounded-full shadow-lg font-label-md">
        Saving changes...
    </div>
</div>
