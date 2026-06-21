<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CreditNote;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FinanceExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
        $this->artisan('db:seed', ['--class' => 'SettingsSeeder']);

        // Set up Paystack keys in env for the test
        config(['services.paystack.secret_key' => 'sk_test_123456789']);

        // Create current term
        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            Term::create([
                'academic_year_id' => $currentYear->id,
                'name' => 'Term 1',
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'is_current' => true,
            ]);
        }
    }

    private function createUserWithRole(string $position, string $roleName)
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => 'User',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '+233' . rand(100000000, 999999999),
            'date_joined' => '2020-01-01',
            'position' => $position,
        ]);

        $user = User::create([
            'name' => 'Test ' . $roleName,
            'email' => strtolower($roleName) . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);
        return $user;
    }

    public function test_paystack_online_payment_initialization(): void
    {
        $user = $this->createUserWithRole('Bursar', 'Accounts');
        $student = Student::create([
            'student_id_number' => 'HAN-2026-8001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        $term = Term::where('is_current', true)->first();

        $invoice = Invoice::create([
            'invoice_number' => 'INV-T1-8001',
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 500.00,
            'amount_paid' => 0.00,
            'balance' => 500.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        // Fake Paystack initialization response
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/mock_auth_url',
                    'access_code' => 'mock_access_code',
                    'reference' => 'mock_ref_123',
                ]
            ], 200)
        ]);

        $response = $this->actingAs($user)->post(route('billing.pay.initialize', $invoice));

        $response->assertRedirect('https://checkout.paystack.com/mock_auth_url');
    }

    public function test_paystack_webhook_processing(): void
    {
        $student = Student::create([
            'student_id_number' => 'HAN-2026-8002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2015-05-05',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        $term = Term::where('is_current', true)->first();

        $invoice = Invoice::create([
            'invoice_number' => 'INV-T1-8002',
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 600.00,
            'amount_paid' => 100.00,
            'balance' => 500.00,
            'status' => 'partial',
            'due_date' => now()->addDays(30),
        ]);

        $reference = "PAY-{$invoice->id}-uniqref123";

        // Paystack charge.success payload
        $payload = [
            'event' => 'charge.success',
            'data' => [
                'reference' => $reference,
                'amount' => 50000, // 500.00 GHS in pesewas
                'status' => 'success',
                'channel' => 'mobile_money',
            ]
        ];

        $payloadJson = json_encode($payload);
        $secretKey = config('services.paystack.secret_key');
        $signature = hash_hmac('sha512', $payloadJson, $secretKey);

        $response = $this->withHeaders([
            'x-paystack-signature' => $signature,
        ])->postJson(route('billing.pay.webhook'), $payload);

        $response->assertStatus(200);

        // Verify database state has updated
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'method' => 'card', // Paystack payments stored under standard online card/payment log
            'reference' => $reference,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount_paid' => 600.00,
            'balance' => 0.00,
            'status' => 'paid',
        ]);

        // Verify audit log has registered it
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'record_payment',
        ]);
    }

    public function test_fee_defaulters_listing(): void
    {
        $user = $this->createUserWithRole('Bursar', 'Accounts');
        $student = Student::create([
            'student_id_number' => 'HAN-2026-8003',
            'first_name' => 'Overdue',
            'last_name' => 'Student',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        $term = Term::where('is_current', true)->first();

        // Create overdue invoice (due_date in past, balance > 0)
        $invoice = Invoice::create([
            'invoice_number' => 'INV-T1-8003',
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 500.00,
            'amount_paid' => 0.00,
            'balance' => 500.00,
            'status' => 'unpaid',
            'due_date' => now()->subDays(5), // overdue
        ]);

        $response = $this->actingAs($user)->get(route('billing.defaulters'));

        $response->assertStatus(200);
        $response->assertSee('INV-T1-8003');
        $response->assertSee('Overdue Student');
    }

    public function test_credit_note_adjustment(): void
    {
        $user = $this->createUserWithRole('Bursar', 'Accounts');
        $student = Student::create([
            'student_id_number' => 'HAN-2026-8004',
            'first_name' => 'Credit',
            'last_name' => 'Receiver',
            'date_of_birth' => '2015-05-05',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        $term = Term::where('is_current', true)->first();

        $invoice = Invoice::create([
            'invoice_number' => 'INV-T1-8004',
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 500.00,
            'amount_paid' => 0.00,
            'balance' => 500.00,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->post(route('billing.credit-note.store', $invoice), [
            'amount' => 150.00,
            'reason' => 'Scholarship waiver for outstanding performance',
        ]);

        $response->assertRedirect();

        // Verify credit note was created in DB
        $this->assertDatabaseHas('credit_notes', [
            'invoice_id' => $invoice->id,
            'amount' => 150.00,
            'reason' => 'Scholarship waiver for outstanding performance',
        ]);

        // Verify invoice balance was reduced
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount_paid' => 150.00,
            'balance' => 350.00,
            'status' => 'partial',
        ]);
    }

    public function test_student_id_card_pdf_generation(): void
    {
        $user = $this->createUserWithRole('Head Teacher', 'HeadTeacher');
        $student = Student::create([
            'student_id_number' => 'HAN-2026-8005',
            'first_name' => 'Card',
            'last_name' => 'Holder',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        $response = $this->actingAs($user)->get(route('students.id-card', $student));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
