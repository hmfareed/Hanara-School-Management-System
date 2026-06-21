@extends('layouts.app')

@section('title', 'Fee Defaulters List')

@section('content')
<div class="space-y-6" x-data="{ creditModalOpen: false, selectedInvoiceId: null, selectedInvoiceNum: '', selectedInvoiceBalance: 0 }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-primary font-medium text-xs mb-1">
                <a href="{{ route('billing.invoices') }}" class="hover:underline">Fees & Invoicing</a>
                <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                <span>Defaulters</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Overdue Fee Defaulters</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Track outstanding invoices that have passed their payment due date.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('billing.invoices') }}" class="btn-ghost !py-2.5 text-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                All Invoices
            </a>
        </div>
    </div>

    <!-- Active Term Banner -->
    <div class="card p-6 bg-[#fef2f2] border border-[#fca5a5] flex items-start gap-4">
        <div class="p-2 bg-[#fee2e2] text-[#b91c1c] rounded-full shrink-0">
            <span class="material-symbols-outlined text-[24px]">gavel</span>
        </div>
        <div>
            <h4 class="font-title-medium text-title-medium font-bold text-[#991b1b]">Outstanding Arrears Warning</h4>
            <p class="font-body-sm text-body-sm text-[#7f1d1d] mt-1">
                The listing below includes all active student accounts containing termly billing invoices that remain unpaid or partially paid after their specified due dates. Ensure payment notices are dispatched.
            </p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card p-4">
        <form method="GET" action="{{ route('billing.defaulters') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Class Filter -->
            <div>
                <label class="form-label text-xs" for="class_id">Class</label>
                <select name="class_id" id="class_id" class="form-input-custom !py-2" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ ucfirst($class->level) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 !py-2 text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">filter_list</span>
                    Filter
                </button>
                <a href="{{ route('billing.defaulters') }}" class="btn-ghost !py-2 text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    Reset
                </a>
            </div>
            <div></div>
        </form>
    </div>

    <!-- Defaulters Table List -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
            <span class="font-label-lg text-label-lg font-semibold text-on-surface">Defaulter Ledger ({{ $defaulters->total() }} total)</span>
        </div>

        @if($defaulters->isEmpty())
            <div class="p-12 text-center text-on-surface-variant">
                <span class="material-symbols-outlined text-[#166534] text-5xl mb-3">check_circle</span>
                <p class="font-body-md font-medium text-on-surface">Excellent! No overdue invoices found.</p>
                <p class="text-xs text-on-surface-variant mt-1">All invoices are either fully paid or within their payment grace periods.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="p-4 w-32">Invoice #</th>
                            <th class="p-4">Student</th>
                            <th class="p-4">Parent / Guardian Contact</th>
                            <th class="p-4">Due Date</th>
                            <th class="p-4">Total Amount</th>
                            <th class="p-4">Outstanding Arrears</th>
                            <th class="p-4 text-right w-80">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach ($defaulters as $invoice)
                            @php
                                $enrollment = $invoice->student->currentClassEnrollment();
                                $guardian = $invoice->student->guardians->first();
                            @endphp
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-semibold font-mono text-xs text-on-surface-variant">{{ $invoice->invoice_number }}</td>
                                <td class="p-4">
                                    <a href="{{ route('students.show', $invoice->student) }}" class="font-medium text-primary hover:underline">
                                        {{ $invoice->student->full_name }}
                                    </a>
                                    @if ($enrollment)
                                        <div class="text-xs mt-0.5">
                                            <span class="px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-medium text-[10px]">
                                                {{ $enrollment->classAcademicYear->schoolClass->name }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if ($guardian)
                                        <div class="font-medium text-on-surface">{{ $guardian->full_name }} ({{ ucfirst($guardian->relationship) }})</div>
                                        <div class="text-xs text-on-surface-variant flex items-center gap-1 mt-0.5">
                                            <span class="material-symbols-outlined text-[14px]">phone</span>
                                            {{ $guardian->phone ?: 'No phone' }}
                                            @if($guardian->email)
                                                <span class="mx-1">|</span>
                                                <span class="material-symbols-outlined text-[14px]">mail</span>
                                                {{ $guardian->email }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-on-surface-variant italic">No guardian linked</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs font-semibold text-error">
                                    {{ $invoice->due_date->format('d M, Y') }}
                                    <span class="block text-[10px] text-outline font-normal">({{ $invoice->due_date->diffForHumans() }})</span>
                                </td>
                                <td class="p-4 font-medium">GH₵{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="p-4 font-semibold text-error">
                                    GH₵{{ number_format($invoice->balance, 2) }}
                                </td>
                                <td class="p-4 text-right flex items-center justify-end gap-2">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-outline-variant bg-surface-container-low">
                {{ $defaulters->appends(request()->query())->links() }}
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
