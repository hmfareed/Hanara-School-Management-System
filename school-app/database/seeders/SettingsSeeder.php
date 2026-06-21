<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentTerm = Term::where('is_current', true)->first();

        $settings = [
            // School Info
            ['key' => 'school_name', 'value' => 'Hanara Schools', 'group' => 'school'],
            ['key' => 'school_logo', 'value' => null, 'group' => 'school'],
            ['key' => 'school_motto', 'value' => 'Excellence in Education and Character', 'group' => 'school'],
            ['key' => 'school_address', 'value' => 'Takoradi, Western Region, Ghana', 'group' => 'school'],
            ['key' => 'school_phone', 'value' => '+233 24 412 3456', 'group' => 'school'],
            ['key' => 'school_email', 'value' => 'info@hanara.edu.gh', 'group' => 'school'],

            // System State
            ['key' => 'current_academic_year_id', 'value' => $currentYear ? (string)$currentYear->id : null, 'group' => 'school'],
            ['key' => 'current_term_id', 'value' => $currentTerm ? (string)$currentTerm->id : null, 'group' => 'school'],

            // Grading Settings
            ['key' => 'ca_weight', 'value' => '30', 'group' => 'grading'],
            ['key' => 'exam_weight', 'value' => '70', 'group' => 'grading'],
            ['key' => 'bece_at_risk_threshold', 'value' => '36', 'group' => 'grading'],
            ['key' => 'nursery_grading_type', 'value' => 'competency', 'group' => 'grading'],
            ['key' => 'primary_jhs_grading_type', 'value' => 'numeric', 'group' => 'grading'],

            // Financial & Currency
            ['key' => 'currency', 'value' => 'GHS', 'group' => 'fees'],
            
            // SMS Settings
            ['key' => 'sms_provider', 'value' => 'arkesel', 'group' => 'sms'],
            ['key' => 'sms_sender_id', 'value' => 'HANARA', 'group' => 'sms'],
        ];

        foreach ($settings as $settingData) {
            Setting::create($settingData);
        }
    }
}
