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
    }
}
