<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\StaffAttendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffHrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
    }

    private function createUserWithRole(string $position, string $roleName, bool $withAssignment = false)
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => $roleName,
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => $position,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Test ' . $roleName,
            'email' => strtolower($roleName) . rand(100, 999) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);

        // Create a TeacherAssignment so the onboarding middleware does not redirect
        if ($withAssignment && in_array($roleName, ['ClassTeacher', 'SubjectTeacher'])) {
            $schoolClass = SchoolClass::first();
            TeacherAssignment::create([
                'user_id' => $user->id,
                'class_id' => $schoolClass->id,
                'subject_id' => null,
                'is_form_teacher' => $roleName === 'ClassTeacher',
            ]);
        }

        return $user;
    }

    public function test_staff_can_clock_in_and_clock_out(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher', true);

        // Clock in
        $response = $this->actingAs($user)->post('/staff/clock-in');

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertTrue(
            StaffAttendance::where('staff_id', $user->userable_id)
                ->whereDate('date', Carbon::today())
                ->exists()
        );

        // Attempt duplicate clock-in
        $dupResponse = $this->actingAs($user)->post('/staff/clock-in');
        $dupResponse->assertSessionHas('warning');

        // Clock out
        $outResponse = $this->actingAs($user)->post('/staff/clock-out');
        $outResponse->assertStatus(302);
        $outResponse->assertSessionHas('success');

        $attendance = StaffAttendance::where('staff_id', $user->userable_id)
            ->whereDate('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance->clock_out);

        // Attempt duplicate clock-out
        $dupOutResponse = $this->actingAs($user)->post('/staff/clock-out');
        $dupOutResponse->assertSessionHas('warning');
    }

    public function test_staff_can_submit_leave_request(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher', true);

        $response = $this->actingAs($user)->post('/staff/leaves', [
            'leave_type' => 'sick',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'reason' => 'Recovery from dental surgery',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'staff_id' => $user->userable_id,
            'leave_type' => 'sick',
            'status' => 'pending',
            'reason' => 'Recovery from dental surgery',
        ]);
    }

    public function test_proprietor_can_approve_leave_request(): void
    {
        $staffUser = $this->createUserWithRole('Class Teacher', 'ClassTeacher', true);
        $proprietorUser = $this->createUserWithRole('Owner', 'Proprietor');

        $leave = LeaveRequest::create([
            'staff_id' => $staffUser->userable_id,
            'leave_type' => 'annual',
            'start_date' => Carbon::today()->toDateString(),
            'end_date' => Carbon::today()->addDays(5)->toDateString(),
            'reason' => 'Annual vacation',
            'status' => 'pending',
        ]);

        // Approve leave
        $response = $this->actingAs($proprietorUser)->post("/admin/leaves/{$leave->id}/approve");
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'approved_by' => $proprietorUser->id,
        ]);

        // Verify staff status updated to on_leave
        $staffUser->userable->refresh();
        $this->assertEquals('on_leave', $staffUser->userable->status);
    }

    public function test_proprietor_can_reject_leave_request(): void
    {
        $staffUser = $this->createUserWithRole('Class Teacher', 'ClassTeacher', true);
        $proprietorUser = $this->createUserWithRole('Owner', 'Proprietor');

        $leave = LeaveRequest::create([
            'staff_id' => $staffUser->userable_id,
            'leave_type' => 'casual',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Casual event',
            'status' => 'pending',
        ]);

        // Reject leave
        $response = $this->actingAs($proprietorUser)->post("/admin/leaves/{$leave->id}/reject", [
            'rejection_reason' => 'Insufficient notice period provided.',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'rejection_reason' => 'Insufficient notice period provided.',
            'approved_by' => $proprietorUser->id,
        ]);
    }

    public function test_unauthorized_user_cannot_approve_leave_request(): void
    {
        $staffUser = $this->createUserWithRole('Class Teacher', 'ClassTeacher', true);
        // Accounts role is not in onboarding middleware scope, and is not in admin route middleware
        $accountsUser = $this->createUserWithRole('Bursar', 'Accounts');

        $leave = LeaveRequest::create([
            'staff_id' => $staffUser->userable_id,
            'leave_type' => 'sick',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Illness',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountsUser)->post("/admin/leaves/{$leave->id}/approve");
        $response->assertStatus(403);
    }
}
