<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;
use RPMS\App\Security\CustomValidation;
use RPMS\App\Security\Header\RequestHeader;

Dotenv::createImmutable(__DIR__)->load();
RequestHeader::setRequestHeader(array('localhost:8000','localhost:8000'));