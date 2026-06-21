@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Leave Requests Management</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Review and approve or reject staff leave submissions.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="border-b border-outline-variant">
        <nav class="flex gap-4">
            <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" 
               class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $status === 'pending' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                Pending Approval
            </a>
            <a href="{{ route('admin.leaves.index', ['status' => 'approved']) }}" 
               class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $status === 'approved' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                Approved
            </a>
            <a href="{{ route('admin.leaves.index', ['status' => 'rejected']) }}" 
               class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $status === 'rejected' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                Rejected
            </a>
        </nav>
    </div>

    <!-- Leave Requests List -->
    <div class="card p-6">
        @if($leaveRequests->isEmpty())
            <div class="text-center py-12 text-on-surface-variant font-body-md">
                <span class="material-symbols-outlined text-outline mb-4" style="font-size: 48px;">inbox</span>
                <p>No leave requests found in this tab.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="p-4">Staff Member</th>
                            <th class="p-4">Period</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Reason</th>
                            @if($status === 'rejected')
                                <th class="p-4">Rejection Reason</th>
                            @endif
                            @if($status === 'pending')
                                <th class="p-4 text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-sm text-body-sm">
                        @foreach($leaveRequests as $req)
                            <tr class="hover:bg-surface-container-lowest transition-colors align-top">
                                <td class="p-4">
                                    <p class="font-medium text-on-surface">{{ $req->staff->full_name }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $req->staff->position }} ({{ $req->staff->staff_id_number }})</p>
                                </td>
                                <td class="p-4 font-mono text-xs whitespace-nowrap text-on-surface">
                                    {{ $req->start_date->format('M d, Y') }}<br>
                                    to {{ $req->end_date->format('M d, Y') }}
                                </td>
                                <td class="p-4 capitalize text-on-surface-variant">{{ $req->leave_type }}</td>
                                <td class="p-4 text-on-surface-variant max-w-sm">
                                    {{ $req->reason }}
                                </td>
                                @if($status === 'rejected')
                                    <td class="p-4 text-error font-medium">
                                        {{ $req->rejection_reason ?? 'No reason provided.' }}
                                    </td>
                                @endif
                                @if($status === 'pending')
                                    <td class="p-4 text-right">
                                        <div class="flex flex-col items-end gap-2" x-data="{ showRejectInput: false }">
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('admin.leaves.approve', $req) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-success hover:bg-success/90 text-white rounded-lg text-xs font-semibold flex items-center gap-1 transition-all">
                                                        <span class="material-symbols-outlined text-[16px]">check</span>
                                                        Approve
                                                    </button>
                                                </form>
                                                <button @click="showRejectInput = !showRejectInput" class="px-3 py-1.5 bg-error hover:bg-error/90 text-white rounded-lg text-xs font-semibold flex items-center gap-1 transition-all">
                                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                                    Reject
                                                </button>
                                            </div>
                                            
                                            <!-- Rejection Reason input field -->
                                            <div x-show="showRejectInput" class="mt-2 text-left bg-surface-container rounded-lg p-3 w-72 border border-outline-variant shadow-sm" x-transition>
                                                <form method="POST" action="{{ route('admin.leaves.reject', $req) }}">
                                                    @csrf
                                                    <label class="form-label text-[11px] mb-1">Rejection Reason</label>
                                                    <input type="text" name="rejection_reason" class="w-full px-2.5 py-1.5 text-xs bg-surface border border-outline-variant rounded-lg focus:outline-none focus:border-error transition-all" required placeholder="Required explanation...">
                                                    <div class="flex justify-end gap-2 mt-2">
                                                        <button type="button" @click="showRejectInput = false" class="px-2 py-1 text-[10px] bg-surface-container-high rounded text-on-surface-variant hover:bg-surface-container-highest">Cancel</button>
                                                        <button type="submit" class="px-2 py-1 text-[10px] bg-error text-white rounded font-semibold hover:bg-error/90">Submit Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                @endif
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
@endsection
