<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentIdGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_id_generates_correct_initial_format(): void
    {
        $year = date('Y');
        $expectedId = "HAN-{$year}-0001";

        $generatedId = Student::generateStudentId();

        $this->assertEquals($expectedId, $generatedId);
    }

    public function test_student_id_increments_sequentially(): void
    {
        $year = date('Y');

        // Create student 1 manually
        Student::create([
            'student_id_number' => "HAN-{$year}-0001",
            'first_name' => 'First',
            'last_name' => 'Student',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        $generatedId = Student::generateStudentId();
        $this->assertEquals("HAN-{$year}-0002", $generatedId);
    }

    public function test_student_id_includes_soft_deleted_students_in_sequence(): void
    {
        $year = date('Y');

        $student1 = Student::create([
            'student_id_number' => "HAN-{$year}-0001",
            'first_name' => 'First',
            'last_name' => 'Student',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        $student2 = Student::create([
            'student_id_number' => "HAN-{$year}-0002",
            'first_name' => 'Second',
            'last_name' => 'Student',
            'date_of_birth' => '2015-06-06',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        // Soft delete student 2
        $student2->delete();

        // The generator should still see student 2 and output 0003
        $generatedId = Student::generateStudentId();
        $this->assertEquals("HAN-{$year}-0003", $generatedId);
    }

    public function test_staff_id_generates_correct_initial_format(): void
    {
        $year = date('Y');
        $expectedId = "STF-{$year}-0001";

        $generatedId = Staff::generateStaffId();

        $this->assertEquals($expectedId, $generatedId);
    }

    public function test_staff_id_increments_sequentially(): void
    {
        $year = date('Y');

        Staff::create([
            'staff_id_number' => "STF-{$year}-0001",
            'first_name' => 'First',
            'last_name' => 'Staff',
            'date_of_birth' => '1985-05-05',
            'gender' => 'male',
            'phone' => '+233000000000',
            'date_joined' => '2020-01-01',
            'position' => 'Teacher',
        ]);

        $generatedId = Staff::generateStaffId();
        $this->assertEquals("STF-{$year}-0002", $generatedId);
    }
}
