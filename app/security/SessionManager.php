<?php
declare(strict_types=1);

namespace App\Security;

use Josantonius\Session\Facades\Session;
use Josantonius\Session\Exceptions\{HeadersSentException, SessionStartedException};

class SessionManager extends Session
{
    public static function init(array $options = []) : void
    {
        if (parent::isStarted()) {
            throw new SessionStartedException('Session has already been started.');
        }

        if (headers_sent()) {
            throw new HeadersSentException('Headers have already been sent.', 0);
        }

        parent::start($options);
    }

    public static function setSessionValue(string $key, $value) : void
    {
        self::checkSessionStarted();

        parent::set($key, $value);
    }

    public static function getSessionValue(string $key) : mixed
    {
        self::checkSessionStarted();

        return parent::get($key);
    }

    public static function delete(string $key) : void
    {
        self::checkSessionStarted();

        parent::remove($key);
    }

    public static function destroySession() : void
    {
        self::checkSessionStarted();

        parent::destroy();
    }

    public static function flash(string $key, $value) : void
    {
        self::checkSessionStarted();

        parent::set($key, $value);
    }

    public static function getAllSessionValue() : array
    {
        self::checkSessionStarted();

        return parent::all();
    }

    private static function checkSessionStarted() : void
    {
        if (!parent::isStarted()) {
            throw new SessionStartedException('Session has not been started.');
        }
    }
}