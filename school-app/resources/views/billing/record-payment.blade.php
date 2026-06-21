@extends('layouts.app')

@section('title', 'Record Fee Payment')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ $invoice ? route('students.show', $invoice->student_id) : route('billing.invoices') }}" 
           class="btn-ghost !p-2 rounded-full hover:bg-surface-container transition-colors">
            <span class="material-symbols-outlined text-[24px]">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg-mob text-on-surface font-semibold mb-1">Record Fee Payment</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Log cash, bank, or MoMo payments manually in the student's ledger.</p>
        </div>
    </div>

    <!-- Payment Logging Form -->
    <div class="card p-6 space-y-6">
        <form action="{{ route('billing.record-payment.post') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Invoice Selector / Display -->
            <div>
                <label class="form-label text-xs" for="invoice_id">Select Invoice / Student</label>
                @if($invoice)
                    <!-- Preselected invoice view -->
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="p-4 bg-surface-container-low border border-outline-variant rounded-2xl flex justify-between items-center">
                        <div>
                            <div class="font-title-medium text-title-medium font-bold text-on-surface">{{ $invoice->student->full_name }}</div>
                            <div class="text-xs text-on-surface-variant font-mono">ID: {{ $invoice->student->student_id_number }} • Invoice: {{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-on-surface-variant font-label-md">Outstanding Balance</span>
                            <div class="text-lg font-bold text-error">GH₵{{ number_format($invoice->balance, 2) }}</div>
                        </div>
                    </div>
                @else
                    <!-- Dropdown selector -->
                    <select name="invoice_id" id="invoice_id" class="form-input-custom !py-2" required
                            onchange="updateOutstanding(this)">
                        <option value="">— Select Invoice —</option>
                        @foreach ($invoices as $inv)
                            <option value="{{ $inv->id }}" data-balance="{{ $inv->balance }}" {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                {{ $inv->invoice_number }} — {{ $inv->student->full_name }} (Bal: GH₵{{ number_format($inv->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    
                    <div class="mt-3 p-4 bg-surface-container-low border border-outline-variant rounded-2xl hidden justify-between items-center" id="balance-widget">
                        <span class="text-xs text-on-surface-variant font-label-md">Selected Invoice Outstanding</span>
                        <div class="text-lg font-bold text-error" id="selected-balance-display">GH₵0.00</div>
                    </div>
                @endif
            </div>

            <!-- Amount & Payment Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs" for="amount">Payment Amount (GH₵)</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" 
                           value="{{ old('amount', $invoice ? $invoice->balance : '') }}" 
                           class="form-input-custom !py-2" required>
                    @error('amount')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="form-label text-xs" for="payment_date">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" 
                           value="{{ old('payment_date', now()->format('Y-m-d')) }}" 
                           class="form-input-custom !py-2" max="{{ now()->format('Y-m-d') }}" required>
                    @error('payment_date')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Payment Method & Reference -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs" for="method">Payment Method</label>
                    <select name="method" id="method" class="form-input-custom !py-2" required>
                        <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="momo" {{ old('method') === 'momo' ? 'selected' : '' }}>Mobile Money (MoMo)</option>
                        <option value="bank_transfer" {{ old('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Deposit</option>
                        <option value="card" {{ old('method') === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                    </select>
                    @error('method')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="form-label text-xs" for="reference">Transaction Reference / Receipt #</label>
                    <input type="text" name="reference" id="reference" value="{{ old('reference') }}" 
                           class="form-input-custom !py-2" placeholder="e.g. Bank slip # or MoMo transaction ID">
                    @error('reference')
                        <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="form-label text-xs" for="notes">Notes / Remarks</label>
                <textarea name="notes" id="notes" rows="3" class="form-input-custom" placeholder="Optional audit details...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-xs text-error font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Action -->
            <div class="flex justify-end gap-3 border-t border-outline-variant pt-4">
                <a href="{{ $invoice ? route('students.show', $invoice->student_id) : route('billing.invoices') }}" 
                   class="btn-ghost !py-2 px-4 text-xs">Cancel</a>
                <button type="submit" class="btn-primary !py-2 px-6 text-xs flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateOutstanding(select) {
    var widget = document.getElementById('balance-widget');
    var display = document.getElementById('selected-balance-display');
    var amountInput = document.getElementById('amount');
    
    if (select.value) {
        var option = select.options[select.selectedIndex];
        var balance = option.getAttribute('data-balance');
        
        display.innerText = 'GH₵' + parseFloat(balance).toFixed(2);
        amountInput.value = parseFloat(balance).toFixed(2);
        
        widget.style.display = 'flex';
    } else {
        widget.style.display = 'none';
        amountInput.value = '';
    }
}
</script>
@endsection
