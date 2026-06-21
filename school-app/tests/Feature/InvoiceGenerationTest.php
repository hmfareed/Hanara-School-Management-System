<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\FeeItem;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
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

    public function test_bulk_invoice_generation_maps_fee_items_correctly(): void
    {
        $admin = $this->createUserWithRole('Proprietor', 'Proprietor');
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Reset any seeded terms' is_current flag so our test term is unambiguous
        Term::query()->update(['is_current' => false]);

        // Create an active Term
        $currentTerm = Term::create([
            'academic_year_id' => $currentYear->id,
            'name' => 'Term 1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
            'is_current' => true,
        ]);

        $schoolClass = SchoolClass::where('name', 'P1')->first();
        $classAcYear = ClassAcademicYear::where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        // Configure fee items
        // 1. Tuition fee specific to P1
        $tuitionFee = FeeItem::create([
            'name' => 'Tuition',
            'amount' => 500.00,
            'academic_year_id' => $currentYear->id,
            'term_id' => $currentTerm->id,
            'school_class_id' => $schoolClass->id,
            'is_optional' => false,
        ]);

        // 2. PTA Levy applicable to all classes and terms
        $ptaLevy = FeeItem::create([
            'name' => 'PTA Levy',
            'amount' => 50.00,
            'academic_year_id' => $currentYear->id,
            'term_id' => null,
            'school_class_id' => null,
            'is_optional' => false,
        ]);

        // 3. Optional transport fee (should NOT be auto-billed)
        $transportFee = FeeItem::create([
            'name' => 'Transport',
            'amount' => 150.00,
            'academic_year_id' => $currentYear->id,
            'term_id' => $currentTerm->id,
            'school_class_id' => $schoolClass->id,
            'is_optional' => true,
        ]);

        // Create student
        $student = Student::create([
            'student_id_number' => 'HAN-2026-0123',
            'first_name' => 'Kofi',
            'last_name' => 'Annan',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        // Enroll student in P1
        ClassStudent::create([
            'student_id' => $student->id,
            'class_academic_year_id' => $classAcYear->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        // Trigger bulk invoice generation
        $response = $this->actingAs($admin)->post('/billing/invoices/generate');

        $response->assertRedirect('/billing/invoices');
        $response->assertSessionHas('success');

        // Check invoice was generated
        $this->assertDatabaseHas('invoices', [
            'student_id' => $student->id,
            'term_id' => $currentTerm->id,
            'total_amount' => 550.00, // Tuition (500) + PTA (50). Transport (150) omitted because optional.
            'amount_paid' => 0.00,
            'balance' => 550.00,
            'status' => 'unpaid',
        ]);

        $invoice = Invoice::where('student_id', $student->id)->where('term_id', $currentTerm->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals("INV-T{$currentTerm->id}-2026-0123", $invoice->invoice_number);

        // Check audit logs
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'generate_invoice',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
        ]);

        // Second run: Test idempotency (should NOT duplicate the invoice)
        $this->actingAs($admin)->post('/billing/invoices/generate');
        $this->assertEquals(1, Invoice::where('student_id', $student->id)->where('term_id', $currentTerm->id)->count());
    }
}
