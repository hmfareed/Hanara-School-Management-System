<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Services\SmsService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List all announcements.
     */
    public function index(Request $request)
    {
        $query = Announcement::with('publisher', 'targetClass')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $announcements = $query->paginate(15);

        return view('communication.announcements.index', compact('announcements'));
    }

    /**
     * Show the create announcement form.
     */
    public function create()
    {
        $classes = SchoolClass::orderBy('display_order')->get();

        return view('communication.announcements.create', compact('classes'));
    }

    /**
     * Store a new announcement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:general,academic,financial,emergency',
            'target_audience' => 'required|in:all,parents,staff,class',
            'target_class_id' => 'required_if:target_audience,class|nullable|exists:school_classes,id',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'send_sms' => 'boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'body' => $request->body,
            'type' => $request->type,
            'target_audience' => $request->target_audience,
            'target_class_id' => $request->target_audience === 'class' ? $request->target_class_id : null,
            'published_by' => auth()->id(),
            'published_at' => now(),
            'expires_at' => $request->expires_at,
            'is_pinned' => $request->boolean('is_pinned'),
            'sms_sent' => false,
        ]);

        // Optionally send SMS blast
        if ($request->boolean('send_sms')) {
            $this->sendAnnouncementSms($announcement);
        }

        AuditLog::log('announcement_created', $announcement, null, $announcement->toArray());

        return redirect()->route('communication.announcements.index')
            ->with('success', 'Announcement published successfully.');
    }

    /**
     * Emergency broadcast — send an urgent SMS to ALL parents immediately.
     */
    public function emergencyBroadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:640',
        ]);

        // Create emergency announcement record
        $announcement = Announcement::create([
            'title' => '⚠ EMERGENCY BROADCAST',
            'body' => $request->message,
            'type' => 'emergency',
            'target_audience' => 'all',
            'published_by' => auth()->id(),
            'published_at' => now(),
            'is_pinned' => true,
            'sms_sent' => false,
        ]);

        // Send SMS to all guardians
        $this->sendAnnouncementSms($announcement);

        AuditLog::log('emergency_broadcast', $announcement, null, ['message' => $request->message]);

        return back()->with('success', 'Emergency broadcast sent to all parents.');
    }

    /**
     * Send announcement message via SMS to relevant guardians.
     */
    protected function sendAnnouncementSms(Announcement $announcement): void
    {
        $smsService = new SmsService();

        $recipients = Guardian::whereNotNull('phone')
            ->pluck('phone')
            ->unique()
            ->toArray();

        if (!empty($recipients)) {
            $message = "[Hanara Schools] {$announcement->title}\n\n{$announcement->body}";

            // Truncate if needed for SMS
            if (strlen($message) > 640) {
                $message = substr($message, 0, 637) . '...';
            }

            $smsService->sendBulkSms($recipients, $message);
            $announcement->update(['sms_sent' => true]);
        }
    }
}
