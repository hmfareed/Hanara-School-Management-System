@extends('layouts.app')

@section('title', 'Staff Directory')

@section('content')
<div x-data="{ 
    selectedStaff: null, 
    showModal: false,
    viewStaff(member) {
        this.selectedStaff = member;
        this.showModal = true;
    }
}">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">
                Staff Directory
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Browse and search all school staff members, class teachers, bursars, and directors.
            </p>
        </div>
    </div>

    @if(auth()->user()->hasRole('Proprietor'))
        <!-- Staff Registration Codes Section -->
        <div class="card p-6 mb-section-gap border border-primary/20 bg-surface-container-low shadow-level-1">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="font-title-lg text-title-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">key</span>
                        Staff Registration PINs
                    </h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                        Generate registration PIN codes for new staff members to create their accounts.
                    </p>
                </div>
                <div>
                    <form method="POST" action="{{ route('staff-codes.generate') }}">
                        @csrf
                        <button type="submit" class="btn-primary flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium">
                            <span class="material-symbols-outlined text-[20px]">add_circle</span>
                            Generate Registration PIN
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="p-3 mb-4 bg-primary-container text-on-primary-container rounded-xl text-body-md">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-3 mb-4 bg-error-container text-on-error-container rounded-xl text-body-md">
                    {{ session('error') }}
                </div>
            @endif

            @if(count($staffCodes) > 0)
                <div class="overflow-x-auto mt-4 border border-outline-variant rounded-xl bg-surface-container-lowest">
                    <table class="w-full text-left border-collapse text-body-md">
                        <thead>
                            <tr class="border-b border-outline-variant bg-surface-container-high/40 text-on-surface-variant font-semibold">
                                <th class="p-3 pl-4">Registration PIN</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Generated At</th>
                                <th class="p-3 text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($staffCodes as $code)
                                <tr class="hover:bg-surface-container-high/20 transition-colors">
                                    <td class="p-3 pl-4 font-mono font-bold text-primary tracking-wider text-lg">
                                        {{ $code->code }}
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-container text-on-primary-container">
                                            Unused / Active
                                        </span>
                                    </td>
                                    <td class="p-3 text-on-surface-variant text-sm">
                                        {{ $code->created_at->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="p-3 text-right pr-4">
                                        <form method="POST" action="{{ route('staff-codes.destroy', $code->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to revoke this registration PIN?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost !text-error hover:bg-error-container/20 !p-2 rounded-lg flex items-center justify-center inline-flex gap-1 text-sm">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Revoke
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-4 text-center border border-dashed border-outline-variant rounded-xl text-on-surface-variant bg-surface-container-lowest">
                    <span class="material-symbols-outlined text-[32px] text-outline mb-1">vpn_key_off</span>
                    <p class="text-sm">No active registration PINs. Click the button above to generate one.</p>
                </div>
            @endif

            <!-- Used PINs History -->
            @if(count($usedStaffCodes) > 0)
                <div class="mt-6">
                    <h4 class="font-title-md text-sm font-bold text-on-surface-variant mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">history</span>
                        PIN History (Used)
                    </h4>
                    <div class="overflow-x-auto border border-outline-variant/60 rounded-xl bg-surface-container-lowest">
                        <table class="w-full text-left border-collapse text-body-md">
                            <thead>
                                <tr class="border-b border-outline-variant bg-surface-container-high/20 text-on-surface-variant font-semibold text-xs">
                                    <th class="p-3 pl-4">PIN Code</th>
                                    <th class="p-3">Used By</th>
                                    <th class="p-3">Used At</th>
                                    <th class="p-3 text-right pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/50">
                                @foreach($usedStaffCodes as $usedCode)
                                    <tr class="hover:bg-surface-container-high/10 transition-colors">
                                        <td class="p-3 pl-4 font-mono font-medium text-on-surface-variant tracking-wider text-sm">
                                            {{ $usedCode->code }}
                                        </td>
                                        <td class="p-3 text-sm">
                                            @if($usedCode->usedBy)
                                                <span class="font-medium text-on-surface">{{ $usedCode->usedBy->name }}</span>
                                            @else
                                                <span class="text-outline">Unknown</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-xs text-on-surface-variant">
                                            {{ $usedCode->updated_at->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="p-3 text-right pr-4">
                                            <form method="POST" action="{{ route('staff-codes.regenerate', $usedCode->id) }}" class="inline-block"
                                                  onsubmit="return confirm('This will replace the old PIN with a new one. Continue?');">
                                                @csrf
                                                <button type="submit" class="btn-ghost !p-2 rounded-lg text-primary hover:bg-primary-container/10 transition-colors inline-flex items-center gap-1 text-xs font-medium">
                                                    <span class="material-symbols-outlined text-[16px]">autorenew</span>
                                                    Regenerate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Filters & Search Bar -->
    <div class="card p-4 mb-section-gap">
        <form method="GET" action="{{ route('staff.index') }}" class="flex flex-col md:flex-row gap-4 items-stretch md:items-center w-full">
            <!-- Search field -->
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input type="text" name="search" id="search-input" value="{{ $search }}"
                       class="w-full pl-10 pr-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                       placeholder="Search by ID, name, or email...">
            </div>

            <!-- Position filter -->
            <div class="w-full md:w-60">
                <select name="position" id="position-filter" onchange="this.form.submit()"
                        class="w-full px-4 py-2.5 bg-surface-container-lowest border border-outline-variant rounded-xl text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="">All Positions</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" {{ $position === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button -->
            @if($search || $position)
                <a href="{{ route('staff.index') }}" class="btn-ghost !py-2.5 text-center flex items-center justify-center gap-1 text-sm rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    Reset
                </a>
            @endif

            <button type="submit" class="btn-primary !py-2.5 px-6 rounded-xl flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">search</span>
                Search
            </button>
        </form>
    </div>

    <!-- Staff List Table -->
    <div class="card overflow-hidden border border-outline-variant">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-body-md">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-high/40 text-on-surface-variant font-semibold">
                        <th class="p-4 pl-6">ID & Name</th>
                        <th class="p-4">Position</th>
                        <th class="p-4">Qualification</th>
                        <th class="p-4">Contact</th>
                        <th class="p-4">Date Joined</th>
                        <th class="p-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($staff as $member)
                        <tr class="hover:bg-surface-container-high/20 transition-colors">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container text-sm font-bold flex items-center justify-center border border-primary/10">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-on-surface text-sm">{{ $member->full_name }}</h4>
                                        <span class="font-mono text-xs text-on-surface-variant font-medium">{{ $member->staff_id_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @php
                                    $badgeClass = match ($member->position) {
                                        'Proprietor' => 'bg-tertiary-container text-on-tertiary-container',
                                        'Head Teacher' => 'bg-error-container text-on-error-container',
                                        'Bursar' => 'bg-primary-container text-on-primary-container',
                                        'Front Desk Officer' => 'bg-secondary-container text-on-secondary-container',
                                        default => 'bg-surface-container-high text-on-surface-variant',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ $member->position }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-on-surface-variant">
                                {{ $member->qualification ?? 'Not listed' }}
                            </td>
                            <td class="p-4 text-xs text-on-surface-variant space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">mail</span>
                                    <span>{{ $member->email }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[14px]">call</span>
                                    <span>{{ $member->phone }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-on-surface-variant">
                                {{ $member->date_joined ? $member->date_joined->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Eye Button -->
                                    <button type="button" @click="viewStaff({{ json_encode($member) }})"
                                            class="btn-ghost !p-2 !py-2 rounded-lg text-primary hover:bg-primary-container/10 transition-colors inline-flex items-center justify-center"
                                            title="View Full Profile">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                    <a href="mailto:{{ $member->email }}" class="btn-ghost !p-2 !py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors inline-flex items-center justify-center text-sm" title="Send Email">
                                        <span class="material-symbols-outlined text-[20px]">mail</span>
                                    </a>
                                    <a href="tel:{{ $member->phone }}" class="btn-ghost !p-2 !py-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors inline-flex items-center justify-center text-sm" title="Call Phone">
                                        <span class="material-symbols-outlined text-[20px]">call</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[48px] text-outline">group_off</span>
                                    <p class="text-sm">No staff members found matching your filters.</p>
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
        {{ $staff->links() }}
    </div>

    <!-- Staff Profile Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak>
        <div class="bg-surface rounded-2xl border border-outline-variant max-w-lg w-full overflow-hidden shadow-level-3" @click.away="showModal = false">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary to-primary/80 p-6 text-on-primary relative">
                <button @click="showModal = false" class="absolute top-4 right-4 text-white/80 hover:text-white rounded-full p-1 bg-white/10 hover:bg-white/20 transition-all">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold backdrop-blur-sm" x-text="selectedStaff ? (selectedStaff.first_name.substring(0,1) + selectedStaff.last_name.substring(0,1)).toUpperCase() : ''">
                    </div>
                    <div>
                        <h3 class="font-headline-sm text-lg font-bold" x-text="selectedStaff ? (selectedStaff.first_name + ' ' + (selectedStaff.other_names || '') + ' ' + selectedStaff.last_name).replace(/\s+/g, ' ') : ''"></h3>
                        <p class="font-mono text-xs opacity-90" x-text="selectedStaff ? selectedStaff.staff_id_number : ''"></p>
                    </div>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-on-surface-variant block uppercase font-bold tracking-wider">Position / Role</span>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-secondary-container text-on-secondary-container mt-1" x-text="selectedStaff ? selectedStaff.position : ''"></span>
                    </div>
                    <div>
                        <span class="text-xs text-on-surface-variant block uppercase font-bold tracking-wider">Status</span>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-container text-on-success-container mt-1" x-text="selectedStaff ? selectedStaff.status.toUpperCase() : ''"></span>
                    </div>
                </div>

                <div class="border-t border-outline-variant/60 pt-4 space-y-3">
                    <h4 class="font-title-md text-sm font-bold text-primary">Personal Details</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-on-surface-variant block">Gender:</span>
                            <span class="font-medium text-on-surface" x-text="selectedStaff ? (selectedStaff.gender ? (selectedStaff.gender.substring(0,1).toUpperCase() + selectedStaff.gender.substring(1)) : 'N/A') : ''"></span>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block">Date of Birth:</span>
                            <span class="font-medium text-on-surface" x-text="selectedStaff && selectedStaff.date_of_birth ? new Date(selectedStaff.date_of_birth).toLocaleDateString('en-GB', {day: 'numeric', month: 'short', year: 'numeric'}) : 'N/A'"></span>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block">Qualification:</span>
                            <span class="font-medium text-on-surface" x-text="selectedStaff ? (selectedStaff.qualification || 'None') : ''"></span>
                        </div>
                        <div>
                            <span class="text-on-surface-variant block">Date Joined:</span>
                            <span class="font-medium text-on-surface" x-text="selectedStaff && selectedStaff.date_joined ? new Date(selectedStaff.date_joined).toLocaleDateString('en-GB', {day: 'numeric', month: 'short', year: 'numeric'}) : 'N/A'"></span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-outline-variant/60 pt-4 space-y-3">
                    <h4 class="font-title-md text-sm font-bold text-primary">Contact Details</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                            <a class="text-primary hover:underline" :href="selectedStaff ? 'mailto:' + selectedStaff.email : '#'" x-text="selectedStaff ? selectedStaff.email : ''"></a>
                        </div>
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px]">call</span>
                            <a class="text-primary hover:underline" :href="selectedStaff ? 'tel:' + selectedStaff.phone : '#'" x-text="selectedStaff ? selectedStaff.phone : ''"></a>
                        </div>
                        <div class="flex items-start gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[18px] mt-0.5">location_on</span>
                            <span class="text-on-surface font-medium" x-text="selectedStaff ? (selectedStaff.address || 'No residential address entered') : ''"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-surface-container-low px-6 py-4 flex justify-end gap-2 border-t border-outline-variant/60">
                <button @click="showModal = false" class="btn-ghost !py-2 px-4 rounded-xl text-sm">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="h-8"></div>
@endsection
