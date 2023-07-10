<?php
namespace RPMS\APP\Notification;

use RPMS\APP\Log\SystemLog;
use AfricasTalking\SDK\AfricasTalking;

class AfricasTalkingSMS
{
    private $sms;
    private $apiKey;
    private $username;
    private $systemLog;

    public function __construct(string $username, string $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;

        $this->systemLog = new SystemLog('africas-talking-sms');

        try {
            $africasTalking = new AfricasTalking($this->username, $this->apiKey);
            $this->sms = $africasTalking->sms();
        } catch (\Exception $e) {
            try {
                $this->systemLog->error("Failed to initialize AfricasTalking: " . $e->getMessage());
            } catch (\Exception $ex) {
                SystemLog::log($ex->getMessage());
            }

            throw new \Exception("Failed to initialize AfricasTalking: " . $e->getMessage());
        }
    }

    public function send(array $recipients, string $message): array
    {
        try {
            $results = [];
            
            foreach ($recipients as $recipient) {
                $result = $this->sms->send([
                    'to' => $recipient,
                    'message' => $message,
                ]);

                $results[$recipient] = $result;
            }

            return $results;
        } catch (\Exception $e) {
            try {
                $this->systemLog->error("Failed to send bulk SMS: " . $e->getMessage());
            } catch (\Exception $ex) {
                SystemLog::log($ex->getMessage());
            }

            throw new \Exception("Failed to send bulk SMS: " . $e->getMessage());
        }
    }
}