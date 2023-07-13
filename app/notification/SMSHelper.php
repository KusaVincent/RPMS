<?php

namespace RPMS\App\Notification;

use RPMS\App\Log\LogHandler;
use RPMS\App\Notification\MobiTechSMS;
use RPMS\App\Notification\AfricasTalkingSMS;

class SMSHelper {

    public static function sendSMS(
        string $name, 
        string $apiKey, 
        array $recipients, 
        array | string $message, 
        string $merchant = 'ATS'
    ) : array
    {
        if($merchant === 'MTS') return self::mobiTechSMS($name, $apiKey, $recipients, $message);

        return self::africasTalkingSMS($name, $apiKey, $recipients, $message);
    }

    private static function mobiTechSMS(string $name, string $apiKey, array $recipients, array | string $message)
    {
        $response   = [];
        $smsSender  = new MobiTechSMS($name, $apiKey);

        try {
            $results = $smsSender->send($recipients, $message);
            
            foreach ($results as $recipient => $result) {
                $response += array(
                    $recipient => "SMS sent to $recipient: $result" . PHP_EOL
                );
            }

            return $response;
        } catch (\Exception $e) {
            $error = 'Bulk SMS sending failed';
            LogHandler::handle('mobitech-sms', $error . ': ' . $e->getMessage());
            return $response += array($error);
        }
    }

    private static function africasTalkingSMS(string $name, string $apiKey, array $recipients, array | string $message) 
    {
        $response  = [];
        $smsSender = new AfricasTalkingSMS($name, $apiKey);

        try {
            $results = $smsSender->send($recipients, $message);
            
            foreach ($results as $recipient => $result) {
                $response += array(
                    $recipient => "SMS sent to $recipient: " . json_encode($result) . PHP_EOL
                );
            }

            return $response;
        } catch (\Exception $e) {
            $error = 'Bulk SMS sending failed';
            LogHandler::handle('africas-talking-sms', $error . ': ' . $e->getMessage());
            return $response += array($error);
        }
    }
}