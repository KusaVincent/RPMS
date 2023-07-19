<?php
declare(strict_types=1);

namespace RPMS\App\Security\Header;

class HeaderSetting
{
    public static function setHeader(string $headerName, string $headerValue): void
    {
        header($headerName . ': ' . $headerValue);
    }
}