<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $dateTime;
    private $slots;

    public function __construct($dateTime, $slots)
    {
        $this->dateTime = Carbon::parse($dateTime)->format('F j, Y g:i A');
        $this->slots = $slots;
    }

    public function build()
    {
        return $this->subject('Walk Thru Bethlehem Appointment Notification')
            ->view('emails.notify-appt-email');
    }
}
