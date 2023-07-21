<?php

namespace App\Util;

class VerifyEmail {
    private static array $typeArray = ['MX', 'A'];

    public static function check(string $email) {
        $domain   = substr($email, strpos($email, '@') + 1);

        foreach(self::$typeArray as $type)
        {
            if(checkdnsrr($domain, $type)) return $email . ' DNS found in ' . $type;
        }

        throw new \Exception($email . ' DNS not found');
    }
}

