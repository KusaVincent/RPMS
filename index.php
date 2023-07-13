<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;
use RPMS\App\Model\DatabaseManager;
use RPMS\App\Notification\SMSHelper;
use RPMS\App\Security\Header\RequestHeader;

Dotenv::createImmutable(__DIR__)->load();
RequestHeader::setRequestHeader(array('localhost:8000','localhost:8000'));

// $encryptedData = Encryption::salt($_ENV['MPESA_SALTED_IV'])->encrypt('254798749323');
// $decryptedData = Encryption::salt($_ENV['MPESA_SALTED_IV'])->decrypt($encryptedData);

$username = 'Kusa';
$apiKey = '22df536c25d34a75f40099dd7f1b4cade1a7baafe6bad5a9cc295cf8d568d3a2';

$recipients = ['+254798749323', '+254745858891', '+254796674665'];
$message = 'Hello, this is a bulk SMS message!';

echo json_encode(SMSHelper::sendSMS($username, $apiKey, $recipients, $message));