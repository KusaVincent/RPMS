<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;
use RPMS\App\Model\DatabaseManager;
use RPMS\App\Security\Header\RequestHeader;

Dotenv::createImmutable(__DIR__)->load();
RequestHeader::setRequestHeader(array('localhost:8000','localhost:8000'));

// $encryptedData = Encryption::salt($_ENV['MPESA_SALTED_IV'])->encrypt('254798749323');
// $decryptedData = Encryption::salt($_ENV['MPESA_SALTED_IV'])->decrypt($encryptedData);

// $data = DatabaseManager::checkConnection("SELECT * FROM mpesa");
echo json_encode(DatabaseManager::checkConnection());