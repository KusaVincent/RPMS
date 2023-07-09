<?php
// declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');

use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->load();

use RPMS\APP\Log\SystemLog;
use RPMS\APP\Payment\Mpesa;
use RPMS\APP\Util\PhoneNumber;

$phoneNumber = PhoneNumber::format('0798749323');


if ($phoneNumber === false) {
    try {
        $systemLog = new SystemLog('phone_formatter');
    } catch (\Exception $e) {
        SystemLog::log($e->getMessage());
    }
    
    return $systemLog->info("Invalid phone number passed");
}

$samplePaymentData = [
    'amount'            => 1,
    'businessShortCode' => '174379',
    'phoneNumber'       => $phoneNumber,
    'description'       => 'TEST PAYBILL',
    'accountReference'  => 'Rentals Konekt',
    'passKey'           => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'
];

$mpesa = new Mpesa('Mbktov5dSAHoBqc3yAAewzWKwcRD1sWR', 'E74GuudetmaLzJmu');
var_dump($mpesa->call($samplePaymentData));