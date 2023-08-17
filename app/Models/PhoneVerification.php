<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    protected $fillable = ['token', 'time_sent', 'user_id'];

    // Function to retrieve the most recent token for a given user_id
    public static function getMostRecentToken($userId)
    {
        return self::where('user_id', $userId)
            ->orderBy('time_sent', 'desc')
            ->first()
            ->token ?? '';
    }
}
