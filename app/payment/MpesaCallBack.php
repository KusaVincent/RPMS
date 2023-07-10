<?php

namespace RPMS\APP\Payment;

use RPMS\APP\Log\LogHandler;

class MpesaCallBack
{
    public static function handleMpesaCallback(array $callbackData): array | string
    {
        $resultCode = $callbackData['Body']['stkCallback']['ResultCode'];
        $checkoutRequestID = $callbackData['Body']['stkCallback']['CheckoutRequestID'];

        if ($resultCode !== 0) {
            if(!isset($callbackData['Body']['stkCallback']['CallbackMetadata'])) {
                LogHandler::handle('mpesa-callback', json_encode($callbackData), 'info');
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