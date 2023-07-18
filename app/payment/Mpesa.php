<?php

namespace RPMS\App\Payment;

use Carbon\Carbon;
use RPMS\App\Util\Curl;
use RPMS\App\Log\LogHandler;
use RPMS\App\Security\Encryption;
use RPMS\App\Security\ImmutableVariable;

class Mpesa
{
    private string $logName;
    private string $consumerKey;
    private string $accessToken;
    private string $consumerSecret;
    private string $currentTimestamp;

    private string $queryURL;
    private string $tokenURL;
    private string $endpointURL;
    private string $callbackURL;
    private string $mpesaSaltedIV;
    private string $safaricomBaseURL;

    public function __construct(string $consumerKey, string $consumerSecret)
    {
        $this->logName          = 'mpesa';
        $this->consumerKey      = $consumerKey;
        $this->consumerSecret   = $consumerSecret;
        $this->accessToken      = $this->generateAccessToken();
        $this->currentTimestamp = Carbon::now()->format('YmdHms');

        $this->mpesaSaltedIV    = ImmutableVariable::getValue('mpesaSaltedIV');
        $this->tokenURL         = ImmutableVariable::getValueAndDecryptBeforeUse('tokenURL');
        $this->queryURL         = ImmutableVariable::getValueAndDecryptBeforeUse('queryURL');
        $this->endpointURL      = ImmutableVariable::getValueAndDecryptBeforeUse('endpointURL');
        $this->callbackURL      = ImmutableVariable::getValueAndDecryptBeforeUse('callbackURL');
        $this->safaricomBaseURL = ImmutableVariable::getValueAndDecryptBeforeUse('safaricomBaseURL');
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
            $curlResponse = Curl::call($this->safaricomBaseURL . $this->tokenURL , $curlHeader, 'token');
            $accessToken = json_decode($curlResponse);
            return $accessToken->access_token;
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, $e->getMessage());
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
            'CallBackURL'       => $this->callbackURL . '?key=' . Encryption::salt($this->mpesaSaltedIV)->encrypt($stkValues['paymentId'])
        ];

        $dataString = json_encode($curlPostData);
        $curlHeader = array('Content-Type:application/json', 'Authorization:Bearer ' . $this->accessToken);

        try {
            $curlResponse = Curl::call($this->safaricomBaseURL . $this->endpointURL, $curlHeader, 'stk_push', $dataString);
            $decodedCurlResponse = json_decode($curlResponse, true);
            $checkoutRequestID = $decodedCurlResponse['ResponseCode'] == "0" ? $decodedCurlResponse['CheckoutRequestID'] : "";
            return $checkoutRequestID;
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, $e->getMessage());
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
            return Curl::call($this->safaricomBaseURL . $this->queryURL, $curlHeader, 'stk_status_response', json_encode($curlPostData));
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, $e->getMessage());
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
            'paymentId'         => $paymentData['paymentId'],
            'phoneNumber'       => $paymentData['phoneNumber'],
            'description'       => $paymentData['description'],
            'accountReference'  => $paymentData['accountReference'],
            'businessShortCode' => $paymentData['businessShortCode']
        ];

        $stkStatusValues = array_slice($stkValues, 0, 2, true);
        $stkStatusValues += array_slice($stkValues, 6, 1, true);

        $stkStatusValues['CheckoutRequestID'] = $this->stkPush($stkValues);

        return $this->checkStkPush($stkStatusValues);
    }
}