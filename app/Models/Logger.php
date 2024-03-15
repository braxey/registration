<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class Logger
{
    public static function info(string $identifier, string $message)
    {
        Log::info(self::createFullMessage($identifier, $message));
    }

    public static function error(string $identifier, string $message)
    {
        Log::error(self::createFullMessage($identifier, $message));
    }

    public static function warning(string $identifier, string $message)
    {
        Log::warning(self::createFullMessage($identifier, $message));
    }

    private static function createFullMessage(string $identifier, string $message): string
    {
        return "$identifier -- $message";
    }
}
