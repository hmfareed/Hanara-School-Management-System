<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AcademicYearSeeder::class,
            SchoolClassSeeder::class,
            SubjectSeeder::class,
            StaffSeeder::class,
            StudentSeeder::class,
            GuardianSeeder::class,
            UserSeeder::class,
            SettingsSeeder::class,
            FeeItemSeeder::class,
            GradeScaleSeeder::class,
        ]);
    }
}
