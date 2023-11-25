<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PhoneVerification extends Model
{
    protected $fillable = ['token', 'time_sent', 'user_id'];

    public function getToken(): string
    {
        return $this->token?? '';
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): User
    {
        return User::fromId($this->getUserId());
    }

    public function isValidToken(string $token): bool
    {
        return strlen($token) === 7 && $this->getToken() === $token;
    }

    public function verify()
    {
        $user = $this->getUser();
        static::where('user_id', $user->getId())->delete();
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

    public static function logTokenSend(User $user, string $token)
    {
        static::create([
            'token'     => $token,
            'time_sent' => now(),
            'user_id'   => $user->getId(),
        ]);
    }
}
