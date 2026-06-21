<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\ClassAcademicYear;
use App\Models\AcademicYear;
use App\Services\SmsService;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * Show the SMS compose form.
     */
    public function smsCompose()
    {
        $classes = SchoolClass::orderBy('display_order')->get();

        return view('communication.sms.compose', compact('classes'));
    }

    /**
     * Send bulk SMS.
     */
    public function smsSend(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:640',
            'recipient_type' => 'required|in:all_parents,class,custom',
            'class_id' => 'required_if:recipient_type,class|nullable|exists:school_classes,id',
            'custom_numbers' => 'required_if:recipient_type,custom|nullable|string',
        ]);

        $smsService = new SmsService();
        $recipients = [];

        switch ($request->recipient_type) {
            case 'all_parents':
                $recipients = Guardian::whereNotNull('phone')
                    ->pluck('phone')
                    ->unique()
                    ->toArray();
                break;

            case 'class':
                $currentYear = AcademicYear::current();
                if ($currentYear) {
                    $classAY = ClassAcademicYear::where('school_class_id', $request->class_id)
                        ->where('academic_year_id', $currentYear->id)
                        ->first();

                    if ($classAY) {
                        $studentIds = $classAY->students()->pluck('students.id');
                        $recipients = Guardian::whereHas('students', fn($q) => $q->whereIn('students.id', $studentIds))
                            ->whereNotNull('phone')
                            ->pluck('phone')
                            ->unique()
                            ->toArray();
                    }
                }
                break;

            case 'custom':
                $recipients = array_filter(
                    array_map('trim', preg_split('/[\n,;]+/', $request->custom_numbers))
                );
                break;
        }

        if (empty($recipients)) {
            return back()->with('warning', 'No recipients found for the selected criteria.');
        }

        $success = $smsService->sendBulkSms($recipients, $request->message);

        AuditLog::log(
            'sms_sent',
            auth()->user(),
            null,
            [
                'recipient_type' => $request->recipient_type,
                'recipients_count' => count($recipients),
                'message' => $request->message,
            ]
        );

        if ($success) {
            return back()->with('success', 'SMS sent successfully to ' . count($recipients) . ' recipient(s).');
        }

        return back()->with('warning', 'SMS sending queued but Arkesel API key may not be configured. Check .env settings.');
    }

    /**
     * View SMS sending history from audit logs.
     */
    public function smsLog()
    {
        $logs = AuditLog::where('action', 'sms_sent')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('communication.sms.log', compact('logs'));
    }
}
