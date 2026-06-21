@extends('layouts.app')

@section('title', 'Monthly Attendance Register')

@section('content')
<div class="space-y-6">
    <!-- Sub-navigation Tabs -->
    <div class="border-b border-outline-variant flex gap-1 overflow-x-auto">
        <a href="{{ route('attendance.mark') }}" 
           class="px-4 py-2.5 font-label-md text-label-md border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-all">
            Daily marking
        </a>
        <a href="{{ route('attendance.register') }}" 
           class="px-4 py-2.5 font-label-md text-label-md border-b-2 border-primary text-primary font-semibold transition-all">
            Monthly register
        </a>
    </div>

    <!-- Filter Bar Card -->
    <div class="card p-4">
        <form method="GET" action="{{ route('attendance.register') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Class select -->
            <div>
                <label class="form-label text-xs" for="class-select">Class</label>
                <select name="class_id" id="class-select" class="form-input-custom !py-2" required>
                    <option value="">— Select Class —</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ ucfirst($class->level) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Month select -->
            <div>
                <label class="form-label text-xs" for="month-select">Month</label>
                <select name="month" id="month-select" class="form-input-custom !py-2">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Year select -->
            <div>
                <label class="form-label text-xs" for="year-select">Year</label>
                <select name="year" id="year-select" class="form-input-custom !py-2">
                    @for ($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Action button -->
            <button type="submit" class="btn-primary !py-2 text-xs flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">visibility</span>
                View Register
            </button>
        </form>
    </div>

    <!-- Attendance Grid Card -->
    @if ($classId && !empty($grid))
        <div class="card overflow-hidden">
            <div class="p-4 border-b border-outline-variant bg-surface-container-low flex items-center justify-between">
                <span class="font-label-lg text-label-lg font-semibold text-on-surface">
                    Monthly Register — {{ $dateObj->format('F Y') }}
                </span>
                <div class="flex gap-4 text-xs font-medium">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-success"></span> Present (P)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-warning"></span> Late (L)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-error"></span> Absent (A)</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border-y border-outline-variant text-center font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container-lowest text-on-surface font-semibold border-b border-outline-variant">
                            <th class="p-3 text-left min-w-[200px] border-r border-outline-variant">Student Name</th>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                <th class="p-1 min-w-[28px] text-xs font-mono border-r border-outline-variant">{{ $day }}</th>
                            @endfor
                            <th class="p-2 text-success font-semibold border-r border-outline-variant min-w-[36px]">P</th>
                            <th class="p-2 text-warning font-semibold border-r border-outline-variant min-w-[36px]">L</th>
                            <th class="p-2 text-error font-semibold border-r border-outline-variant min-w-[36px]">A</th>
                            <th class="p-2 font-semibold min-w-[48px]">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($grid as $studentId => $row)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-3 text-left font-medium text-on-surface border-r border-outline-variant">
                                    <a href="{{ route('students.show', $row['student']) }}" class="hover:underline text-primary">
                                        {{ $row['student']->full_name }}
                                    </a>
                                </td>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $status = $row['days'][$day];
                                        $cellClass = '';
                                        $cellText = '';
                                        if ($status === 'present') {
                                            $cellClass = 'text-success font-bold';
                                            $cellText = 'P';
                                        } elseif ($status === 'late') {
                                            $cellClass = 'text-warning font-bold';
                                            $cellText = 'L';
                                        } elseif ($status === 'absent') {
                                            $cellClass = 'text-error font-bold';
                                            $cellText = 'A';
                                        }
                                    @endphp
                                    <td class="p-1 border-r border-outline-variant font-mono text-xs {{ $cellClass }}">
                                        {{ $cellText ?: '·' }}
                                    </td>
                                @endfor
                                <td class="p-2 text-success font-bold border-r border-outline-variant">{{ $row['present'] }}</td>
                                <td class="p-2 text-warning font-bold border-r border-outline-variant">{{ $row['late'] }}</td>
                                <td class="p-2 text-error font-bold border-r border-outline-variant">{{ $row['absent'] }}</td>
                                <td class="p-2 font-semibold">
                                    {{ $row['rate'] !== null ? $row['rate'] . '%' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($classId)
        <div class="card p-12 text-center text-on-surface-variant font-body-md">
            <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">group_off</span>
            <p>No active students enrolled in this class to display register details.</p>
        </div>
    @else
        <div class="card p-12 text-center text-on-surface-variant font-body-md">
            <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">calendar_month</span>
            <p>Select a class and target month to load register data.</p>
        </div>
    @endif
</div>
@endsection
