<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\BeceMockScore;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase5CommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $proprietor;
    protected User $parentUser;
    protected Guardian $guardian;
    protected Student $student;
    protected AcademicYear $year;
    protected Term $term;
    protected SchoolClass $jhs3Class;
    protected ClassAcademicYear $classAY;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        foreach (['Proprietor', 'HeadTeacher', 'Parent', 'ClassTeacher', 'SubjectTeacher', 'Accounts', 'FrontDesk'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Proprietor user
        $this->proprietor = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hanara.edu.gh',
            'password' => bcrypt('password'),
        ]);
        $this->proprietor->assignRole('Proprietor');

        // Academic year and term
        $this->year = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'is_current' => true,
        ]);

        $this->term = Term::create([
            'academic_year_id' => $this->year->id,
            'name' => 'Term 1',
            'start_date' => '2025-09-01',
            'end_date' => '2025-12-20',
            'is_current' => true,
        ]);

        // Student and class
        $this->jhs3Class = SchoolClass::create([
            'name' => 'JHS 3',
            'level' => 'jhs',
            'display_order' => 10,
        ]);

        $this->classAY = ClassAcademicYear::create([
            'school_class_id' => $this->jhs3Class->id,
            'academic_year_id' => $this->year->id,
        ]);

        $this->student = Student::create([
            'student_id_number' => 'HAN-2025-0001',
            'first_name' => 'Kwame',
            'last_name' => 'Mensah',
            'date_of_birth' => '2011-03-15',
            'gender' => 'male',
            'admission_date' => '2022-09-01',
            'status' => 'active',
        ]);

        ClassStudent::create([
            'student_id' => $this->student->id,
            'class_academic_year_id' => $this->classAY->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        // Guardian and parent user
        $this->guardian = Guardian::create([
            'first_name' => 'Ama',
            'last_name' => 'Mensah',
            'phone' => '0244123456',
            'email' => 'ama@example.com',
            'relationship' => 'Mother',
        ]);
        $this->guardian->students()->attach($this->student->id, ['is_primary' => true]);

        $this->parentUser = User::create([
            'name' => 'Ama Mensah',
            'email' => 'ama@example.com',
            'password' => bcrypt('password'),
            'userable_type' => Guardian::class,
            'userable_id' => $this->guardian->id,
        ]);
        $this->parentUser->assignRole('Parent');
    }

    // ──────── Announcements ────────

    public function test_proprietor_can_create_announcement(): void
    {
        $response = $this->actingAs($this->proprietor)->post(route('communication.announcements.store'), [
            'title' => 'End of Term Exams',
            'body' => 'Exams begin next Monday. Study hard!',
            'type' => 'academic',
            'target_audience' => 'all',
        ]);

        $response->assertRedirect(route('communication.announcements.index'));
        $this->assertDatabaseHas('announcements', ['title' => 'End of Term Exams', 'type' => 'academic']);
    }

    public function test_announcements_index_accessible(): void
    {
        Announcement::create([
            'title' => 'Test Announcement',
            'body' => 'Test body',
            'type' => 'general',
            'target_audience' => 'all',
            'published_by' => $this->proprietor->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->proprietor)->get(route('communication.announcements.index'));
        $response->assertStatus(200);
        $response->assertSee('Test Announcement');
    }

    public function test_announcement_audience_scoping(): void
    {
        $parentsOnly = Announcement::create([
            'title' => 'For Parents',
            'body' => 'Parent-targeted',
            'type' => 'general',
            'target_audience' => 'parents',
            'published_by' => $this->proprietor->id,
            'published_at' => now(),
        ]);

        $staffOnly = Announcement::create([
            'title' => 'For Staff',
            'body' => 'Staff-targeted',
            'type' => 'general',
            'target_audience' => 'staff',
            'published_by' => $this->proprietor->id,
            'published_at' => now(),
        ]);

        // Parents should see parent and 'all' announcements but not staff
        $parentAnnouncements = Announcement::active()->forAudience('parents')->get();
        $this->assertTrue($parentAnnouncements->contains($parentsOnly));
        $this->assertFalse($parentAnnouncements->contains($staffOnly));
    }

    // ──────── SMS (Mocked) ────────

    public function test_sms_compose_accessible(): void
    {
        $response = $this->actingAs($this->proprietor)->get(route('communication.sms.compose'));
        $response->assertStatus(200);
        $response->assertSee('Compose SMS');
    }

    public function test_sms_send_to_all_parents(): void
    {
        // Mock Arkesel API
        Http::fake([
            'sms.arkesel.com/*' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($this->proprietor)->post(route('communication.sms.send'), [
            'message' => 'School resumes next Monday!',
            'recipient_type' => 'all_parents',
        ]);

        $response->assertRedirect();
    }

    // ──────── Parent Portal ────────

    public function test_parent_dashboard_shows_children(): void
    {
        $response = $this->actingAs($this->parentUser)->get(route('dashboard.parent'));
        $response->assertStatus(200);
        $response->assertSee('Kwame Mensah');
    }

    public function test_parent_can_view_child_attendance(): void
    {
        $response = $this->actingAs($this->parentUser)->get(route('parent.child.attendance', $this->student));
        $response->assertStatus(200);
        $response->assertSee('Attendance Record');
    }

    public function test_parent_can_view_child_grades(): void
    {
        $response = $this->actingAs($this->parentUser)->get(route('parent.child.grades', $this->student));
        $response->assertStatus(200);
        $response->assertSee('Academic Performance');
    }

    public function test_parent_can_view_child_fees(): void
    {
        Invoice::create([
            'invoice_number' => 'INV-2025-001',
            'student_id' => $this->student->id,
            'term_id' => $this->term->id,
            'total_amount' => 500.00,
            'amount_paid' => 200.00,
            'balance' => 300.00,
            'status' => 'partial',
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->parentUser)->get(route('parent.child.fees', $this->student));
        $response->assertStatus(200);
        $response->assertSee('300.00');
    }

    public function test_parent_cannot_access_other_students(): void
    {
        $otherStudent = Student::create([
            'student_id_number' => 'HAN-2025-0099',
            'first_name' => 'Other',
            'last_name' => 'Student',
            'date_of_birth' => '2012-01-01',
            'gender' => 'female',
            'admission_date' => '2022-09-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->parentUser)->get(route('parent.child.attendance', $otherStudent));
        $response->assertStatus(403);
    }

    // ──────── BECE Mock Scores ────────

    public function test_bece_grade_conversion(): void
    {
        $this->assertEquals(1, BeceMockScore::rawScoreToBECEGrade(85));
        $this->assertEquals(2, BeceMockScore::rawScoreToBECEGrade(75));
        $this->assertEquals(3, BeceMockScore::rawScoreToBECEGrade(67));
        $this->assertEquals(6, BeceMockScore::rawScoreToBECEGrade(52));
        $this->assertEquals(9, BeceMockScore::rawScoreToBECEGrade(20));
    }

    public function test_bece_aggregate_calculation(): void
    {
        // Create 7 subjects and mock scores
        $subjectNames = ['English', 'Mathematics', 'Int. Science', 'Social Studies', 'ICT', 'French', 'RME'];
        $grades = [2, 3, 1, 4, 5, 7, 6]; // Best 6 = 2+3+1+4+5+6 = 21

        foreach ($subjectNames as $i => $name) {
            $subject = Subject::create(['name' => $name, 'level' => 'jhs']);
            BeceMockScore::create([
                'student_id' => $this->student->id,
                'subject_id' => $subject->id,
                'class_academic_year_id' => $this->classAY->id,
                'mock_exam_label' => 'Mock 1',
                'raw_score' => 80 - ($grades[$i] * 5),
                'bece_grade' => $grades[$i],
                'recorded_by' => $this->proprietor->id,
            ]);
        }

        $result = BeceMockScore::calculateAggregate($this->student->id, $this->classAY->id, 'Mock 1');

        $this->assertNotNull($result);
        $this->assertEquals(21, $result['aggregate']); // Best 6: 1+2+3+4+5+6 = 21
        $this->assertFalse($result['is_at_risk']); // 21 <= 36
        $this->assertEquals(7, $result['total_subjects']);
    }

    public function test_bece_at_risk_flagging(): void
    {
        $subjects = ['English', 'Mathematics', 'Int. Science', 'Social Studies', 'ICT', 'French'];
        $grades = [7, 8, 6, 7, 8, 9]; // Best 6 = 7+8+6+7+8+9 = 45

        foreach ($subjects as $i => $name) {
            $subject = Subject::create(['name' => $name, 'level' => 'jhs']);
            BeceMockScore::create([
                'student_id' => $this->student->id,
                'subject_id' => $subject->id,
                'class_academic_year_id' => $this->classAY->id,
                'mock_exam_label' => 'Mock 1',
                'raw_score' => 50 - ($grades[$i] * 3),
                'bece_grade' => $grades[$i],
                'recorded_by' => $this->proprietor->id,
            ]);
        }

        $result = BeceMockScore::calculateAggregate($this->student->id, $this->classAY->id, 'Mock 1');
        $this->assertTrue($result['is_at_risk']); // 45 > 36
    }

    public function test_bece_score_entry(): void
    {
        $subject = Subject::create(['name' => 'English', 'level' => 'jhs']);

        $response = $this->actingAs($this->proprietor)->post(route('academics.bece.store-scores'), [
            'class_academic_year_id' => $this->classAY->id,
            'mock_exam_label' => 'Mock 1',
            'scores' => [
                [
                    'student_id' => $this->student->id,
                    'subject_id' => $subject->id,
                    'raw_score' => 72,
                ],
            ],
        ]);

        $response->assertRedirect(route('academics.bece.index'));
        $this->assertDatabaseHas('bece_mock_scores', [
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'raw_score' => 72,
            'bece_grade' => 2, // 70-79 = grade 2
        ]);
    }

    public function test_bece_dashboard_accessible(): void
    {
        $response = $this->actingAs($this->proprietor)->get(route('academics.bece.index'));
        $response->assertStatus(200);
        $response->assertSee('BECE Readiness');
    }

    // ──────── Transcripts ────────

    public function test_transcript_generation(): void
    {
        $response = $this->actingAs($this->proprietor)->get(route('students.transcript', $this->student));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertDatabaseHas('transcripts', [
            'student_id' => $this->student->id,
            'type' => 'transcript',
        ]);
    }

    public function test_testimonial_generation(): void
    {
        $response = $this->actingAs($this->proprietor)->get(route('students.testimonial', $this->student));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertDatabaseHas('transcripts', [
            'student_id' => $this->student->id,
            'type' => 'testimonial',
        ]);
    }

    // ──────── Emergency Broadcast ────────

    public function test_emergency_broadcast(): void
    {
        Http::fake([
            'sms.arkesel.com/*' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($this->proprietor)->post(route('communication.emergency-broadcast'), [
            'message' => 'School closes early today due to flooding.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'type' => 'emergency',
            'is_pinned' => true,
        ]);
    }
}
