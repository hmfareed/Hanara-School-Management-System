<?php

namespace Database\Seeders;

use App\Models\FeeItem;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class FeeItemSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentTerm = Term::where('is_current', true)->first(); // Term 3

        if (!$currentYear) {
            return;
        }

        // 1. General PTA Levy - applies to all classes and all terms
        FeeItem::create([
            'name' => 'PTA Levy',
            'amount' => 50.00,
            'academic_year_id' => $currentYear->id,
            'term_id' => null, // all terms
            'school_class_id' => null, // all classes
            'is_optional' => false,
        ]);

        // 2. Class Level Specific Tuition Fees for the current term (Term 3)
        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            $tuitionAmount = match ($class->level) {
                'nursery' => 500.00,
                'kindergarten' => 600.00,
                'primary' => 800.00,
                'jhs' => 1000.00,
                default => 700.00,
            };

            // Tuition Fee
            FeeItem::create([
                'name' => 'Tuition Fee (' . $class->name . ')',
                'amount' => $tuitionAmount,
                'academic_year_id' => $currentYear->id,
                'term_id' => $currentTerm ? $currentTerm->id : null,
                'school_class_id' => $class->id,
                'is_optional' => false,
            ]);

            // ICT Fee for JHS only
            if ($class->level === 'jhs') {
                FeeItem::create([
                    'name' => 'ICT Lab Fee',
                    'amount' => 100.00,
                    'academic_year_id' => $currentYear->id,
                    'term_id' => $currentTerm ? $currentTerm->id : null,
                    'school_class_id' => $class->id,
                    'is_optional' => false,
                ]);
            }
        }

        // 3. Optional Transport Fee
        FeeItem::create([
            'name' => 'School Bus Transport (Optional)',
            'amount' => 300.00,
            'academic_year_id' => $currentYear->id,
            'term_id' => $currentTerm ? $currentTerm->id : null,
            'school_class_id' => null,
            'is_optional' => true,
        ]);
    }
}
