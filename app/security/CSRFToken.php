<?php

namespace RPMS\App\Security;

use RPMS\App\Security\SessionManager;

class CSRFToken {
    private static $csrfLifeTime;

    public function __construct()
    {
        SessionManager::init(['name' => 'JSESSID', 'cookie_lifetime' => 8000]);
        self::$csrfLifeTime = SessionManager::getSessionValue('csrf_time') + 3600;
    }
    
    public static function generate() {
        
        if (SessionManager::getSessionValue('csrf') !== null || 
        (SessionManager::getSessionValue('csrf_time') !== null) || self::$csrfLifeTime <= time()) {
            SessionManager::setSessionValue('csrf_time', time());
            SessionManager::setSessionValue('csrf' , bin2hex(random_bytes(32)));
        } 
        
        $csrfToken = hash_hmac('sha256', SessionManager::getSessionValue('csrf'), $_ENV['CSRF_KEY']);
        
        return $csrfToken;
    }
    
    public static function verify($csrfToken) {
        self::$csrfLifeTime = SessionManager::getSessionValue('csrf_time') + 3600;
        
        if (SessionManager::getSessionValue('csrf_time') !== null || 
        (SessionManager::getSessionValue('csrf_time') !== null && self::$csrfLifeTime <= time())) {
            SessionManager::delete('csrf');
            SessionManager::delete('csrf_time');
            
            return $warning['csrf_set_time'] = 305;
        } 
        
        if (!hash_equals(self::generate(), $csrfToken)) {
            SessionManager::delete('csrf'); 
            SessionManager::delete('csrf_time');
            
            return $warning['csrf_not_match'] = 304;
        } 
        
        SessionManager::delete('csrf');
        SessionManager::delete('csrf_time');
        
        return true;
    }
}
