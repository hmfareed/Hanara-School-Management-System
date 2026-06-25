<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\ClassAcademicYear;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // Only superadmin staff (Proprietor & Head Teacher) are seeded.
        // All other staff roles (Bursar/Accounts, Front Desk, Supervisor, etc.)
        // must self-register via the staff registration flow using a PIN code.
        $staffData = [
            [
                'first_name' => 'Nana Akua',
                'last_name' => 'Mensah',
                'other_names' => null,
                'date_of_birth' => '1975-04-12',
                'gender' => 'female',
                'phone' => '+233244123456',
                'email' => 'proprietor@hanara.edu.gh',
                'address' => '12 Anaji Estate, Takoradi',
                'qualification' => 'MEd Educational Leadership',
                'date_joined' => '2015-09-01',
                'position' => 'Proprietor',
                'status' => 'active',
            ],
            [
                'first_name' => 'Kofi',
                'last_name' => 'Addo',
                'other_names' => 'Gyasi',
                'date_of_birth' => '1980-08-22',
                'gender' => 'male',
                'phone' => '+233244654321',
                'email' => 'headteacher@hanara.edu.gh',
                'address' => '34 Airport Ridge, Takoradi',
                'qualification' => 'BEd Primary Education',
                'date_joined' => '2017-01-15',
                'position' => 'Head Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Adwoa',
                'last_name' => 'Osei',
                'other_names' => null,
                'date_of_birth' => '1985-05-15',
                'gender' => 'female',
                'phone' => '+233244111222',
                'email' => 'teacher@hanara.edu.gh',
                'address' => '45 Beach Road, Takoradi',
                'qualification' => 'Diploma in Basic Education',
                'date_joined' => '2019-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Kwame',
                'last_name' => 'Mensah',
                'other_names' => null,
                'date_of_birth' => '1988-11-20',
                'gender' => 'male',
                'phone' => '+233244333444',
                'email' => 'subject@hanara.edu.gh',
                'address' => '78 Fijai, Takoradi',
                'qualification' => 'BEd Mathematics',
                'date_joined' => '2020-09-01',
                'position' => 'Subject Teacher',
                'status' => 'active',
            ],
        ];

        foreach ($staffData as $data) {
            $data['staff_id_number'] = Staff::generateStaffId();
            $staff = Staff::create($data);
        }

        // Assign Adwoa Osei (Class Teacher) as form teacher of P1
        $teacherStaff = Staff::where('position', 'Class Teacher')->first();
        if ($teacherStaff) {
            $classAY = ClassAcademicYear::whereHas('schoolClass', function ($q) {
                $q->where('name', 'P1');
            })->first();
            if ($classAY) {
                $classAY->update(['class_teacher_id' => $teacherStaff->id]);
            }
        }

        // Assign Kwame Mensah (Subject Teacher) to subjects
        $subjectStaff = Staff::where('position', 'Subject Teacher')->first();
        if ($subjectStaff) {
            $classAY1 = ClassAcademicYear::whereHas('schoolClass', function ($q) {
                $q->where('name', 'P1');
            })->first();
            $classAY2 = ClassAcademicYear::whereHas('schoolClass', function ($q) {
                $q->where('name', 'P2');
            })->first();

            $math = \App\Models\Subject::where('code', 'MATH')->first();
            $eng = \App\Models\Subject::where('code', 'ENG')->first();

            if ($classAY1 && $math) {
                \App\Models\ClassSubjectTeacher::create([
                    'class_academic_year_id' => $classAY1->id,
                    'subject_id' => $math->id,
                    'staff_id' => $subjectStaff->id,
                ]);
            }

            if ($classAY2 && $eng) {
                \App\Models\ClassSubjectTeacher::create([
                    'class_academic_year_id' => $classAY2->id,
                    'subject_id' => $eng->id,
                    'staff_id' => $subjectStaff->id,
                ]);
            }
        }
    }
}
