<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Staff;
use App\Models\Guardian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Hanara Schools');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1985-05-05',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => 'Proprietor',
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);
        $user->assignRole('Proprietor');

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard/proprietor');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1985-05-05',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => 'Proprietor',
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_first_login_forces_password_change(): void
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '1985-05-05',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => 'Proprietor',
        ]);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => true, // Needs password change
        ]);
        $user->assignRole('Proprietor');

        // Authed user tries to visit dashboard
        $response = $this->actingAs($user)->get('/dashboard/proprietor');

        // Redirected to password change
        $response->assertRedirect('/change-password');

        // Submit new password
        $response = $this->actingAs($user)->post('/change-password', [
            'current_password' => 'password123',
            'password' => 'new-secure-password123',
            'password_confirmation' => 'new-secure-password123',
        ]);

        $response->assertRedirect('/dashboard/proprietor');
        $this->assertFalse($user->fresh()->must_change_password);
    }
}
