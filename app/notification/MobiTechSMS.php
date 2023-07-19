<?php
declare(strict_types=1);

namespace RPMS\App\Notification;

use RPMS\App\Util\Curl;
use RPMS\App\Log\LogHandler;
use RPMS\App\Security\ImmutableVariable;

class MobiTechSMS
{
    private string $apiKey;
    private string $logName;
    private string $senderName;
    private string $mobitechBaseUrl;

    public function __construct(string $senderName, string $apiKey)
    {
        $this->apiKey           = $apiKey;
        $this->senderName       = $senderName;
        $this->logName          = 'mobitech-sms';
        $this->mobitechBaseUrl  = ImmutableVariable::getValueAndDecryptBeforeUse('mobitechBaseUrl');
    }

    public function send(array $recipients, array | string $messages): array
    {
        if (is_array($messages)) {
            if (count($recipients) !== count($messages)) {                
                LogHandler::handle($this->logName, "Number of recipients and messages should be equal.");
                throw new \Exception("Number of recipients and messages should be equal.");
            }
        } else {
            $messages = array_fill(0, count($recipients), $messages);
        }

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
                $response = Curl::call($this->mobitechBaseUrl, $curlHeader, 'post', $payload);
                $results[$mobile] = $response;
            } catch (\Exception $e) {
                LogHandler::handle($this->logName, "SMS sending failed to $mobile: " . $e->getMessage());
                $results[$mobile] = "SMS sending failed: " . $e->getMessage();
            }
        }

        return $results;
    }
}