<?php

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