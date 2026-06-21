@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">My Leave Requests</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Submit new leave requests and check your historical submissions.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- New Leave Request Form -->
        <div class="card p-6 h-fit">
            <h2 class="font-headline-sm text-on-surface font-semibold mb-4">Request Leave</h2>
            <form method="POST" action="{{ route('staff.leaves.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="leave_type" class="form-label">Leave Type</label>
                    <select id="leave_type" name="leave_type" class="form-input-custom" required>
                        <option value="">— Select Type —</option>
                        <option value="sick">Sick Leave</option>
                        <option value="annual">Annual Leave</option>
                        <option value="maternity">Maternity Leave</option>
                        <option value="paternity">Paternity Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="other">Other</option>
                    </select>
                    @error('leave_type')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="form-label">Start Date</label>
                        <input id="start_date" name="start_date" type="date" class="form-input-custom" min="{{ now()->format('Y-m-d') }}" required>
                        @error('start_date')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="form-label">End Date</label>
                        <input id="end_date" name="end_date" type="date" class="form-input-custom" min="{{ now()->format('Y-m-d') }}" required>
                        @error('end_date')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="reason" class="form-label">Reason / Explanation</label>
                    <textarea id="reason" name="reason" rows="4" class="form-input-custom !py-2 resize-none" placeholder="Provide a brief explanation for your leave request..." required></textarea>
                    @error('reason')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-1.5 py-2.5">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Submit Request
                </button>
            </form>
        </div>

        <!-- History -->
        <div class="lg:col-span-2 card p-6">
            <h2 class="font-headline-sm text-on-surface font-semibold mb-4">Request History</h2>
            @if($leaveRequests->isEmpty())
                <div class="text-center py-12 text-on-surface-variant font-body-md">
                    <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">receipt_long</span>
                    <p>You have not submitted any leave requests yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                                <th class="p-4">Period</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Reason</th>
                                <th class="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                            @foreach($leaveRequests as $req)
                                <tr class="hover:bg-surface-container-lowest transition-colors">
                                    <td class="p-4 font-mono text-xs whitespace-nowrap text-on-surface">
                                        {{ $req->start_date->format('M d, Y') }}<br>
                                        to {{ $req->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="p-4 capitalize text-on-surface-variant">{{ $req->leave_type }}</td>
                                    <td class="p-4 text-on-surface-variant">
                                        <p class="max-w-xs truncate" title="{{ $req->reason }}">{{ $req->reason }}</p>
                                        @if($req->status === 'rejected' && $req->rejection_reason)
                                            <p class="text-xs text-error mt-1 font-semibold">Rejected: {{ $req->rejection_reason }}</p>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if($req->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-container text-on-success-container">Approved</span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Rejected</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container-high text-on-surface-variant">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $leaveRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
