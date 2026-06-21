@extends('layouts.app')

@section('title', 'Front Desk Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
            Front Desk Dashboard
        </h2>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Manage inquiries, student admissions queue, and general parent communication.
        </p>
    </div>
</div>

<!-- Top Row: KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-section-gap" id="kpi-cards">
    <!-- KPI 1: Total Applications -->
    <div class="kpi-card" id="kpi-total-admissions">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Applications</p>
            <span class="material-symbols-outlined text-outline text-[20px]">assignment</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $totalAdmissions }}</h3>
            <span class="text-xs text-on-surface-variant">All time</span>
        </div>
    </div>

    <!-- KPI 2: Pending -->
    <div class="kpi-card border-l-4 border-warning" id="kpi-pending-admissions">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Pending Review</p>
            <span class="material-symbols-outlined text-warning text-[20px]">hourglass_empty</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $pendingAdmissions }}</h3>
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-warning-container text-on-warning-container">Awaiting Action</span>
        </div>
    </div>

    <!-- KPI 3: Accepted -->
    <div class="kpi-card border-l-4 border-success" id="kpi-accepted-admissions">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Accepted</p>
            <span class="material-symbols-outlined text-success text-[20px]">check_circle</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $acceptedAdmissions }}</h3>
            <span class="text-xs text-on-surface-variant">Ready for enrollment</span>
        </div>
    </div>

    <!-- KPI 4: Declined -->
    <div class="kpi-card border-l-4 border-error" id="kpi-declined-admissions">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Declined</p>
            <span class="material-symbols-outlined text-error text-[20px]">cancel</span>
        </div>
        <div class="flex items-baseline gap-2 mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $declinedAdmissions }}</h3>
            <span class="text-xs text-on-surface-variant">Archived applications</span>
        </div>
    </div>
</div>

<!-- Quick Action Section -->
<div class="card p-6 mb-section-gap flex flex-col md:flex-row md:items-center justify-between gap-4 bg-surface-container-low/50">
    <div>
        <h3 class="font-title-medium text-title-medium font-semibold text-on-surface">Admissions & Front Desk Operations</h3>
        <p class="font-body-sm text-body-sm text-on-surface-variant">Process walk-in applications, review online admissions, or register verified students.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admissions.apply') }}" class="btn-primary flex items-center gap-1.5 text-xs py-2 px-4 shadow-level-1">
            <span class="material-symbols-outlined text-[18px]">add</span>
            New Application
        </a>
        <a href="{{ route('admissions.index') }}" class="btn-ghost flex items-center gap-1.5 text-xs py-2 px-4 border border-outline-variant">
            <span class="material-symbols-outlined text-[18px]">view_list</span>
            Review Queue
        </a>
        <a href="{{ route('students.create') }}" class="btn-ghost flex items-center gap-1.5 text-xs py-2 px-4 border border-outline-variant">
            <span class="material-symbols-outlined text-[18px]">person_add</span>
            Register Student
        </a>
    </div>
</div>

<!-- Main Layout: 2 Columns -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- Left: Recent Applications Table (60% / 7 Cols) -->
    <div class="lg:col-span-8 card overflow-hidden flex flex-col">
        <div class="card-header flex justify-between items-center">
            <h3 class="font-title-lg text-title-lg text-on-background font-semibold">Recent Applications</h3>
            <a href="{{ route('admissions.index') }}" class="text-primary font-label-md text-label-md hover:underline flex items-center gap-1">
                View Queue
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
        
        <div class="overflow-x-auto border-t border-outline-variant">
            <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                <thead>
                    <tr class="bg-surface-container text-on-surface border-b border-outline-variant font-semibold">
                        <th class="p-3 pl-4">Date Applied</th>
                        <th class="p-3">Applicant Name</th>
                        <th class="p-3">Target Level</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right pr-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($recentAdmissions as $admission)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-3 pl-4 text-on-surface-variant">{{ $admission->created_at->format('d M, Y') }}</td>
                            <td class="p-3 font-semibold text-on-surface">{{ $admission->first_name }} {{ $admission->last_name }}</td>
                            <td class="p-3 uppercase text-on-surface-variant">{{ $admission->level }}</td>
                            <td class="p-3">
                                @if($admission->status === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-warning-container text-on-warning-container">Pending</span>
                                @elseif($admission->status === 'accepted')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-success-container text-on-success-container">Accepted</span>
                                @elseif($admission->status === 'reviewed')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-container text-on-primary-container">Reviewed</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Declined</span>
                                @endif
                            </td>
                            <td class="p-3 text-right pr-4">
                                <a href="{{ route('admissions.show', $admission) }}" class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1 text-primary hover:bg-primary-container/20 inline-flex items-center ml-auto">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                    Review Files
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-on-surface-variant font-body-md">
                                No admission applications received recently.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Notices (40% / 4 Cols) -->
    <div class="lg:col-span-4 space-y-6">
        <!-- Bulletins/Announcements -->
        <div class="card overflow-hidden flex flex-col">
            <div class="p-4 border-b border-outline-variant bg-surface-container-low/50">
                <h3 class="font-title-lg text-title-lg text-on-background font-semibold">Active Announcements</h3>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-outline-variant">
                    @forelse($announcements as $bulletin)
                        <li class="p-4 hover:bg-surface-container-low transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $bulletin->is_pinned ? 'bg-error-container text-on-error-container' : 'bg-surface-container-high text-on-surface-variant' }}">
                                    {{ $bulletin->is_pinned ? 'Pinned' : 'General' }}
                                </span>
                                <span class="text-xs text-on-surface-variant">
                                    {{ $bulletin->published_at ? $bulletin->published_at->diffForHumans() : $bulletin->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <h4 class="font-body-md text-body-md font-semibold text-on-surface">{{ $bulletin->title }}</h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 line-clamp-2">{{ $bulletin->content }}</p>
                        </li>
                    @empty
                        <li class="p-6 text-center text-on-surface-variant text-sm">
                            <span class="material-symbols-outlined text-outline text-[32px] mb-2 block">campaign</span>
                            No notices posted currently.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="h-8"></div>
@endsection
