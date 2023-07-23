<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use App\Controller\OwnerController;
use App\Model\OwnerModel;
use Dotenv\Dotenv;
use App\Security\Header\RequestHeader;
use App\Security\{Encryption, SessionManager, ImmutableVariable};

define('BASE_PATH', __DIR__);
define('LOG_PATH', BASE_PATH . '/logs/');

Dotenv::createImmutable(BASE_PATH)->load();
RequestHeader::setRequestHeader(true);
SessionManager::init(
    [
        'name' => 'JSESSID', 
        'cookie_lifetime' => ImmutableVariable::getValueAndDecryptBeforeUse('cookieLife')
    ]
);

$array = [
    'EMAIL' => 'vinonyi21@gmail.com',
    'PASSWORD' => '123567'
];

$array = [
    'LAST_NAME'        => 'Okello',
    'FIRST_NAME'       => 'Kennedy',
    'EMAIL'            => 'vinonyi21@gmail.com',
    'ID_NUMBER'        => '35319473',
    'PHONE_NUMBER'     => '0798719326'
];

try{
    echo json_encode(OwnerController::modify('OWN230722AAAA', $array));
} catch (Exception $e) {
    echo json_encode($e->getMessage());
}