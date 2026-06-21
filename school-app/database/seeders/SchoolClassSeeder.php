<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\ClassAcademicYear;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // Nursery
            ['name' => 'Nursery 1', 'level' => 'nursery', 'display_order' => 1],
            ['name' => 'Nursery 2', 'level' => 'nursery', 'display_order' => 2],
            // Kindergarten
            ['name' => 'KG1', 'level' => 'kindergarten', 'display_order' => 3],
            ['name' => 'KG2', 'level' => 'kindergarten', 'display_order' => 4],
            // Primary
            ['name' => 'P1', 'level' => 'primary', 'display_order' => 5],
            ['name' => 'P2', 'level' => 'primary', 'display_order' => 6],
            ['name' => 'P3', 'level' => 'primary', 'display_order' => 7],
            ['name' => 'P4', 'level' => 'primary', 'display_order' => 8],
            ['name' => 'P5', 'level' => 'primary', 'display_order' => 9],
            ['name' => 'P6', 'level' => 'primary', 'display_order' => 10],
            // JHS
            ['name' => 'JHS1', 'level' => 'jhs', 'display_order' => 11],
            ['name' => 'JHS2', 'level' => 'jhs', 'display_order' => 12],
            ['name' => 'JHS3', 'level' => 'jhs', 'display_order' => 13],
        ];

        $currentYear = AcademicYear::where('is_current', true)->first();

        foreach ($classes as $classData) {
            $class = SchoolClass::create($classData);
            
            if ($currentYear) {
                ClassAcademicYear::create([
                    'school_class_id' => $class->id,
                    'academic_year_id' => $currentYear->id,
                    'class_teacher_id' => null, // Will be set in StaffSeeder
                ]);
            }
        }
    }
}
