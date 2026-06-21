<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Students
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.import',
            'students.export',

            // Attendance
            'attendance.mark',
            'attendance.view',
            'attendance.report',

            // Grades
            'grades.enter',
            'grades.view',
            'grades.approve',
            'grades.report',

            // Fees
            'fees.structure',
            'fees.invoice',
            'fees.payment',
            'fees.refund',
            'fees.report',

            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',

            // Settings
            'settings.view',
            'settings.edit',

            // Reports
            'reports.dashboard',
            'reports.academic',
            'reports.financial',
            'reports.custom',

            // Communications
            'communications.sms',
            'communications.email',
            'communications.announce',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign existing permissions

        // 1. Proprietor - full access
        $proprietor = Role::findOrCreate('Proprietor');
        $proprietor->givePermissionTo(Permission::all());

        // 2. HeadTeacher - academic oversight, reports, staff/student viewing
        $headTeacher = Role::findOrCreate('HeadTeacher');
        $headTeacher->givePermissionTo([
            'students.view', 'students.export',
            'attendance.view', 'attendance.report',
            'grades.view', 'grades.approve', 'grades.report',
            'staff.view',
            'reports.dashboard', 'reports.academic', 'reports.custom',
            'communications.email', 'communications.announce',
        ]);

        // 3. ClassTeacher - attendance, grades, report comments for their class
        $classTeacher = Role::findOrCreate('ClassTeacher');
        $classTeacher->givePermissionTo([
            'students.view',
            'attendance.mark', 'attendance.view', 'attendance.report',
            'grades.enter', 'grades.view', 'grades.report',
            'reports.dashboard', 'reports.academic',
            'communications.announce',
        ]);

        // 4. SubjectTeacher - grade entry, view students
        $subjectTeacher = Role::findOrCreate('SubjectTeacher');
        $subjectTeacher->givePermissionTo([
            'students.view',
            'grades.enter', 'grades.view', 'grades.report',
            'reports.dashboard',
        ]);

        // 5. Accounts - fee structure, invoicing, payments, financial reports
        $accounts = Role::findOrCreate('Accounts');
        $accounts->givePermissionTo([
            'students.view',
            'fees.structure', 'fees.invoice', 'fees.payment', 'fees.refund', 'fees.report',
            'reports.dashboard', 'reports.financial',
        ]);

        // 6. FrontDesk - admissions, view student profiles
        $frontDesk = Role::findOrCreate('FrontDesk');
        $frontDesk->givePermissionTo([
            'students.view', 'students.create', 'students.edit',
            'reports.dashboard',
        ]);

        // 7. Supervisor - read-only school-wide + fee management
        $supervisor = Role::findOrCreate('Supervisor');
        $supervisor->givePermissionTo([
            'students.view', 'students.export',
            'attendance.view', 'attendance.report',
            'grades.view', 'grades.report',
            'fees.structure', 'fees.invoice', 'fees.payment', 'fees.report',
            'staff.view',
            'reports.dashboard', 'reports.academic', 'reports.financial',
            'communications.announce',
        ]);

        // 8. Parent - own children
        $parent = Role::findOrCreate('Parent');
        // Parent permissions are mostly role-based in policies rather than granular permission checking,
        // but we seed the role itself.

        // 9. Student - student portal
        Role::findOrCreate('Student');
    }
}
