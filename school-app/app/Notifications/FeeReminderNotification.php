<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeeReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Invoice $invoice,
        protected string $studentName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fee Reminder — ' . $this->studentName)
            ->greeting('Dear Parent/Guardian,')
            ->line("This is a reminder that the fee invoice for **{$this->studentName}** has an outstanding balance.")
            ->line("**Invoice:** {$this->invoice->invoice_number}")
            ->line("**Total Amount:** GH₵" . number_format($this->invoice->total_amount, 2))
            ->line("**Amount Paid:** GH₵" . number_format($this->invoice->amount_paid, 2))
            ->line("**Balance Due:** GH₵" . number_format($this->invoice->balance, 2))
            ->line("**Due Date:** " . ($this->invoice->due_date?->format('d M Y') ?? 'N/A'))
            ->line('You can make a payment online through the Parent Portal.')
            ->action('Pay Now', url('/parent/child/' . $this->invoice->student_id . '/fees'))
            ->salutation('Regards, Hanara Schools Accounts Office');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'student_name' => $this->studentName,
            'balance' => $this->invoice->balance,
            'type' => 'fee_reminder',
        ];
    }
}
