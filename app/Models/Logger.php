<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;

class Logger
{
    public static function info(string $identifier, string $message)
    {
        Log::info($this->createFullMessage($identifier, $message));
    }

    public static function error(string $identifier, string $message)
    {
        Log::error($this->createFullMessage($identifier, $message));
    }

    public static function warning(string $identifier, string $message)
    {
        Log::warning($this->createFullMessage($identifier, $message));
    }

    private function createFullMessage(string $identifier, string $message): string
    {
        return "$identifier -- $message";
    }
}
