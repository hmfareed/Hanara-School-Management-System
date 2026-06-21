<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Staff;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
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
            'email' => strtolower($roleName) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);
        return $user;
    }

    public function test_proprietor_can_view_settings(): void
    {
        $user = $this->createUserWithRole('Proprietor', 'Proprietor');
        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('System Settings');
        $response->assertSee('Hanara Schools');
    }

    public function test_class_teacher_cannot_view_settings(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher');
        \App\Models\TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => \App\Models\SchoolClass::first()->id,
            'subject_id' => null,
            'is_form_teacher' => true,
        ]);
        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(403);
    }

    public function test_proprietor_can_update_settings_with_valid_weights(): void
    {
        $user = $this->createUserWithRole('Proprietor', 'Proprietor');

        $response = $this->actingAs($user)->post('/settings', [
            'settings' => [
                'school_name' => 'New Hanara Academy',
                'ca_weight' => 40,
                'exam_weight' => 60,
            ]
        ]);

        $response->assertRedirect('/settings');
        $this->assertEquals('New Hanara Academy', Setting::get('school_name'));
        $this->assertEquals(40, Setting::get('ca_weight'));
        $this->assertEquals(60, Setting::get('exam_weight'));

        // Check audit logs
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'updated',
        ]);
    }

    public function test_settings_update_fails_if_weights_do_not_sum_to_100(): void
    {
        $user = $this->createUserWithRole('Proprietor', 'Proprietor');

        $response = $this->actingAs($user)->post('/settings', [
            'settings' => [
                'school_name' => 'New Hanara Academy',
                'ca_weight' => 40,
                'exam_weight' => 50, // Sums to 90
            ]
        ]);

        $response->assertSessionHasErrors('settings.ca_weight');
        $this->assertEquals('Hanara Schools', Setting::get('school_name')); // Name not changed
    }
}
