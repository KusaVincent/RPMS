<?php

namespace RPMS\APP\Payment;

use Carbon\Carbon;
use RPMS\APP\Util\Curl;
use RPMS\APP\Log\SystemLog;
use RPMS\APP\Security\Encryption;

class Mpesa
{
    private $systemLog;
    private $consumerKey;
    private $accessToken;
    private $curlSystemLog;
    private $consumerSecret;
    private $currentTimestamp;

    public function __construct(string $consumerKey, string $consumerSecret)
    {
        $this->consumerKey      = $consumerKey;
        $this->curlSystemLog    = new SystemLog('curl');
        $this->systemLog        = new SystemLog('mpesa');
        $this->consumerSecret   = $consumerSecret;
        $this->accessToken      = $this->generateAccessToken();
        $this->currentTimestamp = Carbon::now()->format('YmdHms');
    }

    private function lipaNaMpesaPassword(int $businessShortCode, string $passKey): string
    {
        return base64_encode($businessShortCode . $passKey . $this->currentTimestamp);
    }

    private function generateAccessToken(): string
    {
        $credential = base64_encode($this->consumerKey . ":" . $this->consumerSecret);
        $curlHeader = array("Authorization: Basic " . $credential, "Content-Type:application/json");

        try {
            $curlResponse = Curl::call($this->curlSystemLog, $_ENV['SAFARICOM_BASE_URL'] . $_ENV['TOKEN_URL'], $curlHeader, 'token');
            $accessToken = json_decode($curlResponse);
            return $accessToken->access_token;
        } catch (\Exception $e) {
            try {
                $this->systemLog->error($e->getMessage());
            } catch (\Exception $ex) {
                SystemLog::log($ex->getMessage());
            }

            throw $e;
        }
    }

    private function stkPush(array $stkValues): string
    {
        $curlPostData = [
            'Amount'            => $stkValues['amount'],
            'Password'          => $stkValues['password'],
            'Timestamp'         => $this->currentTimestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'TransactionDesc'   => $stkValues['description'],
            'PhoneNumber'       => $stkValues['phoneNumber'],
            'PartyA'            => $stkValues['phoneNumber'],
            'AccountReference'  => $stkValues['accountReference'],
            'PartyB'            => $stkValues['businessShortCode'],
            'BusinessShortCode' => $stkValues['businessShortCode'],
            'CallBackURL'       => $_ENV['CALLBACK_URL'] . '?key=' . Encryption::make($stkValues['businessShortCode'])->encrypt($stkValues['phoneNumber'])
        ];

        $dataString = json_encode($curlPostData);
        $curlHeader = array('Content-Type:application/json', 'Authorization:Bearer ' . $this->accessToken);

        try {
            $curlResponse = Curl::call($this->curlSystemLog, $_ENV['SAFARICOM_BASE_URL'] . $_ENV['ENDPOINT_URL'], $curlHeader, 'stk_push', $dataString);
            $decodedCurlResponse = json_decode($curlResponse, true);
            $checkoutRequestID = $decodedCurlResponse['ResponseCode'] == "0" ? $decodedCurlResponse['CheckoutRequestID'] : "";
            return $checkoutRequestID;
        } catch (\Exception $e) {
            try {
                $this->systemLog->error($e->getMessage());
            } catch (\Exception $ex) {
                SystemLog::log($ex->getMessage());
            }

            throw $e;
        }
    }

    private function checkStkPush(array $stkStatusValues): array
    {
        $stkCurlResponse = $this->checkStkPushStatus($stkStatusValues);
        $decodedStkResponse = json_decode($stkCurlResponse, true);

        if (!$decodedStkResponse || sizeof($decodedStkResponse) < 6) {
            return $this->checkStkPush($stkStatusValues);
            // Call your function to keep on checking for M-Pesa response
        }

        $resultCode = $decodedStkResponse['ResultCode'];
        $checkoutRequestID = $decodedStkResponse['CheckoutRequestID'];

        return $this->checkoutResponse($resultCode, $checkoutRequestID);
    }

    private function checkStkPushStatus(array $stkStatusValues): string | bool
    {
        $curlPostData = [
            'Timestamp'         => $this->currentTimestamp,
            'Password'          => $stkStatusValues['password'],
            'BusinessShortCode' => $stkStatusValues['businessShortCode'],
            'CheckoutRequestID' => $stkStatusValues['CheckoutRequestID']
        ];

        $curlHeader = ['Content-Type:application/json', 'Authorization:Bearer ' . $this->accessToken];

        try {
            return Curl::call($this->curlSystemLog, $_ENV['SAFARICOM_BASE_URL'] . $_ENV['QUERY_URL'], $curlHeader, 'stk_status_response', json_encode($curlPostData));
        } catch (\Exception $e) {
            try {
                $this->systemLog->error($e->getMessage());
            } catch (\Exception $ex) {
                SystemLog::log($ex->getMessage());
            }

            throw $e;
        }
    }

    private function checkoutResponse(int $resultCode, string $checkoutRequestID): array
    {
        $result = [
            'CheckoutRequestID' => null,
            'paymentResponse' => ""
        ];

        switch ($resultCode) {
            case 0:
                $result['paymentResponse'] = "successful";
                $result['CheckoutRequestID'] = $checkoutRequestID;
                break;
            case 1:
                $result['paymentResponse'] = "insufficient";
                break;
            case 1032:
                $result['paymentResponse'] = "cancelled";
                break;
            case 1037:
                $result['paymentResponse'] = "timeout";
                break;
            default:
                $result['paymentResponse'] = "error";
        }

        return $result;
    }

    public function call(array $paymentData): array
    {
        $password = $this->lipaNaMpesaPassword($paymentData['businessShortCode'], $paymentData['passKey']);

        $stkValues = [
            'password'          => $password,
            'amount'            => $paymentData['amount'],
            'phoneNumber'       => $paymentData['phoneNumber'],
            'description'       => $paymentData['description'],
            'accountReference'  => $paymentData['accountReference'],
            'businessShortCode' => $paymentData['businessShortCode']
        ];

        $stkStatusValues = array_slice($stkValues, 0, 2, true);
        $stkStatusValues += array_slice($stkValues, 5, 1, true);

        $stkStatusValues['CheckoutRequestID'] = $this->stkPush($stkValues);

        return $this->checkStkPush($stkStatusValues);
    }
}