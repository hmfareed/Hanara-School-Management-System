<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /**
     * Staff clock-in action.
     */
    public function clockIn(Request $request)
    {
        $user = auth()->user();
        if ($user->userable_type !== Staff::class || !$user->userable) {
            return back()->with('error', 'Only staff members can clock in.');
        }

        $staff = $user->userable;
        $today = Carbon::today()->toDateString();

        // Check if already clocked in today
        $existing = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return back()->with('warning', 'You have already clocked in today.');
        }

        // Determine if late (after 08:00 AM)
        $limitTime = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
        $status = now()->greaterThan($limitTime) ? 'late' : 'present';

        StaffAttendance::create([
            'staff_id' => $staff->id,
            'date' => $today,
            'status' => $status,
            'clock_in' => now(),
        ]);

        return back()->with('success', 'Clocked in successfully at ' . now()->format('h:i A') . ($status === 'late' ? ' (Marked Late)' : ''));
    }

    /**
     * Staff clock-out action.
     */
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        if ($user->userable_type !== Staff::class || !$user->userable) {
            return back()->with('error', 'Only staff members can clock out.');
        }

        $staff = $user->userable;
        $today = Carbon::today()->toDateString();

        // Find today's clock-in record
        $attendance = StaffAttendance::where('staff_id', $staff->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You must clock in before clocking out.');
        }

        if ($attendance->clock_out) {
            return back()->with('warning', 'You have already clocked out today.');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return back()->with('success', 'Clocked out successfully at ' . now()->format('h:i A'));
    }

    /**
     * Admin view of all staff attendance.
     */
    public function adminIndex(Request $request)
    {
        $this->authorizeRole(['Proprietor', 'HeadTeacher', 'Supervisor']);

        $date = $request->input('date', Carbon::today()->toDateString());
        $staffList = Staff::whereIn('status', ['active', 'on_leave'])->orderBy('last_name')->get();
        
        $attendances = StaffAttendance::whereDate('date', $date)
            ->get()
            ->keyBy('staff_id');

        return view('staff-attendance.admin-index', compact('staffList', 'attendances', 'date'));
    }

    /**
     * Helper to authorize roles in controllers.
     */
    protected function authorizeRole(array $roles)
    {
        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
