<?php
declare(strict_types=1);

namespace RPMS\App\Util;

use RPMS\App\Log\LogHandler;

class Curl
{
    public static function call(string $url, array $curlHeader, string $methodFor, ?string $payload = null): string | bool
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);

        switch ($methodFor) {
            case 'token':
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                break;
            case 'post':
            case 'stk_push':
            case 'stk_status_response':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($curl, CURLOPT_HEADER, false);
                break;
        }

        $curlResponse = curl_exec($curl);
        
        $logName = 'curl';

        if ($curlResponse === false) {
            $error = curl_error($curl);
            curl_close($curl);

            LogHandler::handle($logName, "cURL Error: " . $error);
            throw new \Exception("cURL Error: " . $error);
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode >= 400) {
            LogHandler::handle($logName, "HTTP Error: " . $httpCode);
            throw new \Exception("HTTP Error: " . $httpCode);
        }

        return $curlResponse;
    }
}