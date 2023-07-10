<?php
namespace RPMS\APP\Util;

use RPMS\APP\Log\SystemLog;

class Curl
{
    public static function call(SystemLog $systemLog, string $url, array $curlHeader, string $methodFor, ?string $dataString = null): string | bool
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
            case 'stk_push':
            case 'stk_status_response':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                curl_setopt($curl, CURLOPT_HEADER, false);
                break;
        }

        $curlResponse = curl_exec($curl);

        if ($curlResponse === false) {
            $error = curl_error($curl);
            curl_close($curl);

            try {
                $systemLog->error($error);
            } catch (\Exception $e) {
                SystemLog::log('curl', 'error', $e->getMessage());
            }

            throw new \Exception("cURL Error: " . $error);
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode >= 400) {
            try {
                $systemLog->error($httpCode);
            } catch (\Exception $e) {
                SystemLog::log('curl', 'error', $e->getMessage());
            }

            throw new \Exception("HTTP Error: " . $httpCode);
        }

        return $curlResponse;
    }
}