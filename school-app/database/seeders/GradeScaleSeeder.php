<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        // Nursery & KG Competency Bands
        $competencies = [
            ['grade' => 'Highly Proficient', 'min_score' => 80, 'max_score' => 100, 'remarks' => 'Exceeds expectations consistently'],
            ['grade' => 'Proficient', 'min_score' => 65, 'max_score' => 79.99, 'remarks' => 'Meets expectations independently'],
            ['grade' => 'Developing', 'min_score' => 50, 'max_score' => 64.99, 'remarks' => 'Meeting expectations with assistance'],
            ['grade' => 'Beginning', 'min_score' => 0, 'max_score' => 49.99, 'remarks' => 'Still developing basic skills'],
        ];

        foreach (['nursery', 'kindergarten'] as $level) {
            foreach ($competencies as $comp) {
                GradeScale::create(array_merge($comp, ['level' => $level]));
            }
        }

        // Primary Level (Standard A-F)
        $primaryScales = [
            ['grade' => 'A', 'min_score' => 80, 'max_score' => 100, 'remarks' => 'Excellent'],
            ['grade' => 'B', 'min_score' => 70, 'max_score' => 79.99, 'remarks' => 'Very Good'],
            ['grade' => 'C', 'min_score' => 60, 'max_score' => 69.99, 'remarks' => 'Good'],
            ['grade' => 'D', 'min_score' => 50, 'max_score' => 59.99, 'remarks' => 'Credit'],
            ['grade' => 'E', 'min_score' => 40, 'max_score' => 49.99, 'remarks' => 'Pass'],
            ['grade' => 'F', 'min_score' => 0, 'max_score' => 39.99, 'remarks' => 'Fail'],
        ];

        foreach ($primaryScales as $scale) {
            GradeScale::create(array_merge($scale, ['level' => 'primary']));
        }

        // JHS BECE Scale (1-9)
        $jhsScales = [
            ['grade' => '1', 'min_score' => 90, 'max_score' => 100, 'remarks' => 'Highest'],
            ['grade' => '2', 'min_score' => 80, 'max_score' => 89.99, 'remarks' => 'Higher'],
            ['grade' => '3', 'min_score' => 70, 'max_score' => 79.99, 'remarks' => 'High'],
            ['grade' => '4', 'min_score' => 60, 'max_score' => 69.99, 'remarks' => 'High Average'],
            ['grade' => '5', 'min_score' => 55, 'max_score' => 59.99, 'remarks' => 'Average'],
            ['grade' => '6', 'min_score' => 50, 'max_score' => 54.99, 'remarks' => 'Low Average'],
            ['grade' => '7', 'min_score' => 40, 'max_score' => 49.99, 'remarks' => 'Low'],
            ['grade' => '8', 'min_score' => 35, 'max_score' => 39.99, 'remarks' => 'Lower'],
            ['grade' => '9', 'min_score' => 0, 'max_score' => 34.99, 'remarks' => 'Lowest'],
        ];

        foreach ($jhsScales as $scale) {
            GradeScale::create(array_merge($scale, ['level' => 'jhs']));
        }
    }
}