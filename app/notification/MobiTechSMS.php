<?php

namespace RPMS\APP\Notification;

use RPMS\APP\Util\Curl;
use RPMS\APP\Log\SystemLog;

class MobiTechSMS
{
    private $apiKey;
    private $baseUrl;
    private $systemLog;
    private $senderName;

    public function __construct(string $apiKey, string $senderName)
    {
        $this->apiKey       = $apiKey;
        $this->senderName   = $senderName;
        $this->systemLog    = new SystemLog('mobitech-sms');
        $this->baseUrl      = "https://api.mobitechtechnologies.com/sms";
    }

    public function send(array $recipients, array $messages): array
    {
        if (count($recipients) !== count($messages)) {
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
                $response = Curl::call($this->systemLog, $url, $curlHeader, 'post', $payload);
                $results[$mobile] = $response;
            } catch (\Exception $e) {
                $this->systemLog->error("SMS sending failed to $mobile: " . $e->getMessage());
                $results[$mobile] = "SMS sending failed: " . $e->getMessage();
            }
        }

        return $results;
    }
}