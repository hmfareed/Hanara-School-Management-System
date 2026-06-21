@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Staff Daily Attendance Monitor</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">View daily check-in and check-out records for all school staff.</p>
        </div>
    </div>

    <!-- Date Select & Summary -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.staff-attendance.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="form-label text-xs" for="date-select">Select Date</label>
                <input id="date-select" name="date" type="date" value="{{ $date }}" class="form-input-custom !py-2" max="{{ now()->format('Y-m-d') }}" onchange="this.form.submit()">
            </div>
            <div>
                <button type="submit" class="btn-primary !py-2 text-xs flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">refresh</span>
                    Refresh Table
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    @php
        $totalStaff = $staffList->count();
        $present = 0;
        $late = 0;
        $absent = 0;
        $onLeave = 0;

        foreach ($staffList as $st) {
            $att = $attendances->get($st->id);
            if ($att) {
                if ($att->status === 'present') $present++;
                elseif ($att->status === 'late') $late++;
                elseif ($att->status === 'on_leave') $onLeave++;
            } else {
                if ($st->status === 'on_leave') $onLeave++;
                else $absent++;
            }
        }
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-on-surface">{{ $totalStaff }}</p>
            <p class="font-label-md text-label-md text-on-surface-variant">Total Staff</p>
        </div>
        <div class="card p-4 text-center border-l-4 border-success">
            <p class="text-2xl font-bold text-success">{{ $present }}</p>
            <p class="font-label-md text-label-md text-on-surface-variant">On Time</p>
        </div>
        <div class="card p-4 text-center border-l-4 border-warning">
            <p class="text-2xl font-bold text-warning">{{ $late }}</p>
            <p class="font-label-md text-label-md text-on-surface-variant">Late</p>
        </div>
        <div class="card p-4 text-center border-l-4 border-error">
            <p class="text-2xl font-bold text-error">{{ $absent }}</p>
            <p class="font-label-md text-label-md text-on-surface-variant">Absent</p>
        </div>
        <div class="card p-4 text-center border-l-4 border-info">
            <p class="text-2xl font-bold text-info">{{ $onLeave }}</p>
            <p class="font-label-md text-label-md text-on-surface-variant">On Leave</p>
        </div>
    </div>

    <!-- Staff Attendance List -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="p-4 w-12">#</th>
                        <th class="p-4">Staff Member</th>
                        <th class="p-4">Staff ID</th>
                        <th class="p-4">Position</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Clock In</th>
                        <th class="p-4">Clock Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                    @foreach ($staffList as $index => $st)
                        @php
                            $att = $attendances->get($st->id);
                        @endphp
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4 text-on-surface-variant">{{ $index + 1 }}</td>
                            <td class="p-4 font-medium text-on-surface">
                                {{ $st->full_name }}
                            </td>
                            <td class="p-4 font-mono text-xs text-on-surface-variant">{{ $st->staff_id_number }}</td>
                            <td class="p-4 text-on-surface-variant">{{ $st->position }}</td>
                            <td class="p-4">
                                @if($att)
                                    @if($att->status === 'present')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-container text-on-success-container">On Time</span>
                                    @elseif($att->status === 'late')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-container text-warning">Late</span>
                                    @elseif($att->status === 'on_leave')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-info-container text-info">On Leave</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Absent</span>
                                    @endif
                                @else
                                    @if($st->status === 'on_leave')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-info-container text-info">On Leave</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Absent</span>
                                    @endif
                                @endif
                            </td>
                            <td class="p-4 text-on-surface-variant font-mono">
                                {{ $att && $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '—' }}
                            </td>
                            <td class="p-4 text-on-surface-variant font-mono">
                                {{ $att && $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
