<?php

namespace RPMS\APP\Payment;

use RPMS\APP\Log\SystemLog;

class MpesaCallBack
{
    public static function handleMpesaCallback(SystemLog $systemLog, array $callbackData): array | string
    {
        $resultCode = $callbackData['Body']['stkCallback']['ResultCode'];
        $checkoutRequestID = $callbackData['Body']['stkCallback']['CheckoutRequestID'];

        if ($resultCode !== 0) {
            if(!isset($callbackData['Body']['stkCallback']['CallbackMetadata'])) {
                try {
                    $systemLog->info(json_encode($callbackData));
                } catch (\Exception $e) {
                    SystemLog::log($e->getMessage());
                }

                return $callbackData['productId'] . "'s Request not successful";
            }
         }

        $metadata = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'];

        $result = array_column($metadata, 'Value', 'Name');

        $result['productId'] = $callbackData['productId'];
        
        $result['checkoutRequestID'] = $checkoutRequestID;

        return $result;
    }
}