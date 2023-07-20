<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;
use App\Security\SessionManager;
use App\Security\ImmutableVariable;
use App\Security\Header\RequestHeader;

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