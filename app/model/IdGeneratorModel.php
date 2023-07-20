<?php

namespace App\Model;

use App\Model\DatabaseManager;

class IdGeneratorModel {
    public static function lastId(string $tableName) : string
    {
        $IdValue = DatabaseManager::executeSelect("SELECT MAX(ID) as MAX_ID FROM $tableName");
        
        $IdValue = $IdValue[0];
        
        return $IdValue['MAX_ID'] ?? '';
    }
}