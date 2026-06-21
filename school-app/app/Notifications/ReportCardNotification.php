<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportCardNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Student $student,
        protected string $termName,
        protected string $academicYear
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Report Card Available — {$this->student->full_name}")
            ->greeting('Dear Parent/Guardian,')
            ->line("The report card for **{$this->student->full_name}** for **{$this->termName}** ({$this->academicYear}) is now available.")
            ->line('You can view and download the report card from the Parent Portal.')
            ->action('View Report Card', url('/parent/child/' . $this->student->id . '/grades'))
            ->salutation('Regards, Hanara Schools Academic Office');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'term' => $this->termName,
            'type' => 'report_card',
        ];
    }
}
