<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Daily Attendance</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Select a class and date, then mark attendance for each student.</p>
        </div>
    </div>

    <!-- Controls Row -->
    <div class="card p-4">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="form-label text-xs" for="class-select">Class</label>
                <select wire:model.live="selectedClassId" id="class-select" class="form-input-custom !py-2">
                    <option value="">— Select Class —</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} ({{ ucfirst($class->level) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="form-label text-xs" for="date-select">Date</label>
                <input wire:model.live="date" id="date-select" type="date" class="form-input-custom !py-2" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="flex gap-2">
                <button wire:click="markAllPresent" class="btn-ghost !py-2 text-xs flex items-center gap-1.5" {{ empty($students) ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    All Present
                </button>
                <button wire:click="save" class="btn-primary !py-2 text-xs flex items-center gap-1.5" {{ empty($students) ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Save Attendance
                </button>
            </div>
        </div>
    </div>

    <!-- Flash saved message -->
    @if ($saved)
        <div class="p-4 bg-success-container text-on-success-container rounded-xl flex items-center gap-3 border border-success/20">
            <span class="material-symbols-outlined">check_circle</span>
            <span class="font-body-md text-body-md">Attendance saved successfully for {{ count($students) }} students.</span>
        </div>
    @endif

    <!-- Summary Cards -->
    @if (!empty($students))
        <div class="grid grid-cols-3 gap-4">
            <div class="card p-4 text-center border-l-4 border-success">
                <p class="text-2xl font-bold text-success">{{ $summary['present'] }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Present</p>
            </div>
            <div class="card p-4 text-center border-l-4 border-error">
                <p class="text-2xl font-bold text-error">{{ $summary['absent'] }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Absent</p>
            </div>
            <div class="card p-4 text-center border-l-4 border-warning">
                <p class="text-2xl font-bold text-warning">{{ $summary['late'] }}</p>
                <p class="font-label-md text-label-md text-on-surface-variant">Late</p>
            </div>
        </div>
    @endif

    <!-- Attendance Grid -->
    @if (!empty($students))
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="p-4 w-12">#</th>
                            <th class="p-4">Student Name</th>
                            <th class="p-4">Student ID</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                        @foreach ($students as $index => $student)
                            <tr class="hover:bg-surface-container-lowest transition-colors
                                {{ ($statuses[$student['id']] ?? 'present') === 'absent' ? 'bg-error-container/10' : '' }}
                                {{ ($statuses[$student['id']] ?? 'present') === 'late' ? 'bg-warning-container/10' : '' }}">
                                <td class="p-4 text-on-surface-variant">{{ $index + 1 }}</td>
                                <td class="p-4 font-medium text-on-surface">
                                    {{ $student['first_name'] }} {{ $student['other_names'] ?? '' }} {{ $student['last_name'] }}
                                </td>
                                <td class="p-4 font-mono text-xs text-on-surface-variant">{{ $student['student_id_number'] }}</td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="$set('statuses.{{ $student['id'] }}', 'present')"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                                {{ ($statuses[$student['id']] ?? 'present') === 'present' ? 'bg-success text-white shadow-sm' : 'bg-surface-container-high text-on-surface-variant hover:bg-success/20' }}">
                                            P
                                        </button>
                                        <button wire:click="$set('statuses.{{ $student['id'] }}', 'absent')"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                                {{ ($statuses[$student['id']] ?? 'present') === 'absent' ? 'bg-error text-white shadow-sm' : 'bg-surface-container-high text-on-surface-variant hover:bg-error/20' }}">
                                            A
                                        </button>
                                        <button wire:click="$set('statuses.{{ $student['id'] }}', 'late')"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                                                {{ ($statuses[$student['id']] ?? 'present') === 'late' ? 'bg-warning text-white shadow-sm' : 'bg-surface-container-high text-on-surface-variant hover:bg-warning/20' }}">
                                            L
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <input wire:model.defer="remarks.{{ $student['id'] }}" type="text"
                                           class="w-full px-2 py-1 text-xs border border-outline-variant rounded-lg bg-surface focus:outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary transition-all"
                                           placeholder="Optional note...">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($selectedClassId)
        <div class="card p-12 text-center text-on-surface-variant font-body-md">
            <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">group_off</span>
            <p>No students enrolled in this class for the current academic year.</p>
        </div>
    @else
        <div class="card p-12 text-center text-on-surface-variant font-body-md">
            <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">fact_check</span>
            <p>Select a class and date to begin marking attendance.</p>
        </div>
    @endif
</div>
