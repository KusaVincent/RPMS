<?php

namespace RPMS\APP\Log;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

class SystemLog
{
    private $log;

    public function __construct(string $name, string|int|Level $logLevel = Level::Debug, string $filename = 'rpms')
    {
        $name = ucfirst($name);
        $this->log = new Logger($name);
        $this->log->pushHandler(new RotatingFileHandler(filename: __DIR__ . '-' . $filename . '.log', level: $logLevel));
    }

    public static function log(string $message): void
    {
        $log = new Logger('log-error');
        $log->pushHandler(new RotatingFileHandler(filename: __DIR__ . '-' . 'logger' . '.log', level:  Level::Error));
        $log->error($message);
    }
    
    public function info(string $message) : void
    {
        $this->log->info($message);
    }

    public function error(string $message) : void
    {
        $this->log->error($message);
    }

    public function debug(string $message) : void
    {
        $this->log->debug($message);
    }

    public function warning(string $message) : void
    {
        $this->log->warning($message);
    }
}