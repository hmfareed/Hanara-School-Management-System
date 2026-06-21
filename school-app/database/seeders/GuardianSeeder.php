<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        $maleFirstNames = ['Kofi', 'Kwame', 'Kweku', 'Kwabena', 'Yaw', 'Kwadwo', 'Emmanuel', 'Samuel', 'Daniel', 'Joseph', 'George', 'Charles', 'Michael'];
        $femaleFirstNames = ['Ama', 'Efua', 'Abena', 'Akua', 'Yaa', 'Akosua', 'Adwoa', 'Esi', 'Mary', 'Elizabeth', 'Rebecca', 'Sarah', 'Grace'];
        $occupations = ['Teacher', 'Trader', 'Civil Servant', 'Nurse', 'Engineer', 'Banker', 'Self-employed', 'Doctor', 'Businessman', 'Businesswoman'];

        $students = Student::all();
        $studentCount = $students->count();

        // Let's create families/sibling groups.
        // We will group students into ~30 sibling groups.
        $studentsArray = $students->shuffle()->toArray();
        
        $families = [];
        $index = 0;
        
        while ($index < $studentCount) {
            $groupSize = rand(1, 3); // 1 to 3 siblings
            $siblingGroup = [];
            for ($s = 0; $s < $groupSize && $index < $studentCount; $s++) {
                $siblingGroup[] = $studentsArray[$index++];
            }
            $families[] = $siblingGroup;
        }

        foreach ($families as $family) {
            // Use the last name of the first sibling as the family name
            $familyName = $family[0]['last_name'];
            $familyAddress = $family[0]['address'];

            // 1. Primary Guardian (e.g. Father or Mother)
            $isFather = rand(0, 1) === 0;
            $g1Gender = $isFather ? 'male' : 'female';
            $g1FirstName = $g1Gender === 'male' ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
            $g1Relation = $g1Gender === 'male' ? 'Father' : 'Mother';

            $guardian1 = Guardian::create([
                'first_name' => $g1FirstName,
                'last_name' => $familyName,
                'phone' => '+233' . rand(24, 29) . rand(1000000, 9999999),
                'email' => strtolower($g1FirstName . '.' . $familyName) . rand(10, 99) . '@example.com',
                'relationship' => $g1Relation,
                'occupation' => $occupations[array_rand($occupations)],
                'address' => $familyAddress,
                'is_emergency_contact' => true,
            ]);

            // Link all siblings to Primary Guardian
            foreach ($family as $student) {
                DB::table('guardian_student')->insert([
                    'guardian_id' => $guardian1->id,
                    'student_id' => $student['id'],
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Secondary Guardian (optional, e.g. Mother/Father or Uncle/Aunt) in ~60% of families
            if (rand(1, 10) <= 6) {
                $g2Relation = ($g1Relation === 'Father') ? 'Mother' : 'Father';
                $g2Gender = ($g2Relation === 'Father') ? 'male' : 'female';
                $g2FirstName = $g2Gender === 'male' ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];

                $guardian2 = Guardian::create([
                    'first_name' => $g2FirstName,
                    'last_name' => $familyName,
                    'phone' => '+233' . rand(24, 29) . rand(1000000, 9999999),
                    'email' => strtolower($g2FirstName . '.' . $familyName) . rand(10, 99) . '@example.com',
                    'relationship' => $g2Relation,
                    'occupation' => $occupations[array_rand($occupations)],
                    'address' => $familyAddress,
                    'is_emergency_contact' => false,
                ]);

                // Link siblings to Secondary Guardian
                foreach ($family as $student) {
                    DB::table('guardian_student')->insert([
                        'guardian_id' => $guardian2->id,
                        'student_id' => $student['id'],
                        'is_primary' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
