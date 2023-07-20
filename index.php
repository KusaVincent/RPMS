<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use App\Controller\OwnerController;
use Dotenv\Dotenv;
use App\Security\SessionManager;
use App\Security\Header\RequestHeader;
use App\Util\IdGenerator;

define('BASE_PATH', __DIR__);
define('LOG_PATH', BASE_PATH . '/logs/');

Dotenv::createImmutable(BASE_PATH)->load();
RequestHeader::setRequestHeader(true);
SessionManager::init(['name' => 'JSESSID', 'cookie_lifetime' => 8000]);

$array = [
    'FIRST_NAME'=> 'vincent',
    'LAST_NAME' => 'Kusa',
    'EMAIL'     => 'vinonyi21d@gmail.com',
    'ID_NUMBER' => '35379479',
    'PASSWORD'  => '1234'
];

echo json_encode(OwnerController::login($array));