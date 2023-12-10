<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QueuedEmail extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'email_queue';

    /**
     * @var array
     */
    protected $fillable = [
        'to_address', 'email_type', 'payload', 'sent',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'sent' => 'boolean',
    ];

    /**
     * Check if the email has been queued for over an hour.
     *
     * @return bool
     */
    public function isQueuedForOverAnHour(): bool
    {
        return $this->created_at->isBefore(Carbon::now()->subHour());
    }

    /**
     * Check if the email was sent.
     *
     * @return bool
     */
    public function wasSent(): bool
    {
        return (int) $this->sent === 1;
    }

    /**
     * Get the to email address.
     *
     * @return string
     */
    public function getTo(): string
    {
        return $this->to_address;
    }

    /**
     * Get the payload for the email.
     *
     * @return array
     */
    public function getPayload(): array
    {
        return json_decode($this->payload, true);
    }

    /**
     * Get the email type.
     *
     * @return int
     */
    public function getEmailType(): int
    {
        return $this->email_type;
    }
}
