<?php
declare(strict_types=1);

namespace RPMS\App\Notification;

use RPMS\App\Log\LogHandler;
use RPMS\App\Security\ImmutableVariable;

class SMSHelper {
    private static string $mobitechApiKey;
    private static string $mobitechSenderName;

    private static string $africasTalkingApiKey;
    private static string $africasTalkingUserName;

    private static function init() : void
    {
        self::$mobitechApiKey         = ImmutableVariable::getValueAndDecryptBeforeUse('mobitechApiKey');
        self::$mobitechSenderName     = ImmutableVariable::getValueAndDecryptBeforeUse('mobitechSenderName');
        self::$africasTalkingApiKey   = ImmutableVariable::getValueAndDecryptBeforeUse('africasTalkingApiKey');
        self::$africasTalkingUserName = ImmutableVariable::getValueAndDecryptBeforeUse('africasTalkingUserName');
    }

    public static function sendSMS(
        array $recipients, 
        array | string $message, 
        string $merchant = 'ATS'
    ) : array
    {
        self::init();

        if($merchant === 'MTS') return self::mobiTechSMS($recipients, $message);

        return self::africasTalkingSMS($recipients, $message);
    }

    private static function mobiTechSMS(array $recipients, array | string $message) : array
    {
        $response   = [];
        $smsSender  = new MobiTechSMS(self::$mobitechSenderName, self::$mobitechApiKey);

        try {
            $results = $smsSender->send($recipients, $message);
            
            foreach ($results as $recipient => $result) {
                $response += array(
                    $recipient => "MTS SMS sent to $recipient: $result" . PHP_EOL
                );
            }

            return $response += ["success"];
        } catch (\Exception $e) {
            $error = 'Bulk SMS sending failed';
            LogHandler::handle('mobitech-sms', $error . ': ' . $e->getMessage());
            return $response += array($error);
        }
    }

    private static function africasTalkingSMS(array $recipients, array | string $message)  : array
    {
        $response  = [];
        $smsSender = new AfricasTalkingSMS(self::$africasTalkingUserName, self::$africasTalkingApiKey);

        try {
            $results = $smsSender->send($recipients, $message);
            
            foreach ($results as $recipient => $result) {
                $response += array(
                    $recipient => "ATS SMS sent to $recipient: " . json_encode($result) . PHP_EOL
                );
            }

            return $response += ["success"];
        } catch (\Exception $e) {
            $error = 'Bulk SMS sending failed';
            LogHandler::handle('africas-talking-sms', $error . ': ' . $e->getMessage());
            return $response += array($error);
        }
    }
}