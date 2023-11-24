<?php

use Carbon\Carbon;

function formatPhoneBrackets($phone): string
{
    $phone = preg_replace("/[^0-9]/", "", $phone);

    switch (strlen($phone)) {
        case 7:
            $formattedPhone = preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            break;
        case 10:
            $formattedPhone = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        case 11:
            $phone = ($phone[0] == "1") ? substr($phone, 1) : $phone;
            $formattedPhone = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        default:
            $formattedPhone = $phone;
    }
    
    return $formattedPhone;
}

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