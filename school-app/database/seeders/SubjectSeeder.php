<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // All levels / general
            ['name' => 'English Language', 'code' => 'ENG', 'level' => null, 'is_elective' => false],
            ['name' => 'Mathematics', 'code' => 'MATH', 'level' => null, 'is_elective' => false],
            ['name' => 'Physical Education', 'code' => 'PE', 'level' => null, 'is_elective' => false],

            // Nursery / KG specific
            ['name' => 'Numeracy', 'code' => 'NUM', 'level' => 'nursery', 'is_elective' => false],
            ['name' => 'Literacy', 'code' => 'LIT', 'level' => 'nursery', 'is_elective' => false],
            ['name' => 'Creative Activities', 'code' => 'CRA', 'level' => 'kindergarten', 'is_elective' => false],
            ['name' => 'Environmental Studies', 'code' => 'ENV', 'level' => 'kindergarten', 'is_elective' => false],

            // Primary specific
            ['name' => 'Science', 'code' => 'SCI', 'level' => 'primary', 'is_elective' => false],
            ['name' => 'History', 'code' => 'HIS', 'level' => 'primary', 'is_elective' => false],
            ['name' => 'Our World Our People', 'code' => 'OWOP', 'level' => 'primary', 'is_elective' => false],
            ['name' => 'Creative Arts and Design', 'code' => 'CAD', 'level' => 'primary', 'is_elective' => false],

            // JHS specific
            ['name' => 'Integrated Science', 'code' => 'INT_SCI', 'level' => 'jhs', 'is_elective' => false],
            ['name' => 'Social Studies', 'code' => 'SOC', 'level' => 'jhs', 'is_elective' => false],
            ['name' => 'Religious and Moral Education', 'code' => 'RME', 'level' => 'jhs', 'is_elective' => false],
            ['name' => 'Information and Communication Technology', 'code' => 'ICT', 'level' => 'jhs', 'is_elective' => false],
            ['name' => 'French', 'code' => 'FRE', 'level' => 'jhs', 'is_elective' => false],
            
            // JHS Electives
            ['name' => 'Career Technology', 'code' => 'CAT', 'level' => 'jhs', 'is_elective' => true],
            ['name' => 'Ghanaian Language (Twi)', 'code' => 'TWI', 'level' => 'jhs', 'is_elective' => true],
        ];

        foreach ($subjects as $subjectData) {
            Subject::create($subjectData);
        }
    }
}
