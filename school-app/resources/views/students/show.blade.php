@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'bio', transferModal: false }">
    <!-- Header/Back Nav -->
    <div class="flex items-center gap-4">
        <a href="{{ route('students.index') }}" class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold">{{ $student->full_name }}</h2>
                @if($student->status === 'active')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-xs font-medium">Active</span>
                @elseif($student->status === 'graduated')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-container text-on-primary-container text-xs font-medium">Graduated</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-xs font-medium">{{ ucfirst($student->status) }}</span>
                @endif
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant">ID: <span class="font-mono text-xs font-semibold">{{ $student->student_id_number }}</span> • Class: {{ $currentEnrollment ? $currentEnrollment->classAcademicYear->schoolClass->name : 'Unassigned' }}</p>
        </div>
    </div>

    <!-- Success/Error Alerts -->
    @if (session('success'))
        <div class="card border-l-4 border-success bg-success-container/10 p-4">
            <div class="flex items-center gap-2 text-success font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="card border-l-4 border-error bg-error-container/10 p-4 space-y-2">
            <div class="flex items-center gap-2 text-error font-semibold font-title-medium text-title-medium">
                <span class="material-symbols-outlined text-[22px]">error_outline</span>
                Error
            </div>
            <ul class="list-disc list-inside space-y-1 font-body-sm text-body-sm text-error">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Layout: Left (Info Card) & Right (Details Tabs) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Left Side Profile Summary Card -->
        <div class="card p-6 space-y-6 text-center">
            <div class="flex flex-col items-center">
                @if($student->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" class="w-32 h-32 rounded-full object-cover border-4 border-primary/20 shadow-level-1">
                @else
                    <div class="w-32 h-32 rounded-full bg-primary-container text-on-primary-container text-4xl font-bold flex items-center justify-center border-4 border-primary/10 shadow-level-1">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                @endif
                <h3 class="font-title-lg text-title-lg font-semibold text-on-surface mt-4">{{ $student->full_name }}</h3>
                <p class="text-xs font-mono text-on-surface-variant mt-1">{{ $student->student_id_number }}</p>
            </div>

            <hr class="border-outline-variant">

            <!-- Small stats block -->
            <div class="grid grid-cols-2 gap-4 text-left">
                <div class="p-3 bg-surface-container-low rounded-xl">
                    <p class="text-xs text-on-surface-variant font-label-md">Attendance</p>
                    <p class="font-title-medium text-title-medium font-semibold text-primary mt-1">{{ $attendanceRate }}%</p>
                </div>
                <div class="p-3 bg-surface-container-low rounded-xl">
                    <p class="text-xs text-on-surface-variant font-label-md">Pending Fees</p>
                    @php
                        $outstanding = $invoices->sum('balance');
                    @endphp
                    <p class="font-title-medium text-title-medium font-semibold text-error mt-1">GH₵{{ number_format($outstanding, 2) }}</p>
                </div>
            </div>

            <a href="{{ route('students.id-card', $student) }}" class="btn-ghost w-full justify-center flex items-center gap-1.5 text-xs !py-2 border border-outline-variant hover:bg-surface-container-high transition-colors">
                <span class="material-symbols-outlined text-[18px]">badge</span>
                Download ID Card
            </a>

            <hr class="border-outline-variant">

            <!-- Edit & Transfer Actions -->
            <div class="space-y-2">
                <a href="{{ route('students.edit', $student) }}" class="btn-primary w-full justify-center flex items-center gap-1.5 text-xs !py-2">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit Profile
                </a>
                <button type="button" @click="transferModal = true" class="btn-ghost w-full justify-center flex items-center gap-1.5 text-xs !py-2 border border-outline-variant hover:bg-secondary-container hover:text-on-secondary-container transition-colors">
                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span>
                    Transfer Class
                </button>
            </div>
        </div>

        <!-- Right Side Tabbed Panel -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Tabs Row -->
            <div class="border-b border-outline-variant flex gap-1 overflow-x-auto">
                <button @click="activeTab = 'bio'"
                        :class="activeTab === 'bio' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-on-surface-variant border-transparent'"
                        class="px-4 py-2.5 font-label-md text-label-md transition-all">
                    General Bio
                </button>
                <button @click="activeTab = 'guardians'"
                        :class="activeTab === 'guardians' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-on-surface-variant border-transparent'"
                        class="px-4 py-2.5 font-label-md text-label-md transition-all">
                    Guardians & Siblings
                </button>
                <button @click="activeTab = 'attendance'"
                        :class="activeTab === 'attendance' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-on-surface-variant border-transparent'"
                        class="px-4 py-2.5 font-label-md text-label-md transition-all">
                    Attendance History
                </button>
                <button @click="activeTab = 'billing'"
                        :class="activeTab === 'billing' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-on-surface-variant border-transparent'"
                        class="px-4 py-2.5 font-label-md text-label-md transition-all">
                    Billing & Finance
                </button>
            </div>

            <!-- Tab Content Panels -->
            
            <!-- 1. Bio Tab -->
            <div x-show="activeTab === 'bio'" class="space-y-6">
                <div class="card p-6 space-y-6">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Biographical Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Full Name</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->full_name }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Date of Birth</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->date_of_birth->format('F d, Y') }} ({{ $student->date_of_birth->age }} years old)</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Gender</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium capitalize">{{ $student->gender }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Nationality</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->nationality }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Religion</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->religion ?? 'Not specified' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Blood Group</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->blood_group ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Admission Date</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->admission_date->format('F d, Y') }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs text-on-surface-variant font-label-md">Physical Address</span>
                            <span class="font-body-md text-body-md text-on-surface font-medium">{{ $student->address ?? 'No address registered' }}</span>
                        </div>
                    </div>
                </div>

                <div class="card p-6 space-y-4">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Medical & Health Notes</h4>
                    <p class="font-body-sm text-body-sm text-on-surface-variant bg-surface-container-low p-4 rounded-xl">
                        {{ $student->medical_notes ?? 'No critical medical information or allergies registered.' }}
                    </p>
                </div>

                <div class="card p-6 space-y-4">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Academic History</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                            <thead>
                                <tr class="bg-surface-container text-on-surface border-b border-outline-variant font-semibold">
                                    <th class="p-3">Academic Year</th>
                                    <th class="p-3">Class</th>
                                    <th class="p-3">Date Enrolled</th>
                                    <th class="p-3">Status</th>
                                    @if(auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']))
                                        <th class="p-3 text-right">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant">
                                @foreach($enrollmentHistory as $enroll)
                                    <tr>
                                        <td class="p-3">{{ $enroll->classAcademicYear->academicYear->name }}</td>
                                        <td class="p-3 font-semibold">{{ $enroll->classAcademicYear->schoolClass->name }}</td>
                                        <td class="p-3">{{ $enroll->enrolled_at->format('d M, Y') }}</td>
                                        <td class="p-3"><span class="capitalize">{{ $enroll->status }}</span></td>
                                        @if(auth()->user()->hasAnyRole(['Proprietor', 'HeadTeacher']))
                                            <td class="p-3 text-right">
                                                @if($loop->first && $enrollmentHistory->count() > 1)
                                                    <form method="POST" action="{{ route('students.revert-transfer', $student) }}" class="inline" onsubmit="return confirm('Are you sure you want to revert the latest transfer/promotion? This will delete the current enrollment and return the student to the previous class.');">
                                                        @csrf
                                                        <button type="submit" class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1 text-error hover:bg-error-container/30 inline-flex items-center ml-auto">
                                                            <span class="material-symbols-outlined text-[16px]">undo</span>
                                                            Revert Transfer
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-on-surface-variant">-</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. Guardians & Siblings Tab -->
            <div x-show="activeTab === 'guardians'" class="space-y-6">
                <!-- Guardians list -->
                <div class="card p-6 space-y-4">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Primary Guardians & Emergency Contacts</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($student->guardians as $guardian)
                            <div class="border border-outline-variant rounded-2xl p-4 bg-surface-container-lowest flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-primary-container text-on-primary-container text-xs font-semibold">
                                            {{ $guardian->relationship }}
                                        </span>
                                        @if($guardian->pivot->is_primary)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-success-container text-on-success-container text-xs font-semibold">
                                                Primary SMS Contact
                                            </span>
                                        @endif
                                    </div>
                                    <h5 class="font-title-medium text-title-medium font-bold text-on-surface">{{ $guardian->first_name }} {{ $guardian->last_name }}</h5>
                                    
                                    <div class="space-y-1.5 font-body-sm text-body-sm text-on-surface-variant mt-3">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px]">call</span>
                                            <span>{{ $guardian->phone }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px]">mail</span>
                                            <span>{{ $guardian->email ?? 'No email' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[16px]">work</span>
                                            <span>{{ $guardian->occupation ?? 'Not specified' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if($guardian->address)
                                    <div class="mt-4 pt-3 border-t border-outline-variant text-xs text-on-surface-variant">
                                        <strong>Address:</strong> {{ $guardian->address }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Siblings list -->
                <div class="card p-6 space-y-4">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Siblings Enrolled</h4>
                    @if($siblings->isEmpty())
                        <div class="text-center p-6 bg-surface-container-low rounded-2xl text-on-surface-variant font-body-sm">
                            <span class="material-symbols-outlined text-[36px] text-outline mb-2">family_history</span>
                            <p>No registered siblings found in the system for this student.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($siblings as $sibling)
                                @php $sEnroll = $sibling->currentClassEnrollment(); @endphp
                                <div class="border border-outline-variant rounded-2xl p-4 bg-surface-container-lowest flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-secondary-container text-on-secondary-container font-bold flex items-center justify-center">
                                            {{ strtoupper(substr($sibling->first_name, 0, 1) . substr($sibling->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h5 class="font-body-md text-body-md font-semibold text-on-surface">{{ $sibling->full_name }}</h5>
                                            <p class="text-xs text-on-surface-variant font-mono">
                                                ID: {{ $sibling->student_id_number }} • Class: {{ $sEnroll ? $sEnroll->classAcademicYear->schoolClass->name : 'Unassigned' }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('students.show', $sibling) }}" class="btn-ghost !py-1 !px-2.5 text-xs flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        View Profile
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Attendance Tab -->
            <div x-show="activeTab === 'attendance'" class="space-y-6">
                <!-- Stat Cards -->
                <div class="grid grid-cols-4 gap-4">
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Attendance Rate</h5>
                        <p class="text-3xl font-bold text-primary mt-1">{{ $attendanceRate }}%</p>
                    </div>
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Present Days</h5>
                        <p class="text-3xl font-bold text-success mt-1">{{ $presentDays }}</p>
                    </div>
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Late Days</h5>
                        <p class="text-3xl font-bold text-warning mt-1">{{ $lateDays }}</p>
                    </div>
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Absent Days</h5>
                        <p class="text-3xl font-bold text-error mt-1">{{ $absentDays }}</p>
                    </div>
                </div>

                <!-- Attendance Log -->
                <div class="card p-6 space-y-4">
                    <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Attendance Log</h4>
                    @if($attendanceRecords->isEmpty())
                        <div class="text-center p-12 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline mb-3">calendar_today</span>
                            <p>No daily attendance logs found for this student.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                                <thead>
                                    <tr class="bg-surface-container text-on-surface border-b border-outline-variant font-semibold">
                                        <th class="p-3">Date</th>
                                        <th class="p-3">Class</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant">
                                    @foreach($attendanceRecords as $record)
                                        <tr>
                                            <td class="p-3">{{ $record->date->format('l, d M Y') }}</td>
                                            <td class="p-3">{{ $record->classAcademicYear->schoolClass->name }}</td>
                                            <td class="p-3">
                                                @if($record->status === 'present')
                                                    <span class="px-2 py-0.5 rounded bg-success-container text-on-success-container text-xs font-semibold">Present</span>
                                                @elseif($record->status === 'late')
                                                    <span class="px-2 py-0.5 rounded bg-warning-container text-on-warning-container text-xs font-semibold">Late</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-error-container text-on-error-container text-xs font-semibold">Absent</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-on-surface-variant">{{ $record->remarks ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $attendanceRecords->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. Billing Tab -->
            <div x-show="activeTab === 'billing'" class="space-y-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Total Billed</h5>
                        <p class="text-2xl font-bold text-on-surface mt-1">GH₵{{ number_format($invoices->sum('total_amount'), 2) }}</p>
                    </div>
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Total Paid</h5>
                        <p class="text-2xl font-bold text-success mt-1">GH₵{{ number_format($invoices->sum('amount_paid'), 2) }}</p>
                    </div>
                    <div class="card p-4 text-center">
                        <h5 class="font-label-md text-label-md text-on-surface-variant">Total Outstanding</h5>
                        <p class="text-2xl font-bold text-error mt-1">GH₵{{ number_format($outstanding, 2) }}</p>
                    </div>
                </div>

                <!-- Invoice History -->
                <div class="card p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="font-title-medium text-title-medium font-semibold text-on-surface">Invoices & Billing History</h4>
                        @can('create', App\Models\Invoice::class)
                        <a href="{{ route('billing.invoices') }}" class="btn-ghost !py-1 text-xs">Manage Billing</a>
                        @endcan
                    </div>
                    @if($invoices->isEmpty())
                        <div class="text-center p-12 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[48px] text-outline mb-3">receipt_long</span>
                            <p>No invoices have been generated for this student yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                                <thead>
                                    <tr class="bg-surface-container text-on-surface border-b border-outline-variant font-semibold">
                                        <th class="p-3">Invoice Number</th>
                                        <th class="p-3">Term</th>
                                        <th class="p-3">Billed Date</th>
                                        <th class="p-3">Total Amount</th>
                                        <th class="p-3">Paid</th>
                                        <th class="p-3">Balance</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-outline-variant">
                                    @foreach($invoices as $invoice)
                                        @php
                                            $paid = $invoice->amount_paid;
                                            $balance = $invoice->balance;
                                        @endphp
                                        <tr>
                                            <td class="p-3 font-semibold font-mono text-xs">{{ $invoice->invoice_number }}</td>
                                            <td class="p-3">Term {{ $invoice->term->number }} ({{ $invoice->term->academicYear->name }})</td>
                                            <td class="p-3">{{ $invoice->created_at->format('d M, Y') }}</td>
                                            <td class="p-3">GH₵{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="p-3 text-success">GH₵{{ number_format($paid, 2) }}</td>
                                            <td class="p-3 font-semibold {{ $balance > 0 ? 'text-error' : 'text-on-surface-variant' }}">
                                                GH₵{{ number_format($balance, 2) }}
                                            </td>
                                            <td class="p-3">
                                                @if($invoice->status === 'paid')
                                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-xs font-medium">Paid</span>
                                                @elseif($invoice->status === 'partial')
                                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-xs font-medium">Partial</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-xs font-medium">Unpaid</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-right">
                                                <div class="flex justify-end gap-2">
                                                    @if($balance > 0)
                                                        <a href="{{ route('billing.record-payment.form', ['invoice_id' => $invoice->id]) }}" class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1 text-success hover:bg-success-container/30">
                                                            <span class="material-symbols-outlined text-[14px]">add_card</span>
                                                            Pay
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Transfer Class Modal -->
    <div x-show="transferModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="transferModal = false"></div>

        <!-- Modal Content -->
        <div class="card p-6 w-full max-w-md z-10 space-y-5"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-data="{ 
                 selectedYearId: '{{ $academicYears->where('is_current', true)->first()?->id ?? ($academicYears->first()?->id ?? '') }}',
                 selectedTermId: '{{ \App\Models\Term::where('is_current', true)->first()?->id ?? '' }}',
                 years: [
                     @foreach($academicYears as $year)
                         {
                             id: '{{ $year->id }}',
                             name: '{{ $year->name }}',
                             is_current: {{ $year->is_current ? 'true' : 'false' }},
                             terms: [
                                 @foreach($year->terms as $term)
                                     {
                                         id: '{{ $term->id }}',
                                         name: '{{ $term->name }}',
                                         is_current: {{ $term->is_current ? 'true' : 'false' }}
                                     },
                                 @endforeach
                             ]
                         },
                     @endforeach
                 ],
                 get filteredTerms() {
                     let yr = this.years.find(y => y.id == this.selectedYearId);
                     return yr ? yr.terms : [];
                 },
                 onYearChange() {
                     let terms = this.filteredTerms;
                     let currentTerm = terms.find(t => t.is_current);
                     this.selectedTermId = currentTerm ? currentTerm.id : (terms[0] ? terms[0].id : '');
                 }
             }">

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-secondary-container text-on-secondary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-[28px]">swap_horiz</span>
                </div>
                <div>
                    <h3 class="font-title-lg text-title-lg font-semibold text-on-surface">Transfer Student</h3>
                    <p class="text-xs text-on-surface-variant">Move {{ $student->full_name }} to a different class</p>
                </div>
            </div>

            <div class="p-3 bg-surface-container-low rounded-xl font-body-sm text-body-sm text-on-surface-variant">
                <p>Current class: <strong class="text-primary">{{ $currentEnrollment ? $currentEnrollment->classAcademicYear->schoolClass->name : 'Unassigned' }}</strong></p>
            </div>

            <form method="POST" action="{{ route('students.transfer', $student) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label text-xs font-semibold" for="target_class_id">Transfer to Class <span class="text-error">*</span></label>
                    <select name="target_class_id" id="target_class_id" required class="form-input-custom !py-2.5">
                        <option value="">Select Target Class</option>
                        @foreach ($classes as $class)
                            @if(!$currentEnrollment || $class->id !== $currentEnrollment->classAcademicYear->schoolClass->id)
                                <option value="{{ $class->id }}">{{ $class->name }} ({{ ucfirst($class->level) }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label text-xs font-semibold" for="academic_year_id">Academic Year <span class="text-error">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" required class="form-input-custom !py-2.5" 
                            x-model="selectedYearId" @change="onYearChange()">
                        <template x-for="year in years" :key="year.id">
                            <option :value="year.id" x-text="year.name + (year.is_current ? ' (Active)' : '')"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="form-label text-xs font-semibold" for="term_id">Academic Term <span class="text-error">*</span></label>
                    <select name="term_id" id="term_id" required class="form-input-custom !py-2.5" 
                            x-model="selectedTermId">
                        <template x-for="term in filteredTerms" :key="term.id">
                            <option :value="term.id" x-text="term.name + (term.is_current ? ' (Active)' : '')"></option>
                        </template>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="transferModal = false" class="btn-ghost !py-2 px-4 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary !py-2 px-6 text-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">swap_horiz</span>
                        Transfer Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
