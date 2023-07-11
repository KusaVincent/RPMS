<?php

namespace RPMS\App\Security\Header;

use RPMS\App\Security\Header\HeaderSetting;

class RequestHeader
{
    public static function setRequestHeader(array $allowedOrigins): void
    {
        $methodArray = ['GET', 'PUT', 'POST', 'DELETE'];
        $method = in_array($_SERVER['REQUEST_METHOD'], $methodArray);

        ini_set('session.cookie_secure', true);
        ini_set('session.cookie_httponly', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.user_strict_mode', true);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', $_ENV['LIFE']);
        ini_set('session.cookie_lifetime', $_ENV['LIFE']);
        ini_set('session.use_trans_sid', false);
        ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);

        $origin = !defined('API') ? $_ENV['BASE_URI'] : $allowedOrigins;
        header("Access-Control-Allow-Origin: $origin");

        if (defined('API')) {
            HeaderSetting::setHeader('Access-Control-Allow-Methods', $method);
        }

        HeaderSetting::setHeader('Access-Control-MAX-Age', '86400');
        HeaderSetting::setHeader('X-XSS-Protection', '1; mode=block');
        HeaderSetting::setHeader('X-Content-Type-Options', 'nosniff');
        HeaderSetting::setHeader('Cache-Control', 'private, no-cache');
        HeaderSetting::setHeader('Access-Control-Allow-Credentials', 'true');
        HeaderSetting::setHeader('Content-Type', 'application/json; charset=UTF-8');
        HeaderSetting::setHeader('Content-Security-Policy', "frame-ancestors 'self'");
        HeaderSetting::setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        HeaderSetting::setHeader('Permissions-Policy', 'geolocation=(self ' . $_ENV['BASE_URL'] . '), microphone=()');
        HeaderSetting::setHeader('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Authorization, Content-Type, X-Requested-With, X-Debug');
        // HeaderSetting::setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubdomains');
    }
}