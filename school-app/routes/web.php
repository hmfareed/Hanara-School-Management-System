<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Guest / Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/admissions/apply', [\App\Http\Controllers\AdmissionController::class, 'create'])->name('admissions.apply');
Route::post('/admissions/apply', [\App\Http\Controllers\AdmissionController::class, 'store'])->name('admissions.apply.store');

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.attempt');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Password change (must be accessible even when must_change_password is true)
    Route::get('/change-password', [ChangePasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    // Teacher Onboarding (must be accessible before other dashboard routes)
    Route::get('/onboarding/teacher', [\App\Http\Controllers\OnboardingController::class, 'showForm'])->name('onboarding.teacher');
    Route::post('/onboarding/teacher', [\App\Http\Controllers\OnboardingController::class, 'submitForm'])->name('onboarding.teacher.submit');

    // Staff Roles Management (Proprietor & HeadTeacher only)
    Route::middleware('role:Proprietor|HeadTeacher')->prefix('staff-roles')->group(function () {
        Route::get('/', [\App\Http\Controllers\StaffRolesController::class, 'index'])->name('staff-roles.index');
        Route::get('/waitlist', [\App\Http\Controllers\StaffRolesController::class, 'waitlist'])->name('staff-roles.waitlist');
        Route::post('/{user}/approve', [\App\Http\Controllers\StaffRolesController::class, 'approve'])->name('staff-roles.approve');
        Route::post('/{user}/reject', [\App\Http\Controllers\StaffRolesController::class, 'reject'])->name('staff-roles.reject');
        Route::get('/{user}/edit', [\App\Http\Controllers\StaffRolesController::class, 'edit'])->name('staff-roles.edit');
        Route::put('/{user}', [\App\Http\Controllers\StaffRolesController::class, 'update'])->name('staff-roles.update');
    });

    // Staff Attendance & Leave Management
    Route::prefix('staff')->group(function () {
        Route::post('/clock-in', [\App\Http\Controllers\StaffAttendanceController::class, 'clockIn'])->name('staff.clock-in');
        Route::post('/clock-out', [\App\Http\Controllers\StaffAttendanceController::class, 'clockOut'])->name('staff.clock-out');
        Route::get('/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'index'])->name('staff.leaves.index');
        Route::post('/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('staff.leaves.store');
    });

    Route::middleware('role:Proprietor|HeadTeacher|Supervisor')->prefix('admin')->group(function () {
        Route::get('/staff-attendance', [\App\Http\Controllers\StaffAttendanceController::class, 'adminIndex'])->name('admin.staff-attendance.index');
        Route::get('/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'adminIndex'])->name('admin.leaves.index');
        Route::post('/leaves/{leaveRequest}/approve', [\App\Http\Controllers\LeaveRequestController::class, 'approve'])->name('admin.leaves.approve');
        Route::post('/leaves/{leaveRequest}/reject', [\App\Http\Controllers\LeaveRequestController::class, 'reject'])->name('admin.leaves.reject');
    });

    /*
    |----------------------------------------------------------------------
    | Role-Based Dashboards
    |----------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->getDashboardRoute());
    })->name('dashboard');

    Route::get('/dashboard/proprietor', [DashboardController::class, 'proprietor'])
        ->middleware('role:Proprietor')
        ->name('dashboard.proprietor');

    Route::get('/dashboard/head-teacher', [DashboardController::class, 'headTeacher'])
        ->middleware('role:HeadTeacher|Proprietor|Supervisor')
        ->name('dashboard.head-teacher');

    Route::get('/dashboard/class-teacher', [DashboardController::class, 'classTeacher'])
        ->middleware('role:ClassTeacher|Proprietor|HeadTeacher')
        ->name('dashboard.class-teacher');

    Route::get('/dashboard/subject-teacher', [DashboardController::class, 'subjectTeacher'])
        ->middleware('role:SubjectTeacher|Proprietor|HeadTeacher')
        ->name('dashboard.subject-teacher');

    Route::get('/dashboard/accounts', [DashboardController::class, 'accounts'])
        ->middleware('role:Accounts|Proprietor')
        ->name('dashboard.accounts');

    Route::get('/dashboard/front-desk', [DashboardController::class, 'frontDesk'])
        ->middleware('role:FrontDesk|Proprietor')
        ->name('dashboard.front-desk');

    Route::get('/dashboard/parent', [DashboardController::class, 'parent'])
        ->middleware('role:Parent')
        ->name('dashboard.parent');

    Route::get('/dashboard/student', [DashboardController::class, 'student'])
        ->middleware('role:Student|Proprietor|HeadTeacher')
        ->name('dashboard.student');

    /*
    |----------------------------------------------------------------------
    | Settings (Proprietor only)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:Proprietor')->prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
    });

    /*
    |----------------------------------------------------------------------
    | Staff Directory
    |----------------------------------------------------------------------
    |*/
    Route::middleware('role:Proprietor|HeadTeacher|Supervisor')->prefix('staff')->group(function () {
        Route::get('/', [\App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
    });

    Route::middleware('role:Proprietor')->prefix('staff-codes')->group(function () {
        Route::post('/generate', [\App\Http\Controllers\StaffCodeController::class, 'generate'])->name('staff-codes.generate');
        Route::post('/{code}/regenerate', [\App\Http\Controllers\StaffCodeController::class, 'regenerate'])->name('staff-codes.regenerate');
        Route::delete('/{code}', [\App\Http\Controllers\StaffCodeController::class, 'destroy'])->name('staff-codes.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Admissions Queue & Actions
    |----------------------------------------------------------------------
    */
    Route::middleware('role:FrontDesk|HeadTeacher|Proprietor|Supervisor')->prefix('admissions')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdmissionController::class, 'index'])->name('admissions.index');
        Route::get('/{admission}', [\App\Http\Controllers\AdmissionController::class, 'show'])->name('admissions.show');
        Route::post('/{admission}/approve', [\App\Http\Controllers\AdmissionController::class, 'approve'])->name('admissions.approve');
        Route::post('/{admission}/decline', [\App\Http\Controllers\AdmissionController::class, 'decline'])->name('admissions.decline');
    });

    /*
    |----------------------------------------------------------------------
    | Student Directory & Profiles
    |----------------------------------------------------------------------
    */
    Route::prefix('students')->group(function () {
        Route::get('/', [\App\Http\Controllers\StudentController::class, 'index'])->name('students.index')
            ->middleware('role:ClassTeacher|SubjectTeacher|FrontDesk|Accounts|HeadTeacher|Proprietor|Supervisor');
        Route::get('/import', [\App\Http\Controllers\StudentController::class, 'importForm'])->name('students.import')
            ->middleware('role:Proprietor|HeadTeacher');
        Route::post('/import', [\App\Http\Controllers\StudentController::class, 'import'])->name('students.import.post')
            ->middleware('role:Proprietor|HeadTeacher');
        Route::get('/create', [\App\Http\Controllers\StudentController::class, 'create'])->name('students.create')
            ->middleware('role:Proprietor|HeadTeacher|FrontDesk');
        Route::post('/', [\App\Http\Controllers\StudentController::class, 'store'])->name('students.store')
            ->middleware('role:Proprietor|HeadTeacher|FrontDesk');

        // Class Promotions (must be before {student} wildcard)
        Route::get('/promotions', [\App\Http\Controllers\StudentController::class, 'promotionForm'])->name('students.promotions')
            ->middleware('role:Proprietor|HeadTeacher');
        Route::post('/promotions/promote', [\App\Http\Controllers\StudentController::class, 'promoteClass'])->name('students.promote')
            ->middleware('role:Proprietor|HeadTeacher');

        // Student-specific routes
        Route::get('/{student}', [\App\Http\Controllers\StudentController::class, 'show'])->name('students.show')
            ->middleware('role:ClassTeacher|SubjectTeacher|FrontDesk|Accounts|HeadTeacher|Proprietor|Supervisor');
        Route::get('/{student}/edit', [\App\Http\Controllers\StudentController::class, 'edit'])->name('students.edit')
            ->middleware('role:Proprietor|HeadTeacher|FrontDesk');
        Route::put('/{student}', [\App\Http\Controllers\StudentController::class, 'update'])->name('students.update')
            ->middleware('role:Proprietor|HeadTeacher|FrontDesk');
        Route::post('/{student}/transfer', [\App\Http\Controllers\StudentController::class, 'transfer'])->name('students.transfer')
            ->middleware('role:Proprietor|HeadTeacher');
        Route::post('/{student}/revert-transfer', [\App\Http\Controllers\StudentController::class, 'revertTransfer'])->name('students.revert-transfer')
            ->middleware('role:Proprietor|HeadTeacher');
        Route::get('/{student}/id-card', [\App\Http\Controllers\StudentController::class, 'printIdCard'])->name('students.id-card')
            ->middleware('role:ClassTeacher|SubjectTeacher|FrontDesk|Accounts|HeadTeacher|Proprietor|Supervisor');
        Route::get('/{student}/transcript', [\App\Http\Controllers\StudentController::class, 'generateTranscript'])->name('students.transcript')
            ->middleware('role:HeadTeacher|Proprietor');
        Route::get('/{student}/testimonial', [\App\Http\Controllers\StudentController::class, 'generateTestimonial'])->name('students.testimonial')
            ->middleware('role:HeadTeacher|Proprietor');
    });

    /*
    |----------------------------------------------------------------------
    | Attendance Workspace
    |----------------------------------------------------------------------
    */
    Route::prefix('attendance')->group(function () {
        Route::get('/mark', [\App\Http\Controllers\AttendanceController::class, 'mark'])->name('attendance.mark')
            ->middleware('role:ClassTeacher|SubjectTeacher|FrontDesk|HeadTeacher|Proprietor|Supervisor');
        Route::get('/register', [\App\Http\Controllers\AttendanceController::class, 'register'])->name('attendance.register')
            ->middleware('role:ClassTeacher|SubjectTeacher|FrontDesk|HeadTeacher|Proprietor|Supervisor');
    });

    /*
    |----------------------------------------------------------------------
    | Fees & Invoicing (Billing)
    |----------------------------------------------------------------------
    */
    Route::prefix('billing')->group(function () {
        Route::get('/invoices', [\App\Http\Controllers\BillingController::class, 'invoices'])->name('billing.invoices')
            ->middleware('role:Accounts|Proprietor|Supervisor');
        Route::post('/invoices/generate', [\App\Http\Controllers\BillingController::class, 'generateInvoices'])->name('billing.invoices.generate')
            ->middleware('role:Proprietor');
        Route::get('/record-payment', [\App\Http\Controllers\BillingController::class, 'recordPaymentForm'])->name('billing.record-payment.form')
            ->middleware('role:Accounts|Proprietor');
        Route::post('/record-payment', [\App\Http\Controllers\BillingController::class, 'recordPayment'])->name('billing.record-payment.post')
            ->middleware('role:Accounts|Proprietor');
        Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\BillingController::class, 'printReceipt'])->name('billing.receipt')
            ->middleware('role:Accounts|Proprietor|Supervisor');

        // Phase 4: Financial additions
        Route::get('/defaulters', [\App\Http\Controllers\BillingController::class, 'defaulters'])->name('billing.defaulters')
            ->middleware('role:Accounts|Proprietor|Supervisor');
        Route::post('/invoices/{invoice}/credit', [\App\Http\Controllers\BillingController::class, 'recordCreditNote'])->name('billing.credit-note.store')
            ->middleware('role:Accounts|Proprietor');

        Route::post('/invoices/{invoice}/pay', [\App\Http\Controllers\BillingController::class, 'initializeOnlinePayment'])->name('billing.pay.initialize');
        Route::get('/pay/callback', [\App\Http\Controllers\BillingController::class, 'paystackCallback'])->name('billing.pay.callback');
    });

    /*
    |----------------------------------------------------------------------
    | Academics Module
    |----------------------------------------------------------------------
    */
    Route::prefix('academics')->group(function () {
        Route::get('/gradebook', [\App\Http\Controllers\AcademicsController::class, 'gradebook'])
            ->name('academics.gradebook')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor|Supervisor');

        Route::get('/assignments', [\App\Http\Controllers\AcademicsController::class, 'assignments'])
            ->name('academics.assignments')
            ->middleware('role:HeadTeacher|Proprietor');

        Route::get('/timetable', [\App\Http\Controllers\AcademicsController::class, 'timetable'])
            ->name('academics.timetable')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor|Supervisor');

        Route::get('/report-cards', [\App\Http\Controllers\AcademicsController::class, 'reportCards'])
            ->name('academics.report-cards')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor|Supervisor');

        Route::get('/report-card/{student}', [\App\Http\Controllers\AcademicsController::class, 'reportCard'])
            ->name('academics.report-card')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor|Supervisor');

        // Phase 5: BECE Readiness
        Route::get('/bece', [\App\Http\Controllers\BeceController::class, 'index'])
            ->name('academics.bece.index')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor');

        Route::get('/bece/enter-scores', [\App\Http\Controllers\BeceController::class, 'enterScores'])
            ->name('academics.bece.enter-scores')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor');

        Route::post('/bece/store-scores', [\App\Http\Controllers\BeceController::class, 'storeScores'])
            ->name('academics.bece.store-scores')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor');

        Route::get('/bece/student/{student}', [\App\Http\Controllers\BeceController::class, 'studentDetail'])
            ->name('academics.bece.student-detail')
            ->middleware('role:ClassTeacher|SubjectTeacher|HeadTeacher|Proprietor');
    });

    /*
    |----------------------------------------------------------------------
    | Communication & Announcements (Phase 5)
    |----------------------------------------------------------------------
    */
    Route::prefix('communication')->group(function () {
        // Read-only/View Announcements (All staff roles)
        Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])
            ->name('communication.announcements.index')
            ->middleware('role:HeadTeacher|Proprietor|Supervisor|ClassTeacher|SubjectTeacher');

        // Write/Manage Announcements & SMS (Exclude Supervisor)
        Route::middleware('role:HeadTeacher|Proprietor|ClassTeacher|SubjectTeacher')->group(function () {
            Route::get('/sms/compose', [\App\Http\Controllers\CommunicationController::class, 'smsCompose'])
                ->name('communication.sms.compose');
            Route::post('/sms/send', [\App\Http\Controllers\CommunicationController::class, 'smsSend'])
                ->name('communication.sms.send');
            Route::get('/announcements/create', [\App\Http\Controllers\AnnouncementController::class, 'create'])
                ->name('communication.announcements.create');
            Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])
                ->name('communication.announcements.store');
            Route::post('/emergency-broadcast', [\App\Http\Controllers\AnnouncementController::class, 'emergencyBroadcast'])
                ->name('communication.emergency-broadcast');
        });
    });

    /*
    |----------------------------------------------------------------------
    | Parent Portal (Phase 5)
    |----------------------------------------------------------------------
    */
    Route::prefix('parent')->middleware('role:Parent')->group(function () {
        Route::get('/child/{student}/attendance', [\App\Http\Controllers\ParentPortalController::class, 'childAttendance'])
            ->name('parent.child.attendance');
        Route::get('/child/{student}/grades', [\App\Http\Controllers\ParentPortalController::class, 'childGrades'])
            ->name('parent.child.grades');
        Route::get('/child/{student}/fees', [\App\Http\Controllers\ParentPortalController::class, 'childFees'])
            ->name('parent.child.fees');
        Route::get('/child/{student}/report-card', [\App\Http\Controllers\ParentPortalController::class, 'childReportCard'])
            ->name('parent.child.report-card');
        Route::post('/child/invoice/{invoice}/pay', [\App\Http\Controllers\ParentPortalController::class, 'payFee'])
            ->name('parent.child.pay');
    });
});

Route::post('/billing/pay/webhook', [\App\Http\Controllers\BillingController::class, 'paystackWebhook'])->name('billing.pay.webhook');

Route::get('/vercel-migrate', function () {
    $secret = env('MIGRATION_SECRET');
    if (!$secret || request('secret') !== $secret) {
        abort(403, 'Unauthorized: Invalid or missing MIGRATION_SECRET');
    }
    
    try {
        $status = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response()->json([
            'status' => 'success',
            'exit_code' => $status,
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

