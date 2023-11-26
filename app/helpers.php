<?php

use Carbon\Carbon;

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

function version(string $file): string
{
    return asset($file) . '?v=' . filemtime($file);
}
