<?php
declare(strict_types=1);

namespace App\Log;

class LogHandler extends SystemLog
{
    public static function handle(string $logName, string $errorMessage, string $severity = 'error'): void
    {
        try {
            $logHandler = new self($logName);

            switch ($severity) {
                case 'info':
                    $logHandler->info($errorMessage);
                    break;
                case 'error':
                    $logHandler->error($errorMessage);
                    break;
                case 'debug':
                    $logHandler->debug($errorMessage);
                    break;
                case 'warning':
                    $logHandler->warning($errorMessage);
                    break;
                default:
                    $logHandler->error("Invalid severity level: $severity");
            }
        } catch (\Exception $e) {
            SystemLog::log($e->getMessage());
        }
    }
}