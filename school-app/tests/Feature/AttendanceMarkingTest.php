<?php

namespace Tests\Feature;

use Carbon\Carbon;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\ClassStudent;
use App\Models\ClassAcademicYear;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\MarkAttendance;
use Tests\TestCase;

class AttendanceMarkingTest extends TestCase
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

    public function test_teacher_can_view_attendance_workspace(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher');
        $schoolClass = SchoolClass::where('name', 'P1')->first();
        \App\Models\TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => $schoolClass->id,
            'subject_id' => null,
            'is_form_teacher' => true,
        ]);
        $response = $this->actingAs($user)->get('/attendance/mark');

        $response->assertStatus(200);
        $response->assertSeeLivewire(MarkAttendance::class);
    }

    public function test_daily_attendance_marking_grid_persists_statuses(): void
    {
        $user = $this->createUserWithRole('Class Teacher', 'ClassTeacher');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $schoolClass = SchoolClass::where('name', 'P1')->first();

        \App\Models\TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => $schoolClass->id,
            'subject_id' => null,
            'is_form_teacher' => true,
        ]);

        // Create student 1
        $student1 = Student::create([
            'student_id_number' => 'HAN-2026-0001',
            'first_name' => 'Kofi',
            'last_name' => 'Annan',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        // Create student 2
        $student2 = Student::create([
            'student_id_number' => 'HAN-2026-0002',
            'first_name' => 'Ama',
            'last_name' => 'Kofi',
            'date_of_birth' => '2016-06-06',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        $classAcYear = ClassAcademicYear::where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        // Enroll students in P1
        ClassStudent::create([
            'student_id' => $student1->id,
            'class_academic_year_id' => $classAcYear->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        ClassStudent::create([
            'student_id' => $student2->id,
            'class_academic_year_id' => $classAcYear->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        // Mark attendance via Livewire
        $testDate = Carbon::today()->toDateString();
        Livewire::actingAs($user)
            ->test(MarkAttendance::class)
            ->set('selectedClassId', $schoolClass->id)
            ->set('date', $testDate)
            ->call('loadStudents')
            ->set("statuses.{$student1->id}", 'present')
            ->set("remarks.{$student1->id}", 'On time')
            ->set("statuses.{$student2->id}", 'absent')
            ->set("remarks.{$student2->id}", 'Sick leave')
            ->call('save')
            ->assertSet('saved', true);

        // Verify database records
        $this->assertTrue(
            Attendance::where('student_id', $student1->id)
                ->where('class_academic_year_id', $classAcYear->id)
                ->whereDate('date', $testDate)
                ->where('status', 'present')
                ->where('remarks', 'On time')
                ->exists()
        );

        $this->assertTrue(
            Attendance::where('student_id', $student2->id)
                ->where('class_academic_year_id', $classAcYear->id)
                ->whereDate('date', $testDate)
                ->where('status', 'absent')
                ->where('remarks', 'Sick leave')
                ->exists()
        );

        // Test updating (idempotency/prevention of duplicates)
        Livewire::actingAs($user)
            ->test(MarkAttendance::class)
            ->set('selectedClassId', $schoolClass->id)
            ->set('date', $testDate)
            ->call('loadStudents')
            ->set("statuses.{$student2->id}", 'late') // Change Ama to late
            ->set("remarks.{$student2->id}", 'Late due to rain')
            ->call('save');

        // Total records in attendances table should still be exactly 2
        $this->assertEquals(2, Attendance::whereDate('date', $testDate)->count());

        $this->assertTrue(
            Attendance::where('student_id', $student2->id)
                ->whereDate('date', $testDate)
                ->where('status', 'late')
                ->where('remarks', 'Late due to rain')
                ->exists()
        );

        // Switch to another date (e.g. tomorrow)
        $tomorrowDate = Carbon::tomorrow()->toDateString();
        $lw = Livewire::actingAs($user)
            ->test(MarkAttendance::class)
            ->set('selectedClassId', $schoolClass->id)
            ->set('date', $testDate)
            ->call('loadStudents');

        // Verify loaded status for today is correct
        $this->assertEquals('present', $lw->get("statuses.{$student1->id}"));
        $this->assertEquals('late', $lw->get("statuses.{$student2->id}"));

        // Switch date to tomorrow
        $lw->set('date', $tomorrowDate); // This triggers updatedDate -> loadStudents
        
        // On tomorrow, it should be default (present)
        $this->assertEquals('present', $lw->get("statuses.{$student1->id}"));
        $this->assertEquals('present', $lw->get("statuses.{$student2->id}"));

        // Switch date back to today
        $lw->set('date', $testDate); // This triggers updatedDate -> loadStudents

        // On today, it should have the saved values
        $this->assertEquals('present', $lw->get("statuses.{$student1->id}"));
        $this->assertEquals('late', $lw->get("statuses.{$student2->id}"));
    }
}
