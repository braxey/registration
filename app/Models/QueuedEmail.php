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
        $email = new static();
        $email->to_address = $to;
        $email->email_type = $type;
        $email->payload = json_encode($payload);
        $email->save();
    }

    /**
     * Check if the email was sent less than an hour ago.
     *
     * @return bool
     */
    public function wasSentLessThanAnHourAgo(): bool
    {
        return $this->getParsedSentTime()->gte(Carbon::now('EST')->subHour());
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
     * @return string
     */
    public function getSentTime()
    {
        return $this->sent_at;
    }

    /**
     * @return Carbon
     */
    public function getParsedSentTime()
    {
        return Carbon::parse($this->getSentTime(), 'EST');
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
        $this->sent_at = Carbon::now('EST');
        $this->save();
    }
}
