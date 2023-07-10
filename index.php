<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->load();

use RPMS\APP\Log\SystemLog;
use RPMS\APP\Payment\Mpesa;
use RPMS\APP\Util\PhoneNumber;
use RPMS\APP\Security\Encryption;
use RPMS\APP\Payment\MpesaCallBack;

$phoneNumber = PhoneNumber::format('0798749323');


if ($phoneNumber === false) {
    try {
        $systemLog = new SystemLog('phone_formatter');
    } catch (\Exception $e) {
        SystemLog::log($e->getMessage());
    }
    
    return $systemLog->info("Invalid phone number passed");
}




// header('Content-Type: application/json');

// $mpesaResponse = file_get_contents('php://input');

// $key = $_GET['key'] ?? "key not set";

// $callbackData = json_decode($mpesaResponse, true);
// $callbackData['productId'] = Encryption::make($_ENV['MPESA_SALTED_IV'])->decrypt($key);

// $response = MpesaCallBack::handleMpesaCallback(new SystemLog('mpesa-callback'), $callbackData);

// echo json_encode($response);