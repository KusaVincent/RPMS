<?php

namespace RPMS\App\Util;

use RPMS\App\Security\ImmutableVariable;

class PhoneNumber {
    
    public static function format(string $phoneNumber): string | bool
    {
        $numLength = strlen($phoneNumber);
        $countryCode = ImmutableVariable::getValue('countryCode');
        
        if ($numLength < 9 || $numLength > 12) {
            return false;
        }

        $firstLetter = substr($phoneNumber, 0, 1);

        switch ($firstLetter) {
            case '0':
                $formattedPhoneNumber = substr($phoneNumber, 1); // 79*****45
                break;
            case '2':
                $formattedPhoneNumber = substr($phoneNumber, 3); // 79*****45
                break;
            case '+':
                $formattedPhoneNumber = substr($phoneNumber, 4); // 79*****45
                break;
            default:
                if ($numLength !== 9 || $firstLetter !== '7') {
                    return false;
                }
                $formattedPhoneNumber = $phoneNumber;
        }

        if (!is_numeric($formattedPhoneNumber)) {
            return false;
        }

        return $countryCode . $formattedPhoneNumber; // 25479*****45
    }
}