<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbsenceAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Student $student,
        protected string $date,
        protected string $className
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Absence Alert — ' . $this->student->full_name)
            ->greeting('Dear Parent/Guardian,')
            ->line("This is to inform you that **{$this->student->full_name}** ({$this->className}) was marked **absent** on **{$this->date}**.")
            ->line('If this absence was pre-arranged or you have concerns, please contact the school office.')
            ->line('You can view your child\'s full attendance record on the Parent Portal.')
            ->action('View Attendance', url('/parent/child/' . $this->student->id . '/attendance'))
            ->salutation('Regards, Hanara Schools');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name,
            'date' => $this->date,
            'type' => 'absence_alert',
        ];
    }
}
