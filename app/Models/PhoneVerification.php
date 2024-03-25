<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;

class PhoneVerification extends Model
{
    protected $fillable = ['token', 'time_sent', 'user_id'];

    public function getToken(): string
    {
        return $this->token?? '';
    }

    public function getTimeSent()
    {
        return $this->time_sent;
    }

    public function getParsedTimeSent(): Carbon
    {
        return Carbon::parse($this->getTimeSent(), 'EST');
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return User::fromId($this->getUserId());
    }

    public static function fromUserEmail(string $email): ?PhoneVerification
    {
        $user = User::fromEmail($email);
        if ($user === null) {
            return null;
        }

        return static::where('user_id', $user->getId())->orderBy('time_sent', 'desc')->first();
    }

    public static function fromUserId(int $userId): ?PhoneVerification
    {
        $user = User::fromId($userId);
        if ($user === null) {
            return null;
        }

        return static::where('user_id', $user->getId())->orderBy('time_sent', 'desc')->first();;
    }
}
