<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\ClassSubjectTeacher;
use App\Models\GradeScale;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TimetableSlot;
use App\Models\User;
use App\Services\AcademicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'AcademicYearSeeder']);
        $this->artisan('db:seed', ['--class' => 'SchoolClassSeeder']);
        $this->artisan('db:seed', ['--class' => 'SubjectSeeder']);
        $this->artisan('db:seed', ['--class' => 'SettingsSeeder']);
        $this->artisan('db:seed', ['--class' => 'GradeScaleSeeder']);

        // Create current term
        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            Term::create([
                'academic_year_id' => $currentYear->id,
                'name' => 'Term 1',
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'is_current' => true,
            ]);
        }
    }

    private function createUserWithRole(string $position, string $roleName)
    {
        $staff = Staff::create([
            'staff_id_number' => 'STF-' . rand(1000, 9999),
            'first_name' => 'Test',
            'last_name' => 'User',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone' => '+233' . rand(100000000, 999999999),
            'date_joined' => '2020-01-01',
            'position' => $position,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Test ' . $roleName,
            'email' => strtolower($roleName) . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Staff::class,
            'userable_id' => $staff->id,
            'must_change_password' => false,
        ]);

        $user->assignRole($roleName);
        return $user;
    }

    public function test_gradebook_entry_and_validation(): void
    {
        $user = $this->createUserWithRole('Subject Teacher', 'SubjectTeacher');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $schoolClass = SchoolClass::where('name', 'P1')->first();
        $subject = Subject::first();

        $classAcYear = ClassAcademicYear::where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        // Assign teacher to class-subject
        ClassSubjectTeacher::create([
            'class_academic_year_id' => $classAcYear->id,
            'subject_id' => $subject->id,
            'staff_id' => $user->userable_id,
        ]);

        // Also assign in teacher_assignments for RBAC
        \App\Models\TeacherAssignment::create([
            'user_id' => $user->id,
            'class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'is_form_teacher' => false,
        ]);

        // Create Assessment Component
        $component = AssessmentComponent::create([
            'name' => 'Class Assessment',
            'weight' => 40.00,
            'academic_year_id' => $currentYear->id,
            'level' => 'primary',
            'max_score' => 30,
        ]);

        // Create student
        $student = Student::create([
            'student_id_number' => 'HAN-2026-9001',
            'first_name' => 'Kofi',
            'last_name' => 'Annan',
            'date_of_birth' => '2015-05-05',
            'gender' => 'male',
            'admission_date' => '2025-09-08',
        ]);

        // Enroll student
        ClassStudent::create([
            'student_id' => $student->id,
            'class_academic_year_id' => $classAcYear->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        // Test entering valid score
        Livewire::actingAs($user)
            ->test(\App\Livewire\Academics\Gradebook::class)
            ->set('selectedClassId', $classAcYear->id)
            ->set('selectedSubjectId', $subject->id)
            ->set('selectedComponentId', $component->id)
            ->call('loadScores')
            ->set("scores.{$student->id}", 25)
            ->call('saveScore', $student->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('assessment_scores', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'assessment_component_id' => $component->id,
            'class_academic_year_id' => $classAcYear->id,
            'score' => 25,
        ]);

        // Test entering invalid score (> max_score)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Academics\Gradebook::class)
            ->set('selectedClassId', $classAcYear->id)
            ->set('selectedSubjectId', $subject->id)
            ->set('selectedComponentId', $component->id)
            ->call('loadScores')
            ->set("scores.{$student->id}", 35) // exceeds max 30
            ->call('saveScore', $student->id)
            ->assertHasErrors("scores.{$student->id}");
    }

    public function test_report_card_pdf_generation(): void
    {
        $user = $this->createUserWithRole('Head Teacher', 'HeadTeacher');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $schoolClass = SchoolClass::where('name', 'P1')->first();
        $subject = Subject::first();

        $classAcYear = ClassAcademicYear::where('school_class_id', $schoolClass->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        // Create student
        $student = Student::create([
            'student_id_number' => 'HAN-2026-9002',
            'first_name' => 'Ama',
            'last_name' => 'Kofi',
            'date_of_birth' => '2015-06-06',
            'gender' => 'female',
            'admission_date' => '2025-09-08',
        ]);

        // Enroll student
        ClassStudent::create([
            'student_id' => $student->id,
            'class_academic_year_id' => $classAcYear->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        // Create Assessment Components (CA 40%, Exam 60%)
        $caComponent = AssessmentComponent::create([
            'name' => 'Class Assessment',
            'weight' => 40.00,
            'academic_year_id' => $currentYear->id,
            'level' => 'primary',
            'max_score' => 50,
        ]);

        $examComponent = AssessmentComponent::create([
            'name' => 'Exam',
            'weight' => 60.00,
            'academic_year_id' => $currentYear->id,
            'level' => 'primary',
            'max_score' => 100,
        ]);

        // Record scores
        AssessmentScore::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'assessment_component_id' => $caComponent->id,
            'class_academic_year_id' => $classAcYear->id,
            'score' => 40, // 40/50 * 40 = 32%
            'recorded_by' => $user->id,
        ]);

        AssessmentScore::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'assessment_component_id' => $examComponent->id,
            'class_academic_year_id' => $classAcYear->id,
            'score' => 80, // 80/100 * 60 = 48%
            'recorded_by' => $user->id,
        ]);

        // Subject total should be 32 + 48 = 80%. In Primary GradeScale, 80% maps to 'A' (Excellent)
        $service = new AcademicService();
        $this->assertEquals(80.00, $service->calculateSubjectTotal($student->id, $subject->id, $classAcYear->id));
        $this->assertEquals('A', $service->getGrade(80.00, 'primary'));

        // Request PDF report card
        $response = $this->actingAs($user)->get(route('academics.report-card', $student));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_timetable_clash_detection(): void
    {
        $user = $this->createUserWithRole('Proprietor', 'Proprietor');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $schoolClass1 = SchoolClass::where('name', 'P1')->first();
        $schoolClass2 = SchoolClass::where('name', 'P2')->first();
        $subject1 = Subject::orderBy('id')->first();
        $subject2 = Subject::orderBy('id', 'desc')->first();

        $classAcYear1 = ClassAcademicYear::where('school_class_id', $schoolClass1->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        $classAcYear2 = ClassAcademicYear::where('school_class_id', $schoolClass2->id)
            ->where('academic_year_id', $currentYear->id)
            ->first();

        // Create another teacher
        $teacher = Staff::create([
            'staff_id_number' => 'STF-8888',
            'first_name' => 'Secondary',
            'last_name' => 'Teacher',
            'date_of_birth' => '1988-08-08',
            'gender' => 'female',
            'phone' => '+233888888888',
            'date_joined' => '2020-01-01',
            'position' => 'Subject Teacher',
        ]);

        // Assign teacher to subjects
        ClassSubjectTeacher::create([
            'class_academic_year_id' => $classAcYear1->id,
            'subject_id' => $subject1->id,
            'staff_id' => $user->userable_id,
        ]);

        ClassSubjectTeacher::create([
            'class_academic_year_id' => $classAcYear2->id,
            'subject_id' => $subject2->id,
            'staff_id' => $user->userable_id, // Same teacher for class 2
        ]);

        // Add slot to class 1: Monday 09:00 - 10:00
        TimetableSlot::create([
            'class_academic_year_id' => $classAcYear1->id,
            'subject_id' => $subject1->id,
            'staff_id' => $user->userable_id,
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room' => 'Room 1',
        ]);

        $service = new AcademicService();

        // Clash case 1: Class 1 busy at overlapping time
        $this->assertTrue(
            $service->hasClash($classAcYear1->id, $teacher->id, 'monday', '09:30', '10:30')
        );

        // Clash case 2: Teacher busy in Class 2 at overlapping time
        $this->assertTrue(
            $service->hasClash($classAcYear2->id, $user->userable_id, 'monday', '09:30', '10:30')
        );

        // Non-clash case: Class 1 and Teacher free at another time or day
        $this->assertFalse(
            $service->hasClash($classAcYear1->id, $user->userable_id, 'monday', '10:00', '11:00')
        );

        $this->assertFalse(
            $service->hasClash($classAcYear1->id, $user->userable_id, 'tuesday', '09:00', '10:00')
        );
    }
}
