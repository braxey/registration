<?php

use Carbon\Carbon;

/**
 * Get Carbon-parsed time ranges
 */
function getBetween(array $arr): array
{
    $container = [];

    try {
        if (isset($arr['start_date'])) {
            if (isset($arr['start_time'])) {
                $dateTimeString = $arr['start_date'] . ' ' . $arr['start_time'];
            } else {
                $dateTimeString = $arr['start_date'] . ' 00:00';
            }

            $container['start'] = Carbon::parse($dateTimeString);
        }
    } catch (Exception $e) {
        unset($container['start']);
    }

    try {
        if (isset($arr['end_date'])) {
            if (isset($arr['end_time'])) {
                $dateTimeString = $arr['end_date'] . ' ' . $arr['end_time'];
            } else {
                $dateTimeString = $arr['end_date'] . ' 23:59';
            }

            $container['end'] = Carbon::parse($dateTimeString);
        }
    } catch (Exception $e) {
        unset($container['end']);
    }
    
    return $container;
}

/**
 * Version files for cache busting
 */
function version(string $file): string
{
    try {
        $cacheBuster = filemtime($file);
    } catch (Exception $e) {
        $cacheBuster = (string) random_int(0, 9999999);
    }
    return asset($file) . '?v=' . $cacheBuster;
}

/**
 * Generate cryptographically secure, numeric token
 */
function generateSecureNumericToken($length = 7)
{
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    $randomNumber = random_int($min, $max);
    return str_pad($randomNumber, $length, '0', STR_PAD_LEFT);
}
