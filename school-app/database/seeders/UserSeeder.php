<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Seed user accounts for all Staff
        $staffMembers = Staff::all();

        foreach ($staffMembers as $staff) {
            $user = User::create([
                'name' => $staff->full_name,
                'email' => $staff->email ?? strtolower($staff->first_name . '.' . $staff->last_name) . '@hanara.edu.gh',
                'password' => $defaultPassword,
                'userable_type' => Staff::class,
                'userable_id' => $staff->id,
                'must_change_password' => true,
            ]);

            // Assign role based on position
            $roleName = match ($staff->position) {
                'Proprietor' => 'Proprietor',
                'Head Teacher' => 'HeadTeacher',
                'Bursar' => 'Accounts',
                'Front Desk Officer' => 'FrontDesk',
                'Class Teacher' => 'ClassTeacher',
                'Subject Teacher' => 'SubjectTeacher',
                default => 'SubjectTeacher',
            };

            $user->assignRole($roleName);
        }

        // 2. Seed a sample parent user
        $parentGuardian = Guardian::first();
        if ($parentGuardian) {
            $parentUser = User::create([
                'name' => $parentGuardian->first_name . ' ' . $parentGuardian->last_name,
                'email' => 'parent@example.com',
                'password' => $defaultPassword,
                'userable_type' => Guardian::class,
                'userable_id' => $parentGuardian->id,
                'must_change_password' => true,
            ]);

            $parentUser->assignRole('Parent');
        }

        // Also add another parent for testing
        $secondParent = Guardian::skip(1)->first();
        if ($secondParent) {
            $parentUser2 = User::create([
                'name' => $secondParent->first_name . ' ' . $secondParent->last_name,
                'email' => $secondParent->email ?? 'parent2@example.com',
                'password' => $defaultPassword,
                'userable_type' => Guardian::class,
                'userable_id' => $secondParent->id,
                'must_change_password' => true,
            ]);

            $parentUser2->assignRole('Parent');
        }
    }
}
