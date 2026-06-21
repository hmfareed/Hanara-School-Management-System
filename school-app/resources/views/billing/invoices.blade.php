@extends('layouts.app')

@section('title', 'Fees & Invoicing')

@section('content')
<div class="space-y-6" x-data="{ creditModalOpen: false, selectedInvoiceId: null, selectedInvoiceNum: '', selectedInvoiceBalance: 0 }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Fees & Invoicing</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Manage termly school fees, track billing invoices, and log parent payments.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('billing.defaulters') }}" class="btn-ghost !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">warning</span>
                Defaulters List
            </a>
            <a href="{{ route('billing.record-payment.form') }}" class="btn-primary !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add_card</span>
                Record Payment
            </a>
        </div>
    </div>

    <!-- Active Term Banner & Bulk Generation Trigger -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Term Details -->
        <div class="card p-6 md:col-span-1 bg-surface-container-low border border-outline-variant flex flex-col justify-between">
            <div>
                <h3 class="font-title-medium text-title-medium font-semibold text-on-surface flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>
                    Active Term Billing
                </h3>
                @if ($currentTerm)
                    <div class="space-y-2 font-body-sm text-body-sm">
                        <p class="text-on-surface font-medium">Term: <span class="font-semibold text-primary">{{ $currentTerm->name }}</span></p>
                        <p class="text-on-surface font-medium">Academic Year: <span class="font-semibold text-on-surface-variant">{{ $currentTerm->academicYear->name }}</span></p>
                        <p class="text-xs text-on-surface-variant">Billing Due: {{ $currentTerm->end_date ? $currentTerm->end_date->format('d M, Y') : 'N/A' }}</p>
                    </div>
                @else
                    <p class="text-xs text-error font-medium">No active academic term configured. Please configure term details in Settings first.</p>
                @endif
            </div>
        </div>

        <!-- Bulk Invoice Generation CTA -->
        <div class="card p-6 md:col-span-2 border-l-4 border-primary bg-primary-container/10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="max-w-md">
                <h4 class="font-title-medium text-title-medium font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    Bulk Invoice Generator
                </h4>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                    Auto-generate custom termly invoices for all currently enrolled active students. The generator maps fee structures by class level and bypasses students already billed this term.
                </p>
            </div>
            <form action="{{ route('billing.invoices.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary !py-2.5 px-6 text-xs flex items-center gap-1.5 whitespace-nowrap" {{ !$currentTerm ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                    Generate Invoices
                </button>
            </form>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-4">
        <form method="GET" action="{{ route('billing.invoices') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search -->
            <div>
                <label class="form-label text-xs" for="search">Search</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input type="text" name="search" id="search" value="{{ $search }}" 
                           class="form-input-custom !pl-10 !py-2" 
                           placeholder="Invoice # or student ID...">
                </div>
            </div>

            <!-- Class Filter -->
            <div>
                <label class="form-label text-xs" for="class_id">Class</label>
                <select name="class_id" id="class_id" class="form-input-custom !py-2">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ ucfirst($class->level) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="form-label text-xs" for="status">Payment Status</label>
                <select name="status" id="status" class="form-input-custom !py-2">
                    <option value="">All Statuses</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ $status === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 !py-2 text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Filter
                </button>
                <a href="{{ route('billing.invoices') }}" class="btn-ghost !py-2 text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table List -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-outline-variant bg-surface-container-low">
            <span class="font-label-lg text-label-lg font-semibold text-on-surface">Invoice Registry ({{ $invoices->total() }} total)</span>
        </div>

        @if($invoices->isEmpty())
            <div class="p-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-outline text-5xl mb-3">receipt_long</span>
                <p class="font-body-md">No invoices found matching your criteria.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="p-4 w-32">Invoice #</th>
                            <th class="p-4">Student</th>
                            <th class="p-4">Class</th>
                            <th class="p-4">Term</th>
                            <th class="p-4">Total Amount</th>
                            <th class="p-4">Paid</th>
                            <th class="p-4">Outstanding</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right w-80">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($invoices as $invoice)
                            @php
                                $enrollment = $invoice->student->currentClassEnrollment();
                            @endphp
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-semibold font-mono text-xs text-on-surface-variant">{{ $invoice->invoice_number }}</td>
                                <td class="p-4">
                                    <a href="{{ route('students.show', $invoice->student) }}" class="font-medium text-primary hover:underline">
                                        {{ $invoice->student->full_name }}
                                    </a>
                                    <div class="text-xs text-on-surface-variant font-mono">{{ $invoice->student->student_id_number }}</div>
                                </td>
                                <td class="p-4">
                                    @if ($enrollment)
                                        <span class="text-xs px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-medium">
                                            {{ $enrollment->classAcademicYear->schoolClass->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs">Term {{ $invoice->term->number }} ({{ $invoice->term->academicYear->name }})</td>
                                <td class="p-4 font-semibold">GH₵{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="p-4 text-success">GH₵{{ number_format($invoice->amount_paid, 2) }}</td>
                                <td class="p-4 font-semibold {{ $invoice->balance > 0 ? 'text-error' : 'text-on-surface-variant' }}">
                                    GH₵{{ number_format($invoice->balance, 2) }}
                                </td>
                                <td class="p-4">
                                    @if($invoice->status === 'paid')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-success-container text-on-success-container text-xs font-semibold">Paid</span>
                                    @elseif($invoice->status === 'partial')
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-warning-container text-on-warning-container text-xs font-semibold">Partial</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-error-container text-on-error-container text-xs font-semibold">Unpaid</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right flex items-center justify-end gap-2">
                                    @if($invoice->balance > 0)
                                        <form action="{{ route('billing.pay.initialize', $invoice) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-ghost !py-1 !px-2.5 text-xs text-primary hover:bg-primary-container/30 inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px]">credit_card</span>
                                                Pay Online
                                            </button>
                                        </form>

                                        @if(auth()->user()->hasAnyRole(['Accounts', 'Proprietor']))
                                            <button type="button" 
                                                    @click="creditModalOpen = true; selectedInvoiceId = {{ $invoice->id }}; selectedInvoiceNum = '{{ $invoice->invoice_number }}'; selectedInvoiceBalance = {{ $invoice->balance }}"
                                                    class="btn-ghost !py-1 !px-2.5 text-xs text-[#b45309] hover:bg-[#fef3c7] inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px]">redeem</span>
                                                Credit
                                            </button>
                                            
                                            <a href="{{ route('billing.record-payment.form', ['invoice_id' => $invoice->id]) }}" class="btn-ghost !py-1 !px-2.5 text-xs text-success hover:bg-success-container/30 inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[16px]">add_card</span>
                                                Record Pay
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">Settled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-outline-variant bg-surface-container-low">
                {{ $invoices->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Credit Note Modal -->
    <div x-show="creditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak>
        <div class="w-full max-w-md bg-surface border border-outline-variant rounded-xl shadow-level-3 overflow-hidden" @click.away="creditModalOpen = false">
            <div class="p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
                <div>
                    <h3 class="font-title-lg text-on-surface">Apply Fee Credit</h3>
                    <p class="font-body-sm text-on-surface-variant mt-1">Invoice: <span class="font-mono font-semibold" x-text="selectedInvoiceNum"></span></p>
                </div>
                <button type="button" @click="creditModalOpen = false" class="p-1 rounded-full text-outline hover:bg-surface-container-high">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form :action="'/billing/invoices/' + selectedInvoiceId + '/credit'" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="form-label text-xs">Outstanding Balance</label>
                    <div class="font-semibold text-lg text-on-surface" x-text="'GH₵' + selectedInvoiceBalance.toFixed(2)"></div>
                </div>

                <div>
                    <label class="form-label text-xs" for="credit_amount">Credit Note Amount (GH₵)</label>
                    <input type="number" step="0.01" min="0.01" :max="selectedInvoiceBalance" name="amount" id="credit_amount" required 
                           class="form-input-custom w-full" placeholder="e.g. 150.00">
                </div>

                <div>
                    <label class="form-label text-xs" for="credit_reason">Reason for Credit / Adjustment</label>
                    <textarea name="reason" id="credit_reason" required rows="3"
                              class="form-input-custom w-full" placeholder="e.g. Scholarship waiver, PTA discount..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="creditModalOpen = false" class="btn-ghost text-xs">Cancel</button>
                    <button type="submit" class="btn-primary text-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">check</span>
                        Apply Credit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
