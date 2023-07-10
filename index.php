<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Dotenv\Dotenv;
use RPMS\APP\Report\ExcelReport;

Dotenv::createImmutable(__DIR__)->load();

$format = 'xlsx';
$filename = 'data.' . $format;
$data = [
    ['Name', 'Email'],
    ['John Doe', 'john@example.com'],
    ['Jane Smith', 'jane@example.com'],
];

ExcelReport::generate($filename, $data, $format)->preview();