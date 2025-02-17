<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SupportStatusUpdated extends Notification
{
    use Queueable;

    protected $supportRequest;

    public function __construct($supportRequest)
    {
        $this->supportRequest = $supportRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $statusMessages = [
            'pending' => 'is now pending review',
            'in_progress' => 'is being processed',
            'resolved' => 'has been resolved'
        ];

        $message = $statusMessages[$this->supportRequest->status] ?? 'has been updated';

        return (new MailMessage)
            ->subject('Support Request Status Update')
            ->greeting('Hello ' . $this->supportRequest->user_name)
            ->line('Your support request "' . $this->supportRequest->subject . '" ' . $message)
            ->line('Request Details:')
            ->line('Subject: ' . $this->supportRequest->subject)
            ->line('Status: ' . ucfirst($this->supportRequest->status))
            ->line('Priority: ' . ucfirst($this->supportRequest->priority))
            ->action('View Request', url('/user/help'))
            ->line('Thank you for using our service!');
    }
} 