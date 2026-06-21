@extends('layouts.app')

@section('title', 'Staff Roles & Management')

@section('content')
<div x-data="{ activeTab: '{{ $filter === 'pending' ? 'waitlist' : 'all' }}' }">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
                Staff Roles & Management
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Manage staff roles, approve new registrations, and assign teaching subjects & form classes.
            </p>
        </div>
    </div>

    <!-- Tabs: All Staff / Waitlist -->
    <div class="flex gap-1 mb-6 bg-surface-container-low rounded-xl p-1 border border-outline-variant/50 max-w-md">
        <button @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high'"
                class="flex-1 py-2.5 px-4 rounded-lg font-medium text-sm transition-all flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">groups</span>
            All Staff
        </button>
        <button @click="activeTab = 'waitlist'"
                :class="activeTab === 'waitlist' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high'"
                class="flex-1 py-2.5 px-4 rounded-lg font-medium text-sm transition-all flex items-center justify-center gap-2 relative">
            <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
            Waitlist
            @if($pendingCount > 0)
                <span class="bg-error text-on-error text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ $pendingCount }}</span>
            @endif
        </button>
    </div>

    <!-- All Staff Tab -->
    <div x-show="activeTab === 'all'" x-transition>
        <!-- Search Bar -->
        <div class="card p-4 mb-6">
            <form method="GET" action="{{ route('staff-roles.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center w-full">
                <input type="hidden" name="filter" value="all">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input type="text" name="search" id="search-staff" value="{{ $search }}"
                           class="w-full pl-10 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                           placeholder="Search by name or email...">
                </div>
                <button type="submit" class="btn-primary !py-2.5 px-6 rounded-xl flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Search
                </button>
            </form>
        </div>

        <!-- Staff Table -->
        <div class="card overflow-hidden border border-outline-variant">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-body-md">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container-high/40 text-on-surface-variant font-semibold">
                            <th class="p-4 pl-6">Staff Member</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Form Class</th>
                            <th class="p-4">Subjects Taught</th>
                            <th class="p-4 text-right pr-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($users as $staffUser)
                            @php
                                $staffProfile = $staffUser->userable;
                                $currentRole = $staffUser->roles->first()?->name;
                                $formAssignment = $staffUser->teacherAssignments->where('is_form_teacher', true)->first();
                                $subjectAssignments = $staffUser->teacherAssignments->where('is_form_teacher', false)->filter(fn($a) => $a->subject_id);
                                $isPending = $staffProfile && $staffProfile instanceof \App\Models\Staff && $staffProfile->status === 'pending';
                            @endphp
                            <tr class="hover:bg-surface-container-high/20 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container text-sm font-bold flex items-center justify-center border border-primary/10">
                                            {{ strtoupper(substr($staffUser->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-on-surface text-sm">{{ $staffUser->name }}</h4>
                                            <span class="text-xs text-on-surface-variant block">{{ $staffUser->email }}</span>
                                            @if($staffProfile && $staffProfile instanceof \App\Models\Staff && $staffProfile->personal_code)
                                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary-container text-on-primary-container mt-0.5">Code: {{ $staffProfile->personal_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @php
                                        $roleBadge = match ($currentRole) {
                                            'HeadTeacher' => 'bg-error-container text-on-error-container',
                                            'ClassTeacher' => 'bg-primary-container text-on-primary-container',
                                            'SubjectTeacher' => 'bg-secondary-container text-on-secondary-container',
                                            'Accounts' => 'bg-tertiary-container text-on-tertiary-container',
                                            'Supervisor' => 'bg-surface-container-high text-on-surface-variant',
                                            default => 'bg-surface-container-high text-on-surface-variant',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleBadge }}">
                                        {{ $currentRole }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($isPending)
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-container text-warning">
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-container text-on-success-container">
                                            Active
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-on-surface-variant">
                                    @if($formAssignment)
                                        <span class="font-medium text-on-surface">{{ $formAssignment->schoolClass?->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="text-outline">—</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($subjectAssignments->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($subjectAssignments->take(3) as $sa)
                                                <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-surface-container-high text-on-surface-variant">
                                                    {{ $sa->subject?->name }} ({{ $sa->schoolClass?->name }})
                                                </span>
                                            @endforeach
                                            @if($subjectAssignments->count() > 3)
                                                <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-primary-container text-on-primary-container">
                                                    +{{ $subjectAssignments->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-outline text-sm">No subjects</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right pr-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff-roles.edit', $staffUser) }}"
                                           class="btn-ghost !p-2 rounded-lg text-primary hover:bg-primary-container/10 transition-colors inline-flex items-center justify-center"
                                           title="Manage Assignments">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        @if($isPending)
                                            <form method="POST" action="{{ route('staff-roles.approve', $staffUser) }}" class="inline-block">
                                                @csrf
                                                <button type="submit" class="btn-ghost !p-2 rounded-lg text-green-600 hover:bg-green-50 transition-colors inline-flex items-center justify-center" title="Approve">
                                                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-on-surface-variant">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[48px] text-outline">group_off</span>
                                        <p class="text-sm">No staff members found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Waitlist Tab -->
    <div x-show="activeTab === 'waitlist'" x-transition>
        @php
            $pendingUsers = $users->filter(function ($u) {
                $staff = $u->userable;
                return $staff && $staff instanceof \App\Models\Staff && $staff->status === 'pending';
            });
            // Re-query for waitlist to get all pending (not just current page)
        @endphp

        <div class="card overflow-hidden border border-outline-variant">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-b border-outline-variant p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-warning-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-warning text-[22px]">hourglass_top</span>
                    </div>
                    <div>
                        <h3 class="font-title-lg text-title-lg font-bold text-on-surface">Staff Waitlist</h3>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">New staff members awaiting your approval before they can access the system.</p>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-outline-variant">
                @php
                    // Get ALL pending staff (not filtered by current page)
                    $allPending = \App\Models\User::whereHas('roles', function ($q) {
                        $q->whereIn('name', ['HeadTeacher', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'Supervisor']);
                    })->whereHasMorph('userable', [\App\Models\Staff::class], function ($q) {
                        $q->where('status', 'pending');
                    })->with(['roles', 'userable'])->orderBy('created_at', 'desc')->get();
                @endphp

                @forelse($allPending as $pendUser)
                    @php
                        $pendProfile = $pendUser->userable;
                        $pendRole = $pendUser->roles->first()?->name;
                    @endphp
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4 hover:bg-surface-container-high/10 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-12 h-12 rounded-full bg-warning-container text-warning text-sm font-bold flex items-center justify-center border border-warning/20">
                                {{ strtoupper(substr($pendUser->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface">{{ $pendUser->name }}</h4>
                                <p class="text-xs text-on-surface-variant">{{ $pendUser->email }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold bg-secondary-container text-on-secondary-container">
                                        {{ $pendRole }}
                                    </span>
                                    <span class="text-[11px] text-on-surface-variant">
                                        Applied {{ $pendUser->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if($pendProfile)
                                <div class="text-xs text-on-surface-variant hidden md:block">
                                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span> {{ $pendProfile->phone ?? 'N/A' }}</span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('staff-roles.approve', $pendUser) }}" class="inline-block">
                                @csrf
                                <button type="submit" class="btn-primary !py-2 !px-4 rounded-xl flex items-center gap-1.5 text-sm font-medium">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('staff-roles.reject', $pendUser) }}" class="inline-block"
                                  onsubmit="return confirm('Are you sure you want to reject this staff member? This action cannot be undone.');">
                                @csrf
                                <button type="submit" class="btn-ghost !py-2 !px-4 rounded-xl flex items-center gap-1.5 text-sm font-medium text-error hover:bg-error-container/20">
                                    <span class="material-symbols-outlined text-[18px]">cancel</span>
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-on-surface-variant">
                        <span class="material-symbols-outlined text-[48px] text-outline mb-2">verified</span>
                        <p class="font-medium">All caught up!</p>
                        <p class="text-sm">No pending staff registrations at this time.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="h-8"></div>
@endsection
