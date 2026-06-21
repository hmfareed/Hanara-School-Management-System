<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkStudentImportTest extends TestCase
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

    public function test_proprietor_can_import_roster_and_link_siblings(): void
    {
        $admin = $this->createUserWithRole('Proprietor', 'Proprietor');

        // CSV content containing two siblings with the same guardian details
        $csvContent = "first_name,last_name,other_names,date_of_birth,gender,class,guardian_first_name,guardian_last_name,guardian_phone,guardian_relationship\n"
                    . "Kojo,Mensah,,2015-05-05,male,P1,Kofi,Mensah,+233241111111,Father\n"
                    . "Abena,Mensah,Osei,2017-08-10,female,P1,Kofi,Mensah,+233241111111,Father\n";

        $file = UploadedFile::fake()->createWithContent('roster.csv', $csvContent);

        $response = $this->actingAs($admin)->post('/students/import', [
            'roster_file' => $file,
        ]);

        $response->assertRedirect('/students');
        $response->assertSessionHas('success');

        // 1. Verify two students were created
        $kojo = Student::where('first_name', 'Kojo')->where('last_name', 'Mensah')->first();
        $abena = Student::where('first_name', 'Abena')->where('last_name', 'Mensah')->first();

        $this->assertNotNull($kojo);
        $this->assertNotNull($abena);

        // 2. Verify sequential Student IDs
        $year = date('Y');
        $this->assertEquals("HAN-{$year}-0001", $kojo->student_id_number);
        $this->assertEquals("HAN-{$year}-0002", $abena->student_id_number);

        // 3. Verify exactly ONE guardian record was created (de-duplication)
        $guardians = Guardian::where('phone', '+233241111111')->get();
        $this->assertCount(1, $guardians);
        
        $guardian = $guardians->first();
        $this->assertEquals('Kofi', $guardian->first_name);

        // 4. Verify both students are linked to the same guardian
        $this->assertTrue($kojo->guardians->contains($guardian->id));
        $this->assertTrue($abena->guardians->contains($guardian->id));

        // 5. Verify enrollment in P1
        $kojoEnroll = $kojo->currentClassEnrollment();
        $abenaEnroll = $abena->currentClassEnrollment();

        $this->assertNotNull($kojoEnroll);
        $this->assertNotNull($abenaEnroll);
        $this->assertEquals('P1', $kojoEnroll->classAcademicYear->schoolClass->name);
        $this->assertEquals('P1', $abenaEnroll->classAcademicYear->schoolClass->name);
    }
}
