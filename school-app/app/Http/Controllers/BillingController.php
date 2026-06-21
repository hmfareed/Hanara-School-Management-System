<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\FeeItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display a listing of invoices with filters.
     */
    public function invoices(Request $request)
    {
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $status = $request->input('status');

        $query = Invoice::query()->with(['student', 'term.academicYear']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sQ) use ($search) {
                      $sQ->where('student_id_number', 'like', "%{$search}%")
                         ->orWhere('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($classId) {
            $query->whereHas('student.classEnrollments', function ($q) use ($classId) {
                $q->where('status', 'enrolled')
                  ->whereHas('classAcademicYear', function ($cQ) use ($classId) {
                      $cQ->where('school_class_id', $classId);
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $classes = SchoolClass::orderBy('display_order')->get();
        $currentTerm = Term::current();

        return view('billing.invoices', compact('invoices', 'classes', 'search', 'classId', 'status', 'currentTerm'));
    }

    /**
     * Trigger bulk invoice generation for the current academic term.
     */
    public function generateInvoices(Request $request)
    {
        $currentTerm = Term::current();
        if (!$currentTerm) {
            return back()->with('warning', 'No active academic term configured. Please configure a current term in Settings first.');
        }

        $currentYear = $currentTerm->academicYear;

        // Get all active enrolled students for the current academic year
        $enrollments = ClassStudent::where('status', 'enrolled')
            ->whereHas('classAcademicYear', function ($q) use ($currentYear) {
                $q->where('academic_year_id', $currentYear->id);
            })
            ->with(['student', 'classAcademicYear.schoolClass'])
            ->get();

        if ($enrollments->isEmpty()) {
            return back()->with('warning', 'No active student enrollments found for the current academic year.');
        }

        $generatedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($enrollments as $enrollment) {
                $student = $enrollment->student;
                $class = $enrollment->classAcademicYear->schoolClass;

                // Check if invoice already exists for this student and term
                $existing = Invoice::where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id)
                    ->first();

                if ($existing) {
                    continue; // Skip already billed students
                }

                // Query fee items applicable to this class and term
                $feeItems = FeeItem::where('academic_year_id', $currentYear->id)
                    ->where(function ($q) use ($currentTerm) {
                        $q->where('term_id', $currentTerm->id)
                          ->orWhereNull('term_id');
                    })
                    ->where(function ($q) use ($class) {
                        $q->where('school_class_id', $class->id)
                          ->orWhereNull('school_class_id');
                    })
                    ->where('is_optional', false)
                    ->get();

                $totalAmount = $feeItems->sum('amount');

                if ($totalAmount <= 0) {
                    continue; // Skip if no fees are configured/applicable
                }

                // Generate unique invoice number
                // Format: INV-[TERM_ID]-[STUDENT_ID_SEQ]
                $idSeq = str_replace('HAN-', '', $student->student_id_number);
                $invoiceNumber = "INV-T{$currentTerm->id}-{$idSeq}";

                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'student_id' => $student->id,
                    'term_id' => $currentTerm->id,
                    'total_amount' => $totalAmount,
                    'amount_paid' => 0.00,
                    'balance' => $totalAmount,
                    'status' => 'unpaid',
                    'due_date' => $currentTerm->end_date ?? now()->addDays(30),
                ]);

                // Log the action
                AuditLog::log('generate_invoice', $invoice, null, $invoice->toArray());

                $generatedCount++;
            }

            DB::commit();

            return redirect()->route('billing.invoices')
                ->with('success', "Invoice generation completed! Generated {$generatedCount} new invoice(s) for the current term.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Bulk generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show form to log manual payment.
     */
    public function recordPaymentForm(Request $request)
    {
        $invoiceId = $request->input('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = Invoice::with('student')->findOrFail($invoiceId);
        }

        // Get unpaid/partial invoices for lookup if needed
        $invoices = Invoice::with('student')
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        return view('billing.record-payment', compact('invoice', 'invoices', 'invoiceId'));
    }

    /**
     * Process and save manual payment.
     */
    public function recordPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,momo,bank_transfer,card'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        $amount = (float) $request->amount;

        if ($amount > (float) $invoice->balance) {
            return back()->withErrors(['amount' => "Payment amount (GH₵{$amount}) cannot exceed outstanding invoice balance (GH₵{$invoice->balance})."])->withInput();
        }

        DB::beginTransaction();

        try {
            // Generate unique receipt number
            // Format: REC-YYYYMMDD-[SEQ]
            $date = date('Ymd');
            $count = Payment::whereDate('created_at', now()->toDateString())->count() + 1;
            $paymentNumber = "REC-{$date}-" . sprintf('%04d', $count);

            // Create Payment record
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'method' => $request->method,
                'reference' => $request->reference,
                'received_by' => auth()->id(),
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            // Update Invoice details
            $oldInvoiceState = $invoice->toArray();
            $newPaid = (float) $invoice->amount_paid + $amount;
            $newBalance = (float) $invoice->total_amount - $newPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newPaid,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            // Create Audit log entries
            AuditLog::log('record_payment', $payment, null, $payment->toArray());
            AuditLog::log('update_invoice_payment', $invoice, $oldInvoiceState, $invoice->toArray());

            DB::commit();

            return redirect()->route('students.show', $invoice->student_id)
                ->with('success', "Payment of GH₵" . number_format($amount, 2) . " recorded successfully. Receipt #: {$paymentNumber}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['invoice_id' => 'Failed to record payment: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Render and download a high-fidelity PDF receipt.
     */
    public function printReceipt(Payment $payment)
    {
        $payment->load(['invoice.student', 'invoice.term.academicYear', 'receivedByUser']);
        
        $pdf = Pdf::loadView('billing.receipt', compact('payment'));
        
        return $pdf->download("receipt-{$payment->payment_number}.pdf");
    }

    /**
     * Display fee defaulters (invoices past due date with remaining balance).
     */
    public function defaulters(Request $request)
    {
        $classId = $request->input('class_id');
        $query = Invoice::query()
            ->with(['student.guardians', 'term.academicYear'])
            ->where('balance', '>', 0)
            ->where('due_date', '<', now()->toDateString());

        if ($classId) {
            $query->whereHas('student.classEnrollments', function ($q) use ($classId) {
                $q->where('status', 'enrolled')
                  ->whereHas('classAcademicYear', function ($cQ) use ($classId) {
                      $cQ->where('school_class_id', $classId);
                  });
            });
        }

        $defaulters = $query->orderBy('due_date', 'asc')->paginate(15);
        $classes = SchoolClass::orderBy('display_order')->get();
        $currentTerm = Term::current();

        return view('billing.defaulters', compact('defaulters', 'classes', 'classId', 'currentTerm'));
    }

    /**
     * Process credit note adjustments.
     */
    public function recordCreditNote(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $amount = (float) $request->amount;

        if ($amount > (float) $invoice->balance) {
            return back()->withErrors(['amount' => "Credit note amount (GH₵{$amount}) cannot exceed outstanding invoice balance (GH₵{$invoice->balance})."]);
        }

        DB::beginTransaction();
        try {
            $date = date('Ymd');
            $count = \App\Models\CreditNote::whereDate('created_at', now()->toDateString())->count() + 1;
            $creditNoteNumber = "CN-{$date}-" . sprintf('%04d', $count);

            $creditNote = \App\Models\CreditNote::create([
                'invoice_id' => $invoice->id,
                'credit_note_number' => $creditNoteNumber,
                'amount' => $amount,
                'reason' => $request->reason,
                'recorded_by' => auth()->id(),
            ]);

            // Adjust invoice balance
            $oldInvoiceState = $invoice->toArray();
            $newPaid = (float) $invoice->amount_paid + $amount;
            $newBalance = (float) $invoice->total_amount - $newPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newPaid,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            AuditLog::log('create_credit_note', $creditNote, null, $creditNote->toArray());
            AuditLog::log('update_invoice_credit', $invoice, $oldInvoiceState, $invoice->toArray());

            DB::commit();

            return back()->with('success', "Credit note of GH₵" . number_format($amount, 2) . " applied successfully. Credit Note #: {$creditNoteNumber}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['amount' => 'Failed to apply credit note: ' . $e->getMessage()]);
        }
    }

    /**
     * Initialize online checkout via Paystack.
     */
    public function initializeOnlinePayment(Invoice $invoice)
    {
        if ($invoice->balance <= 0) {
            return back()->with('warning', 'This invoice has already been fully paid.');
        }

        $email = $invoice->student->guardians->first()?->email ?? auth()->user()->email;
        $reference = 'PAY-' . $invoice->id . '-' . uniqid();

        $paystackService = app(\App\Services\PaystackService::class);
        $result = $paystackService->initializeTransaction(
            $email,
            (float) $invoice->balance,
            $reference,
            route('billing.pay.callback')
        );

        if ($result && isset($result['authorization_url'])) {
            return redirect($result['authorization_url']);
        }

        return back()->withErrors(['paystack' => 'Failed to initialize online payment with Paystack. Please try again.']);
    }

    /**
     * Process Paystack callback redirect.
     */
    public function paystackCallback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('billing.invoices')->with('warning', 'No reference code provided.');
        }

        $paystackService = app(\App\Services\PaystackService::class);
        $data = $paystackService->verifyTransaction($reference);

        if ($data && $data['status'] === 'success') {
            $this->processPaymentSuccess($data);
            return redirect()->route('billing.invoices')->with('success', 'Online payment completed successfully.');
        }

        return redirect()->route('billing.invoices')->withErrors(['paystack' => 'Payment verification failed or payment is pending.']);
    }

    /**
     * Process Paystack webhook notification.
     */
    public function paystackWebhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $secretKey = config('services.paystack.secret_key');

        if (!$signature || !$secretKey) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $computedSignature = hash_hmac('sha512', $request->getContent(), $secretKey);
        if ($computedSignature !== $signature) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        if (isset($payload['event']) && $payload['event'] === 'charge.success') {
            $data = $payload['data'];
            $this->processPaymentSuccess($data);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Helper to persist successful Paystack payments.
     */
    protected function processPaymentSuccess(array $data)
    {
        $reference = $data['reference'];

        $existing = Payment::where('reference', $reference)->first();
        if ($existing) {
            return;
        }

        $parts = explode('-', $reference);
        if (count($parts) < 3 || $parts[0] !== 'PAY') {
            \Illuminate\Support\Facades\Log::error('Invalid Paystack payment reference format: ' . $reference);
            return;
        }

        $invoiceId = (int) $parts[1];
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            \Illuminate\Support\Facades\Log::error('Invoice not found for Paystack reference: ' . $reference);
            return;
        }

        $amount = (float) ($data['amount'] / 100);

        DB::beginTransaction();
        try {
            $date = date('Ymd');
            $count = Payment::whereDate('created_at', now()->toDateString())->count() + 1;
            $paymentNumber = "REC-{$date}-" . sprintf('%04d', $count);

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_number' => $paymentNumber,
                'amount' => $amount,
                'method' => 'card',
                'reference' => $reference,
                'received_by' => null,
                'payment_date' => now()->toDateString(),
                'notes' => 'Paid online via Paystack: ' . ($data['channel'] ?? 'card/momo'),
            ]);

            $oldInvoiceState = $invoice->toArray();
            $newPaid = (float) $invoice->amount_paid + $amount;
            $newBalance = (float) $invoice->total_amount - $newPaid;
            $newStatus = $newBalance <= 0 ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newPaid,
                'balance' => $newBalance,
                'status' => $newStatus,
            ]);

            AuditLog::log('record_payment', $payment, null, $payment->toArray());
            AuditLog::log('update_invoice_payment', $invoice, $oldInvoiceState, $invoice->toArray());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to process Paystack payment success: ' . $e->getMessage());
        }
    }
}
