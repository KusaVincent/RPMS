<?php

namespace RPMS\App\Notification;

use RPMS\App\Util\Curl;
use RPMS\App\Log\LogHandler;

class MobiTechSMS
{
    private string $apiKey;
    private string $baseUrl;
    private string $logName;
    private string $senderName;

    public function __construct(string $apiKey, string $senderName)
    {
        $this->apiKey     = $apiKey;
        $this->senderName = $senderName;
        $this->logName    = 'mobitech-sms';
        $this->baseUrl    = "https://api.mobitechtechnologies.com/sms";
    }

    public function send(array $recipients, array $messages): array
    {
        if (count($recipients) !== count($messages)) {                
            LogHandler::handle($this->logName, "Number of recipients and messages should be equal.");
            throw new \Exception("Number of recipients and messages should be equal.");
        }

        $url = $this->baseUrl . "/sendsms";
        $results = [];

        foreach ($recipients as $index => $mobile) {
            $message = $messages[$index];

            $data = [
                "service_id" => 0,
                "mobile" => $mobile,
                "message" => $message,
                "response_type" => "json",
                "sender_name" => $this->senderName
            ];

            $payload = json_encode($data);

            $curlHeader = [
                'Content-Type: application/json',
                'h_api_key: ' . $this->apiKey
            ];

            try {
                $response = Curl::call($url, $curlHeader, 'post', $payload);
                $results[$mobile] = $response;
            } catch (\Exception $e) {
                LogHandler::handle($this->logName, "SMS sending failed to $mobile: " . $e->getMessage());
                $results[$mobile] = "SMS sending failed: " . $e->getMessage();
            }
        }

        return $results;
    }
}