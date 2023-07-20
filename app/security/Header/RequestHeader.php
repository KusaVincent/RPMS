<?php
declare(strict_types=1);

namespace App\Security\Header;

use App\Log\LogHandler;
use App\Security\ImmutableVariable;

class RequestHeader
{
    private static string $baseURI;
    private static string $baseURL;
    private static string $cookieLife;
    private static string $methodArray;
    private static string $allowedOrigins;

    public static function setRequestHeader(bool $api = false): void
    {
        self::$baseURI    = ImmutableVariable::getValueAndDecryptBeforeUse('baseURI');
        self::$baseURL    = ImmutableVariable::getValueAndDecryptBeforeUse('baseURL');
        self::$cookieLife = ImmutableVariable::getValueAndDecryptBeforeUse('cookieLife');

        self::$methodArray    = ImmutableVariable::getValueAndDecryptBeforeUse('methodArray');
        self::$allowedOrigins = ImmutableVariable::getValueAndDecryptBeforeUse('allowedOrigins');

        $methodArray    = explode(',', self::$methodArray);
        $allowedOrigins = explode(',', self::$allowedOrigins);
        
        $method = in_array($_SERVER['REQUEST_METHOD'], $methodArray) ? $_SERVER['REQUEST_METHOD'] : false;
        $origin = isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins) ? $_SERVER['HTTP_ORIGIN'] : self::$baseURI;

        ini_set('session.cookie_secure', true);
        ini_set('session.use_trans_sid', false);
        ini_set('session.cookie_httponly', true);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', true);
        ini_set('session.user_strict_mode', true);
        ini_set('session.gc_maxlifetime', self::$cookieLife);
        ini_set('session.cookie_lifetime', self::$cookieLife);
        ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);

        if($api) {
            if(!$method){
                $message = "Access-Control-Allow-Methods header not allowed: Method=" . $_SERVER['REQUEST_METHOD'];
                LogHandler::handle('header', $message);
                throw new \Exception($message);
            }

            HeaderSetting::setHeader('Access-Control-Allow-Methods', $method);
            $contentType = 'application/json';
        }

        $contentType = $contentType ?? 'text/html';

        HeaderSetting::setHeader('Access-Control-MAX-Age', '86400');
        HeaderSetting::setHeader('X-XSS-Protection', '1; mode=block');
        HeaderSetting::setHeader('X-Content-Type-Options', 'nosniff');
        HeaderSetting::setHeader('Cache-Control', 'private, no-cache');
        HeaderSetting::setHeader("Access-Control-Allow-Origin", $origin);
        HeaderSetting::setHeader('Access-Control-Allow-Credentials', 'true');
        HeaderSetting::setHeader('Content-Type', $contentType . '; charset=UTF-8');
        HeaderSetting::setHeader('Content-Security-Policy', "frame-ancestors 'self'");
        HeaderSetting::setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        HeaderSetting::setHeader('Permissions-Policy', 'geolocation=(self ' . self::$baseURL . '), microphone=()');
        HeaderSetting::setHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Authorization, Content-Type, X-Requested-With, X-Debug');
        HeaderSetting::setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubdomains');
    }
}