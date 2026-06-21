<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\ClassAcademicYear;
use App\Models\ClassStudent;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $maleFirstNames = ['Kwadwo', 'Kwabena', 'Kwaku', 'Yaw', 'Kofi', 'Kwame', 'Kweku', 'Ekow', 'Emmanuel', 'Samuel', 'Daniel', 'Joseph', 'Isaac', 'David', 'Prince', 'Bright', 'Nana', 'John', 'Kelvin', 'Jojo'];
        $femaleFirstNames = ['Abena', 'Akua', 'Yaa', 'Afua', 'Ama', 'Akosua', 'Adwoa', 'Esi', 'Mary', 'Elizabeth', 'Sarah', 'Rebecca', 'Grace', 'Mercy', 'Comfort', 'Rita', 'Bernice', 'Joy', 'Theresa', 'Patricia'];
        $lastNames = ['Mensah', 'Osei', 'Owusu', 'Boateng', 'Appiah', 'Gyan', 'Ansah', 'Addo', 'Agyemang', 'Asante', 'Koomson', 'Baidoo', 'Essien', 'Quansah', 'Arthur', 'Donkor', 'Yeboah', 'Oppong', 'Bempah', 'Aidoo', 'Acquah', 'Tetteh', 'Annan'];

        $classes = ClassAcademicYear::with('schoolClass')->get();

        // Target student distribution per class level:
        // Nursery: 2 classes (Nursery 1, Nursery 2) -> 3 students each = 6
        // KG: 2 classes (KG1, KG2) -> 4 students each = 8
        // Primary: 6 classes (P1 to P6) -> 6 students each = 36
        // JHS: 3 classes (JHS1 to JHS3) -> 5 students each = 15
        // Total = 65 students.

        $currentYear = date('Y');
        $studentCounter = 1;

        foreach ($classes as $classAcYear) {
            $className = $classAcYear->schoolClass->name;
            $level = $classAcYear->schoolClass->level;

            $numStudents = 0;
            $baseAge = 0;

            if ($level === 'nursery') {
                $numStudents = 3;
                $baseAge = 3; // 2-4 years
            } elseif ($level === 'kindergarten') {
                $numStudents = 4;
                $baseAge = 5; // 4-6 years
            } elseif ($level === 'primary') {
                $numStudents = 6;
                // P1 = 6, P2 = 7, etc.
                preg_match('/\d+/', $className, $matches);
                $pNumber = isset($matches[0]) ? (int)$matches[0] : 1;
                $baseAge = 5 + $pNumber; // 6 to 11 years
            } elseif ($level === 'jhs') {
                $numStudents = 5;
                preg_match('/\d+/', $className, $matches);
                $jNumber = isset($matches[0]) ? (int)$matches[0] : 1;
                $baseAge = 11 + $jNumber; // 12 to 14 years
            }

            for ($i = 0; $i < $numStudents; $i++) {
                $gender = (rand(0, 1) === 0) ? 'male' : 'female';
                $firstName = ($gender === 'male') ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $otherName = (rand(0, 3) === 0) ? (($gender === 'male') ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)]) : null;
                
                // Realistic date of birth based on level
                $age = $baseAge + rand(-1, 1);
                if ($age < 2) $age = 2;
                $dob = date('Y-m-d', strtotime("-{$age} years -" . rand(1, 365) . " days"));

                $student = Student::create([
                    'student_id_number' => sprintf('HAN-%s-%04d', $currentYear, $studentCounter++),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'other_names' => $otherName,
                    'date_of_birth' => $dob,
                    'gender' => $gender,
                    'photo' => null,
                    'address' => rand(10, 99) . ' Block ' . chr(rand(65, 75)) . ', Takoradi',
                    'nationality' => 'Ghanaian',
                    'religion' => ['Christian', 'Christian', 'Christian', 'Muslim', 'None'][rand(0, 4)],
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+'][rand(0, 3)],
                    'medical_notes' => (rand(0, 9) === 0) ? 'Mild asthma.' : null,
                    'admission_date' => date('Y-m-d', strtotime('-' . rand(0, 2) . ' years -' . rand(1, 100) . ' days')),
                    'status' => 'active',
                ]);

                // Enroll student in this class
                ClassStudent::create([
                    'student_id' => $student->id,
                    'class_academic_year_id' => $classAcYear->id,
                    'enrolled_at' => $student->admission_date,
                    'status' => 'enrolled',
                ]);
            }
        }
    }
}
