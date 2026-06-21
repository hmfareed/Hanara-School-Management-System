<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Staff;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StaffCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions & metadata
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    public function test_staff_can_register_with_valid_pin(): void
    {
        $pinCode = StaffCode::create([
            'code' => '998877',
            'is_used' => false,
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Michael',
            'last_name' => 'Owusu',
            'email' => 'michael@example.com',
            'role' => 'Staff',
            'staff_pin' => '998877',
            'position' => 'Form Master', // Maps to Class Teacher
            'gender' => 'male',
            'date_of_birth' => '1990-05-15',
            'phone' => '+233240000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();

        $user = User::where('email', 'michael@example.com')->first();
        $this->assertNotNull($user);
        $this->assertInstanceOf(Staff::class, $user->userable);
        $this->assertEquals('Class Teacher', $user->userable->position);
        $this->assertEquals('pending', $user->userable->status);
        $this->assertTrue($user->hasRole('ClassTeacher'));

        // Check PIN was NOT marked used (shared registration PIN persists)
        $this->assertFalse($pinCode->fresh()->is_used);
        $this->assertNull($pinCode->fresh()->used_by_user_id);
    }

    public function test_staff_cannot_register_with_invalid_pin(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Michael',
            'last_name' => 'Owusu',
            'email' => 'michael@example.com',
            'role' => 'Staff',
            'staff_pin' => 'wrong-pin',
            'position' => 'Form Master',
            'gender' => 'male',
            'date_of_birth' => '1990-05-15',
            'phone' => '+233240000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['staff_pin']);
        $this->assertGuest();
    }

    public function test_parent_can_register_and_optionally_link_child(): void
    {
        $student = Student::create([
            'student_id_number' => 'HAN-2026-9999',
            'first_name' => 'Kojo',
            'last_name' => 'Ansah',
            'date_of_birth' => '2015-06-20',
            'gender' => 'male',
            'status' => 'active',
            'admission_date' => '2026-01-01',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Abena',
            'last_name' => 'Ansah',
            'email' => 'abena@example.com',
            'role' => 'Parent',
            'relationship' => 'Mother',
            'phone' => '+233241112222',
            'address' => '12 Anaji St, Takoradi',
            'student_id_number' => 'HAN-2026-9999',
            'student_date_of_birth' => '2015-06-20',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertInstanceOf(Guardian::class, $user->userable);
        $this->assertTrue($user->hasRole('Parent'));

        // Verify child linkage
        $guardian = $user->userable;
        $this->assertCount(1, $guardian->students);
        $this->assertEquals($student->id, $guardian->students->first()->id);
    }

    public function test_student_can_register_by_verifying_details(): void
    {
        $student = Student::create([
            'student_id_number' => 'HAN-2026-5555',
            'first_name' => 'Yaw',
            'last_name' => 'Boakye',
            'date_of_birth' => '2012-08-15',
            'gender' => 'male',
            'status' => 'active',
            'admission_date' => '2026-01-01',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Yaw',
            'last_name' => 'Boakye',
            'email' => 'yaw@example.com',
            'role' => 'Student',
            'student_id_number' => 'HAN-2026-5555',
            'student_date_of_birth' => '2012-08-15',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertInstanceOf(Student::class, $user->userable);
        $this->assertTrue($user->hasRole('Student'));
        $this->assertEquals($student->id, $user->userable->id);
    }

    public function test_student_registration_fails_if_dob_is_incorrect(): void
    {
        $student = Student::create([
            'student_id_number' => 'HAN-2026-5555',
            'first_name' => 'Yaw',
            'last_name' => 'Boakye',
            'date_of_birth' => '2012-08-15',
            'gender' => 'male',
            'status' => 'active',
            'admission_date' => '2026-01-01',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Yaw',
            'last_name' => 'Boakye',
            'email' => 'yaw@example.com',
            'role' => 'Student',
            'student_id_number' => 'HAN-2026-5555',
            'student_date_of_birth' => '1999-01-01', // Incorrect DOB
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['student_date_of_birth']);
        $this->assertGuest();
    }
}
