<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Booking;

class BookingStatusNotification extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->booking_id,
            'status' => $this->booking->status,
            'message' => "Your booking #{$this->booking->booking_id} has been {$this->booking->status}",
            'room_name' => $this->booking->room->room_name ?? 'Unknown Room',
            'boarding_house' => $this->booking->room->boardingHouse->name ?? 'Unknown Boarding House'
        ];
    }
} 