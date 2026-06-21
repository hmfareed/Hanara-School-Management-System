<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
    }

    private function createUserWithRole(string $position, string $roleName)
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => 'User',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => $position,
        ]);

        $user = User::create([
            'name' => 'Test ' . $roleName,
            'email' => strtolower($roleName) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);
        return $user;
    }

    public function test_accounts_can_record_payment_and_invoice_transitions_status(): void
    {
        $accountsUser = $this->createUserWithRole('Accounts Officer', 'Accounts');
        
        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentTerm = Term::create([
            'academic_year_id' => $currentYear->id,
            'name' => 'Term 1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
            'is_current' => true,
        ]);

        $student = Student::create([
            'student_id_number' => 'HAN-2026-9999',
            'first_name' => 'Adjoa',
            'last_name' => 'Mensah',
            'date_of_birth' => '2016-04-12',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-T1-9999',
            'student_id' => $student->id,
            'term_id' => $currentTerm->id,
            'total_amount' => 600.00,
            'amount_paid' => 0.00,
            'balance' => 600.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        // 1. Record Partial Payment
        $response = $this->actingAs($accountsUser)->post('/billing/record-payment', [
            'invoice_id' => $invoice->id,
            'amount' => 200.00,
            'method' => 'momo',
            'reference' => 'TXN-123456789',
            'payment_date' => now()->format('Y-m-d'),
            'notes' => 'Partial payment via MTN Mobile Money.',
        ]);

        $response->assertRedirect("/students/{$student->id}");
        $response->assertSessionHas('success');

        // Verify database updates
        $invoice->refresh();
        $this->assertEquals(200.00, $invoice->amount_paid);
        $this->assertEquals(400.00, $invoice->balance);
        $this->assertEquals('partial', $invoice->status);

        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(200.00, $payment->amount);
        $this->assertEquals('momo', $payment->method);
        $this->assertEquals('TXN-123456789', $payment->reference);

        // Verify audit logs
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $accountsUser->id,
            'action' => 'record_payment',
            'auditable_type' => Payment::class,
            'auditable_id' => $payment->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $accountsUser->id,
            'action' => 'update_invoice_payment',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
        ]);

        // 2. Record Final Payment to Settle Invoice
        $this->actingAs($accountsUser)->post('/billing/record-payment', [
            'invoice_id' => $invoice->id,
            'amount' => 400.00,
            'method' => 'cash',
            'payment_date' => now()->format('Y-m-d'),
            'notes' => 'Final settlement.',
        ]);

        $invoice->refresh();
        $this->assertEquals(600.00, $invoice->amount_paid);
        $this->assertEquals(0.00, $invoice->balance);
        $this->assertEquals('paid', $invoice->status);

        // 3. Test PDF Receipt download
        $latestPayment = Payment::orderBy('id', 'desc')->first();
        $pdfResponse = $this->actingAs($accountsUser)->get("/billing/payments/{$latestPayment->id}/receipt");
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
