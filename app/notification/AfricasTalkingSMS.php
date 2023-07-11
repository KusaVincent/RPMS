<?php
namespace RPMS\App\Notification;

use RPMS\App\Log\LogHandler;
use AfricasTalking\SDK\AfricasTalking;

class AfricasTalkingSMS
{
    private object $sms;
    private string $apiKey;
    private string $logName;
    private string $username;

    public function __construct(string $username, string $apiKey)
    {
        $this->apiKey   = $apiKey;
        $this->username = $username;
        $this->logName  = 'africas-talking-sms';

        try {
            $africasTalking = new AfricasTalking($this->username, $this->apiKey);
            $this->sms = $africasTalking->sms();
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, "Failed to initialize AfricasTalking: " . $e->getMessage());
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
            LogHandler::handle($this->logName, "Failed to send bulk SMS: " . $e->getMessage());
            throw new \Exception("Failed to send bulk SMS: " . $e->getMessage());
        }
    }
}