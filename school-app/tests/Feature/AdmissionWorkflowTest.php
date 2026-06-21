<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionWorkflowTest extends TestCase
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

    public function test_guest_can_submit_admission_application(): void
    {
        $schoolClass = SchoolClass::first();

        $response = $this->post('/admissions/apply', [
            'first_name' => 'Kofi',
            'last_name' => 'Annan',
            'other_names' => 'Atta',
            'date_of_birth' => '2018-03-15',
            'gender' => 'male',
            'level' => 'primary',
            'assigned_class_id' => $schoolClass->id,
            'guardian_name' => 'Kojo Annan',
            'guardian_phone' => '+233240000001',
            'guardian_email' => 'kojo@example.com',
            'guardian_relationship' => 'Father',
        ]);

        $response->assertRedirect('/admissions/apply');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('admissions', [
            'first_name' => 'Kofi',
            'last_name' => 'Annan',
            'status' => 'pending',
            'guardian_phone' => '+233240000001',
        ]);
    }

    public function test_admin_can_approve_admission_and_enroll_student(): void
    {
        $schoolClass = SchoolClass::first();
        
        $admission = Admission::create([
            'first_name' => 'Ama',
            'last_name' => 'Kofi',
            'date_of_birth' => '2019-06-20',
            'gender' => 'female',
            'level' => 'primary',
            'assigned_class_id' => $schoolClass->id,
            'guardian_name' => 'Adjoa Kofi',
            'guardian_phone' => '+233240000002',
            'guardian_relationship' => 'Mother',
        ]);

        $admin = $this->createUserWithRole('Front Desk', 'FrontDesk');

        $response = $this->actingAs($admin)->post("/admissions/{$admission->id}/approve", [
            'assigned_class_id' => $schoolClass->id,
            'notes' => 'Approved and enrolled in P1.',
        ]);

        $response->assertRedirect('/admissions');
        $response->assertSessionHas('success');

        // Verify status change
        $admission->refresh();
        $this->assertEquals('accepted', $admission->status);
        $this->assertEquals('Approved and enrolled in P1.', $admission->notes);

        // Verify Student created
        $student = Student::where('first_name', 'Ama')->where('last_name', 'Kofi')->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->student_id_number);
        $this->assertStringStartsWith('HAN-', $student->student_id_number);

        // Verify Guardian created and linked
        $guardian = Guardian::where('phone', '+233240000002')->first();
        $this->assertNotNull($guardian);
        $this->assertEquals('Adjoa', $guardian->first_name);
        $this->assertTrue($student->guardians->contains($guardian->id));

        // Verify Class Enrollment
        $enrollment = $student->currentClassEnrollment();
        $this->assertNotNull($enrollment);
        $this->assertEquals($schoolClass->id, $enrollment->classAcademicYear->school_class_id);
    }
}
