<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Show list of leave requests for the logged-in staff member.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->userable_type !== Staff::class || !$user->userable) {
            return redirect()->route('dashboard')->with('error', 'Only staff members can request leaves.');
        }

        $staff = $user->userable;
        $leaveRequests = LeaveRequest::where('staff_id', $staff->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leaves.index', compact('leaveRequests'));
    }

    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->userable_type !== Staff::class || !$user->userable) {
            return back()->with('error', 'Only staff members can submit leave requests.');
        }

        $staff = $user->userable;

        $request->validate([
            'leave_type' => 'required|in:sick,annual,maternity,paternity,casual,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        LeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted successfully and is pending approval.');
    }

    /**
     * Admin view of all leave requests.
     */
    public function adminIndex(Request $request)
    {
        $this->authorizeRole(['Proprietor', 'HeadTeacher']);

        $status = $request->input('status', 'pending');
        $leaveRequests = LeaveRequest::with('staff')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('leaves.admin-index', compact('leaveRequests', 'status'));
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorizeRole(['Proprietor', 'HeadTeacher']);

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // If today is within the leave range, update staff status to 'on_leave'
        $today = Carbon::today();
        if ($today->between($leaveRequest->start_date, $leaveRequest->end_date)) {
            $leaveRequest->staff->update(['status' => 'on_leave']);
        }

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeRole(['Proprietor', 'HeadTeacher']);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Leave request has been rejected.');
    }

    /**
     * Helper to authorize roles.
     */
    protected function authorizeRole(array $roles)
    {
        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
