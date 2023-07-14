<?php

namespace RPMS\App\Security;

use Josantonius\Session\Facades\Session;
use Josantonius\Session\Exceptions\HeadersSentException;
use Josantonius\Session\Exceptions\SessionStartedException;

class SessionManager extends Session
{
    public static function init(array $options = [])
    {
        if (parent::isStarted()) {
            throw new SessionStartedException('Session has already been started.');
        }

        if (headers_sent()) {
            throw new HeadersSentException('Headers have already been sent.', 0);
        }

        parent::start($options);
    }

    public static function setSessionValue($key, $value)
    {
        self::checkSessionStarted();

        parent::set($key, $value);
    }

    public static function getSessionValue($key)
    {
        self::checkSessionStarted();

        return parent::get($key);
    }

    public static function delete($key)
    {
        self::checkSessionStarted();

        parent::remove($key);
    }

    public static function destroySession()
    {
        self::checkSessionStarted();

        parent::destroy();
    }

    public static function flash($key, $value)
    {
        self::checkSessionStarted();

        parent::set($key, $value);
    }

    public static function getAllSessionValue()
    {
        self::checkSessionStarted();

        return parent::all();
    }

    private static function checkSessionStarted()
    {
        if (!parent::isStarted()) {
            throw new SessionStartedException('Session has not been started.');
        }
    }
}