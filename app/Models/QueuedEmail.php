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

    public static function queue(string $to, int $type, array $payload): void
    {
        error_log('TO EMAIL: ' . $to);
        $email = new static();
        $email->to_address = $to;
        $email->email_type = $type;
        $email->payload = json_encode($payload);
        $email->save();
    }

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
     * Check if the email has been queued for over an hour.
     *
     * @return bool
     */
    public function isQueuedForLessThanAnHour(): bool
    {
        return $this->created_at->isAfter(Carbon::now()->subHour());
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
     * Check if the email was not sent.
     *
     * @return bool
     */
    public function wasNotSent(): bool
    {
        return !$this->wasSent();
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

    /**
     * Mark an email as sent.
     */
    public function markSent()
    {
        $this->sent = true;
        $this->save();
    }
}
