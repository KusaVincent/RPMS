<?php
declare(strict_types=1);

namespace App\Security;

use Volnix\CSRF\CSRF;

class CSRFToken {
    private static $tokenName = CSRF::TOKEN_NAME;
    
    public static function getTokenName()  : string
    {
        return self::$tokenName;
    }
    
    public static function generate()  : string
    {
        return CSRF::getToken();
    }
    
    public static function getInputString()  : string
    {
        return CSRF::getHiddenInputString(self::$tokenName);
    }
    
    public static function getTheQueryString()  : string
    {
        return CSRF::getQueryString(self::$tokenName);
    }
    
    public static function verify($data, $tokenName = null)  : bool
    {
        if (is_null($tokenName)) $tokenName = self::$tokenName;
        
        return CSRF::validate($data, $tokenName);
    }
}