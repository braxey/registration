<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\User;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;

    private User $recipient;
    private string $sub;
    private string $message;
    private bool $includeAppointments;
    private ?Collection $appointments;

    public function __construct(User $recipient, string $subject, string $message, bool $includeAppointments, ?Collection $appointments = null)
    {
        $this->recipient = $recipient;
        $this->sub = $subject;
        $this->mes = $message;
        $this->includeAppointments = $includeAppointments;
        $this->appointments = $appointments;
    }

    public function build()
    {
        return $this->subject($this->sub)
            ->view('emails.custom', [
                'recipient'             => $this->recipient,
                'customMessage'         => $this->mes,
                'includeAppointments'   => $this->includeAppointments,
                'appointments'          => $this->appointments,
            ]);
    }

    public function assertHas(array $payload): bool
    {
        return (
            $this->firstName === $payload['first_name']
            && $this->message === $payload['message']
            && $this->includeAppointments === $payload['includeAppointments']
            && $this->appointments === $payload['appointments']
        );
    }
}
