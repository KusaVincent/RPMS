<?php
declare(strict_types=1);

namespace App\Util;

use App\Security\ImmutableVariable;

class PhoneNumber {
    
    public static function format(string $phoneNumber): string | bool
    {
        $numLength   = strlen($phoneNumber);
        $countryCode = ImmutableVariable::getValueAndDecryptBeforeUse('countryCode');
        
        if ($numLength < 9 || $numLength > 12) {
            return false;
        }

        $firstLetter = substr($phoneNumber, 0, 1);

        switch ($firstLetter) {
            case '0':
                $formattedPhoneNumber = substr($phoneNumber, 1);
                break;
            case '2':
                $formattedPhoneNumber = substr($phoneNumber, 3);
                break;
            case '+':
                $formattedPhoneNumber = substr($phoneNumber, 4);
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

        return $countryCode . $formattedPhoneNumber;
    }
}