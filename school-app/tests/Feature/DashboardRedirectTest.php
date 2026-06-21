<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Staff;
use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
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
            'status' => 'active',
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

    public function test_proprietor_redirects_to_proprietor_dashboard(): void
    {
        $user = $this->createUserWithRole('Proprietor', 'Proprietor');
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/proprietor');
    }

    public function test_head_teacher_redirects_to_head_teacher_dashboard(): void
    {
        $user = $this->createUserWithRole('Head Teacher', 'HeadTeacher');
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/head-teacher');
    }

    public function test_accounts_redirects_to_accounts_dashboard(): void
    {
        $user = $this->createUserWithRole('Bursar', 'Accounts');
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/dashboard/accounts');
    }

    public function test_class_teacher_cannot_access_proprietor_dashboard(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher');
        \App\Models\TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => \App\Models\SchoolClass::first()->id,
            'subject_id' => null,
            'is_form_teacher' => true,
        ]);
        $response = $this->actingAs($user)->get('/dashboard/proprietor');
        $response->assertStatus(403);
    }

    public function test_parent_cannot_access_settings(): void
    {
        $guardian = Guardian::create([
            'first_name' => 'Parent',
            'last_name' => 'User',
            'phone' => '+233111222333',
            'relationship' => 'Father',
        ]);

        $user = User::create([
            'name' => 'Parent User',
            'email' => 'parent@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Guardian::class,
            'userable_id' => $guardian->id,
            'must_change_password' => false,
        ]);
        $user->assignRole('Parent');

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(403);
    }
}
