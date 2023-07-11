<?php

namespace RPMS\App\Security\Header;

class HeaderSetting
{
    public static function setHeader(string $headerName, string $headerValue): void
    {
        header($headerName . ': ' . $headerValue);
    }
}