<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\TeacherAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
        $this->artisan('db:seed', ['--class' => 'SettingsSeeder']);
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
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Test ' . $roleName,
            'email' => strtolower($roleName) . '_' . rand(1, 100) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);
        return $user;
    }

    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_principal_can_access_all_pages(): void
    {
        $user = $this->createUserWithRole('Head Teacher', 'HeadTeacher');

        $response = $this->actingAs($user)->get('/dashboard/head-teacher');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/students');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/admissions');
        $response->assertStatus(200);
    }

    public function test_supervisor_has_read_only_access(): void
    {
        $user = $this->createUserWithRole('Supervisor', 'Supervisor');

        // View dashboards & listings is allowed
        $response = $this->actingAs($user)->get('/dashboard/head-teacher');
        $response->assertStatus(200);
        $response->assertSee('Read-Only Access');

        $response = $this->actingAs($user)->get('/students');
        $response->assertStatus(200);

        // Edit forms & mutations should be forbidden
        $response = $this->actingAs($user)->get('/students/import');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/students/create');
        $response->assertStatus(403);

        // Cannot send announcements or compose SMS
        $response = $this->actingAs($user)->get('/communication/sms/compose');
        $response->assertStatus(403);
    }

    public function test_accountant_only_accesses_finance(): void
    {
        $user = $this->createUserWithRole('Bursar', 'Accounts');

        $response = $this->actingAs($user)->get('/billing/invoices');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/billing/defaulters');
        $response->assertStatus(200);

        // Cannot view settings or staff directory
        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/staff');
        $response->assertStatus(403);
    }

    public function test_form_teacher_scoping(): void
    {
        $user = $this->createUserWithRole('Teacher', 'ClassTeacher');
        $currentYear = AcademicYear::where('is_current', true)->first();

        // Let's create two classes
        $classA = SchoolClass::where('name', 'P1')->first();
        $classB = SchoolClass::where('name', 'P2')->first();

        $cayA = ClassAcademicYear::where('school_class_id', $classA->id)->where('academic_year_id', $currentYear->id)->first();
        $cayB = ClassAcademicYear::where('school_class_id', $classB->id)->where('academic_year_id', $currentYear->id)->first();

        // Assign user to classA as Form Teacher
        TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => $classA->id,
            'subject_id' => null,
            'is_form_teacher' => true,
        ]);

        // Create student in Class A and Class B
        $studentA = Student::create([
            'student_id_number' => 'STD-001',
            'first_name' => 'Alice',
            'last_name' => 'ClassA',
            'date_of_birth' => '2015-01-01',
            'gender' => 'female',
            'admission_date' => now(),
            'status' => 'active',
        ]);
        ClassStudent::create([
            'student_id' => $studentA->id,
            'class_academic_year_id' => $cayA->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $studentB = Student::create([
            'student_id_number' => 'STD-002',
            'first_name' => 'Bob',
            'last_name' => 'ClassB',
            'date_of_birth' => '2015-01-01',
            'gender' => 'male',
            'admission_date' => now(),
            'status' => 'active',
        ]);
        ClassStudent::create([
            'student_id' => $studentB->id,
            'class_academic_year_id' => $cayB->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Access student list: should see Alice but not Bob
        $response = $this->actingAs($user)->get('/students');
        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');

        // Access Alice details: allowed
        $response = $this->actingAs($user)->get("/students/{$studentA->id}");
        $response->assertStatus(200);

        // Access Bob details: forbidden
        $response = $this->actingAs($user)->get("/students/{$studentB->id}");
        $response->assertStatus(403);
    }
}
