<?php

namespace RPMS\App\Security;

use Volnix\CSRF\CSRF;

class CSRFToken {
    private static $tokenName = CSRF::TOKEN_NAME;
    
    public static function getTokenName() {
        return self::$tokenName;
    }
    
    public static function generate() {
        return CSRF::getToken();
    }
    
    public static function getInputString() {
        return CSRF::getHiddenInputString(self::$tokenName);
    }
    
    public static function getTheQueryString() {
        return CSRF::getQueryString(self::$tokenName);
    }
    
    public static function verify($data, $tokenName = null) {
        if (is_null($tokenName)) $tokenName = self::$tokenName;
        
        return CSRF::validate($data, $tokenName);
    }
}