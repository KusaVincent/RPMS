<?php

namespace RPMS\App\Model;

use RPMS\App\Model\DatabaseManager;

class ImmutableModel {
    public static function getValue(string $columnValue) : string
    {
        $columnValue = DatabaseManager::executeSelect("SELECT VALUE FROM APPLICATION_SETTINGS WHERE NAME = ?", [$columnValue]);
        
        $columnValue = $columnValue[0];
        
        return $columnValue['VALUE'];
    }
}