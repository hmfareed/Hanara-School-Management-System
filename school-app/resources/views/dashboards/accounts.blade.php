@extends('layouts.app')

@section('title', 'Accounts Dashboard')

@section('content')
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background" id="page-title">Accounts Dashboard</h2>
        @if(auth()->user()->userable && auth()->user()->userable->personal_code)
            <div class="mt-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-container text-on-primary-container text-xs font-semibold">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    Staff Personal Code: {{ auth()->user()->userable->personal_code }}
                </span>
            </div>
        @endif
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Fee collection logs, outstanding invoices, and transaction ledger details.</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('billing.record-payment.form') }}" class="btn-primary flex items-center gap-1.5 text-xs !py-2 px-4">
            <span class="material-symbols-outlined text-[18px]">add_card</span>
            Record Payment
        </a>
    </div>
</div>

<!-- Financial Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-section-gap" id="kpi-cards">
    <!-- Total Invoiced -->
    <div class="kpi-card" id="kpi-total-invoiced">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Invoiced</p>
            <span class="material-symbols-outlined text-outline text-[20px]">receipt_long</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">GH₵{{ number_format($totalInvoiced, 2) }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">For active billing term</p>
        </div>
    </div>

    <!-- Total Collected -->
    <div class="kpi-card" id="kpi-total-collected">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Total Collected</p>
            <span class="material-symbols-outlined text-outline text-[20px]">payments</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-success">GH₵{{ number_format($totalCollected, 2) }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Reconciled payments</p>
        </div>
    </div>

    <!-- Outstanding -->
    <div class="kpi-card" id="kpi-outstanding">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Arrears Outstanding</p>
            <span class="material-symbols-outlined text-outline text-[20px]">assignment_late</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-error">GH₵{{ number_format($outstanding, 2) }}</h3>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Pending student balances</p>
        </div>
    </div>

    <!-- Collection Rate -->
    <div class="kpi-card" id="kpi-collection-rate">
        <div class="flex justify-between items-start mb-2">
            <p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Collection Rate</p>
            <span class="material-symbols-outlined text-outline text-[20px]">percent</span>
        </div>
        <div class="mt-2">
            <h3 class="font-display-lg text-display-lg text-on-background">{{ $collectionRate }}%</h3>
            <div class="w-full bg-surface-container-high h-2 rounded-full mt-3 overflow-hidden">
                <div class="bg-primary h-full rounded-full" style="width: {{ $collectionRate }}%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Middle Section: Payment Methods & Quick Links -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-section-gap">
    <!-- Payment Methods Breakdown -->
    <div class="card p-6 space-y-4">
        <h3 class="font-title-lg text-on-surface border-b border-outline-variant pb-2">Channels Breakdown</h3>
        <div class="space-y-3 font-body-sm text-body-sm">
            <div class="flex justify-between items-center">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                    Mobile Money
                </span>
                <span class="font-semibold text-on-surface">GH₵{{ number_format($momoCollected, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#166534]"></span>
                    Cash Payments
                </span>
                <span class="font-semibold text-on-surface">GH₵{{ number_format($cashCollected, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span>
                    Card/Online
                </span>
                <span class="font-semibold text-on-surface">GH₵{{ number_format($cardCollected, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#8b5cf6]"></span>
                    Bank Transfer
                </span>
                <span class="font-semibold text-on-surface">GH₵{{ number_format($bankCollected, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Defaulters Preview (3 cols) -->
    <div class="lg:col-span-3 card overflow-hidden flex flex-col">
        <div class="p-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
            <h3 class="font-title-lg text-on-surface">Top Outstanding Arrears</h3>
            <a href="{{ route('billing.defaulters') }}" class="text-primary font-label-md text-label-md hover:underline">Full Defaulters List</a>
        </div>
        <div class="flex-1 overflow-y-auto">
            @if($defaulters->isEmpty())
                <p class="p-8 text-center font-body-md text-on-surface-variant">No overdue arrears found.</p>
            @else
                <table class="w-full text-left border-collapse font-body-sm text-body-sm">
                    <thead>
                        <tr class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant font-label-md text-label-md">
                            <th class="p-4">Student</th>
                            <th class="p-4">Due Date</th>
                            <th class="p-4">Balance</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @foreach($defaulters as $invoice)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-semibold text-on-surface">{{ $invoice->student->full_name }}</td>
                                <td class="p-4 text-xs">{{ $invoice->due_date->format('d M, Y') }}</td>
                                <td class="p-4 font-semibold text-error">GH₵{{ number_format($invoice->balance, 2) }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('billing.record-payment.form', ['invoice_id' => $invoice->id]) }}" class="text-primary hover:underline">Record Pay</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Recent Collections Table -->
<div class="card overflow-hidden">
    <div class="p-4 border-b border-outline-variant bg-surface-container-low">
        <h3 class="font-title-lg text-on-surface">Recent Received Payments</h3>
    </div>
    <div class="overflow-x-auto">
        @if($recentPayments->isEmpty())
            <p class="p-8 text-center font-body-md text-on-surface-variant">No recent payments logged.</p>
        @else
            <table class="w-full border-collapse text-left font-body-sm text-body-sm">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-md text-label-md border-b border-outline-variant">
                        <th class="p-4">Receipt #</th>
                        <th class="p-4">Student</th>
                        <th class="p-4">Amount</th>
                        <th class="p-4">Payment Method</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($recentPayments as $payment)
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-4 font-mono font-semibold text-xs text-on-surface-variant">{{ $payment->payment_number }}</td>
                            <td class="p-4 font-medium text-on-surface">{{ $payment->invoice->student->full_name }}</td>
                            <td class="p-4 font-semibold text-success">GH₵{{ number_format($payment->amount, 2) }}</td>
                            <td class="p-4 uppercase text-xs">{{ $payment->method }}</td>
                            <td class="p-4 text-xs">{{ $payment->payment_date->format('d M, Y') }}</td>
                            <td class="p-4 text-right">
                                <a href="{{ route('billing.receipt', $payment) }}" class="text-primary font-medium hover:underline inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                    Receipt PDF
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="h-8"></div>
@endsection
