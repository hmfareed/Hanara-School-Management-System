<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-08',
            'end_date' => '2026-07-24',
            'is_current' => true,
        ]);

        // Term 1
        Term::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2025-09-08',
            'end_date' => '2025-12-19',
            'is_current' => false,
        ]);

        // Term 2
        Term::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 2',
            'start_date' => '2026-01-12',
            'end_date' => '2026-04-10',
            'is_current' => false,
        ]);

        // Term 3 (Current term for June 2026)
        Term::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 3',
            'start_date' => '2026-05-04',
            'end_date' => '2026-07-24',
            'is_current' => true,
        ]);

        // Seed Custom Assessment Components
        \App\Models\AssessmentComponent::create([
            'name' => 'Class Exercise 1',
            'weight' => 4.29,
            'max_score' => 10,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Class Exercise 2',
            'weight' => 4.29,
            'max_score' => 10,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Exercise 1',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Exercise 2',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Class Test',
            'weight' => 8.57,
            'max_score' => 20,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Homework 1',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Homework 2',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Homework 3',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'Homework 4',
            'weight' => 2.14,
            'max_score' => 5,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
        \App\Models\AssessmentComponent::create([
            'name' => 'End of Term Exam',
            'weight' => 70.00,
            'max_score' => 100,
            'academic_year_id' => $academicYear->id,
            'level' => null,
        ]);
    }
}
