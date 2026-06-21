<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\ClassAcademicYear;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
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
                'first_name' => 'Kwame',
                'last_name' => 'Boateng',
                'other_names' => null,
                'date_of_birth' => '1985-11-05',
                'gender' => 'male',
                'phone' => '+233201234567',
                'email' => 'accounts@hanara.edu.gh',
                'address' => '45 Beach Road, Takoradi',
                'qualification' => 'BCom Accounting',
                'date_joined' => '2018-06-01',
                'position' => 'Bursar',
                'status' => 'active',
            ],
            [
                'first_name' => 'Ama',
                'last_name' => 'Serwaa',
                'other_names' => 'Osei',
                'date_of_birth' => '1992-02-18',
                'gender' => 'female',
                'phone' => '+233277987654',
                'email' => 'frontdesk@hanara.edu.gh',
                'address' => '56 Fijai, Takoradi',
                'qualification' => 'HND Secretaryship',
                'date_joined' => '2020-10-01',
                'position' => 'Front Desk Officer',
                'status' => 'active',
            ],
            // Teachers (6)
            [
                'first_name' => 'Ekow',
                'last_name' => 'Eshun',
                'other_names' => null,
                'date_of_birth' => '1988-05-14',
                'gender' => 'male',
                'phone' => '+233243111222',
                'email' => 'ekow.eshun@hanara.edu.gh',
                'address' => '15 Effia Kuma, Takoradi',
                'qualification' => 'Diploma in Basic Education',
                'date_joined' => '2019-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Abena',
                'last_name' => 'Ofori',
                'other_names' => 'Asante',
                'date_of_birth' => '1990-07-28',
                'gender' => 'female',
                'phone' => '+233243333444',
                'email' => 'abena.ofori@hanara.edu.gh',
                'address' => '8 Kwesimintsim, Takoradi',
                'qualification' => 'BEd Early Childhood',
                'date_joined' => '2020-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Yaw',
                'last_name' => 'Appiah',
                'other_names' => null,
                'date_of_birth' => '1987-03-09',
                'gender' => 'male',
                'phone' => '+233243555666',
                'email' => 'yaw.appiah@hanara.edu.gh',
                'address' => '24 Kansaworado, Takoradi',
                'qualification' => 'Diploma in Education',
                'date_joined' => '2018-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Efua',
                'last_name' => 'Ansah',
                'other_names' => 'Baidoo',
                'date_of_birth' => '1993-12-01',
                'gender' => 'female',
                'phone' => '+233243777888',
                'email' => 'efua.ansah@hanara.edu.gh',
                'address' => '19 Tanokrom, Takoradi',
                'qualification' => 'BEd Mathematics',
                'date_joined' => '2021-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Kweku',
                'last_name' => 'Mensah',
                'other_names' => 'Agyei',
                'date_of_birth' => '1989-10-10',
                'gender' => 'male',
                'phone' => '+233243999000',
                'email' => 'kweku.mensah@hanara.edu.gh',
                'address' => '30 New Takoradi, Takoradi',
                'qualification' => 'BEd Science',
                'date_joined' => '2019-09-01',
                'position' => 'Subject Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Akosua',
                'last_name' => 'Dapaah',
                'other_names' => null,
                'date_of_birth' => '1995-01-25',
                'gender' => 'female',
                'phone' => '+233244888999',
                'email' => 'akosua.dapaah@hanara.edu.gh',
                'address' => '12 West Line, Takoradi',
                'qualification' => 'Diploma in Basic Education',
                'date_joined' => '2022-09-01',
                'position' => 'Class Teacher',
                'status' => 'active',
            ],
        ];

        foreach ($staffData as $data) {
            $data['staff_id_number'] = Staff::generateStaffId();
            $staff = Staff::create($data);

            // Assign some teachers to classes for testing
            if ($staff->position === 'Class Teacher') {
                // Find a class-academic-year without a teacher
                $classAcYear = ClassAcademicYear::whereNull('class_teacher_id')->first();
                if ($classAcYear) {
                    $classAcYear->update(['class_teacher_id' => $staff->id]);
                }
            }
        }
    }
}
