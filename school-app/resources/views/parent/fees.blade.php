@extends('layouts.app')
@section('title', $student->full_name . ' — Fees')
@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between mb-section-gap gap-4">
    <div>
        <a href="{{ route('dashboard.parent') }}" class="inline-flex items-center gap-1 text-primary font-label-md text-label-md mb-2 hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Back to Dashboard
        </a>
        <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background">{{ $student->full_name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Fee Invoices & Payment History</p>
    </div>
</div>

{{-- Fee Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-section-gap">
    <div class="card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-[#dcfce7] flex items-center justify-center">
            <span class="material-symbols-outlined text-[#166534]" style="font-size: 28px;">payments</span>
        </div>
        <div>
            <p class="font-label-md text-label-md text-on-surface-variant">Total Paid</p>
            <p class="font-headline-md text-headline-md font-bold text-[#166534]">GH₵{{ number_format($totalPaid, 2) }}</p>
        </div>
    </div>
    <div class="card p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-error-container/30 flex items-center justify-center">
            <span class="material-symbols-outlined text-error" style="font-size: 28px;">account_balance_wallet</span>
        </div>
        <div>
            <p class="font-label-md text-label-md text-on-surface-variant">Outstanding Balance</p>
            <p class="font-headline-md text-headline-md font-bold {{ $totalOwed > 0 ? 'text-error' : 'text-[#166534]' }}">GH₵{{ number_format($totalOwed, 2) }}</p>
        </div>
    </div>
</div>

{{-- Invoices List --}}
<div class="space-y-4">
    @forelse($invoices as $invoice)
    <div class="card overflow-hidden">
        <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-outline-variant">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-title-md text-title-md font-semibold text-on-surface">{{ $invoice->invoice_number }}</span>
                    <span class="badge {{ $invoice->balance <= 0 ? 'badge-success' : ($invoice->due_date && $invoice->due_date->isPast() ? 'badge-error' : 'badge-warning') }}">
                        {{ $invoice->balance <= 0 ? 'Paid' : ($invoice->due_date && $invoice->due_date->isPast() ? 'Overdue' : 'Pending') }}
                    </span>
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ $invoice->term?->name ?? 'N/A' }} · Due: {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="font-label-md text-label-md text-on-surface-variant">Balance</p>
                    <p class="font-title-lg text-title-lg font-bold {{ $invoice->balance > 0 ? 'text-error' : 'text-[#166534]' }}">
                        GH₵{{ number_format($invoice->balance, 2) }}
                    </p>
                </div>
                @if($invoice->balance > 0)
                <form action="{{ route('parent.child.pay', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary py-2 px-4 flex items-center gap-1.5" id="btn-pay-{{ $invoice->id }}">
                        <span class="material-symbols-outlined text-[16px]">credit_card</span>
                        Pay Now
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Payment History for this invoice --}}
        @if($invoice->payments->isNotEmpty())
        <div class="px-4 py-3 bg-surface-container-lowest/50">
            <p class="font-label-md text-label-md text-on-surface-variant mb-2">Payment History</p>
            <div class="space-y-1.5">
                @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#166534] text-[16px]">check_circle</span>
                        <span class="font-body-md text-body-md text-on-surface">GH₵{{ number_format($payment->amount, 2) }}</span>
                        <span class="font-label-md text-label-md text-on-surface-variant">({{ ucfirst($payment->method) }})</span>
                    </div>
                    <span class="font-label-md text-label-md text-outline">{{ $payment->created_at->format('d M Y') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="card p-8 text-center">
        <span class="material-symbols-outlined text-outline mb-4" style="font-size: 64px;">receipt_long</span>
        <h3 class="font-title-lg text-title-lg text-on-surface mb-2">No Invoices</h3>
        <p class="font-body-md text-body-md text-on-surface-variant">No fee invoices have been generated yet.</p>
    </div>
    @endforelse
</div>
@endsection
