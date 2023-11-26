<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $dateTime;
    private $slots;
    private $name;

    public function __construct(Carbon $dateTime, int $slots, string $name, bool $update)
    {
        $this->dateTime = $dateTime->format('F j, Y g:i A');
        $this->slots    = $slots;
        $this->name     = $name;
        $this->update   = $update; 
    }

    public function build()
    {
        return $this->subject('Walk Thru Bethlehem Appointment Notification')
            ->view('emails.notify-appt-email', [
                'dateTime' => $this->dateTime,
                'slots'    => $this->slots,
                'name'     => $this->name,
                'update'   => $this->update,
            ]);
    }
}
