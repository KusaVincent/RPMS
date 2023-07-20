<?php

namespace App\Model;

class ValidationModel {
    public static function email(string $email, string $tableName) : bool
    {
        $ownerData = DatabaseManager::executeSelect("SELECT EMAIL FROM $tableName WHERE EMAIL = ?", [$email]);
        
        return !isset($ownerData[0]) ? false : true;
    }

    public static function phone(string $phone, string $tableName) : bool
    {
        $ownerData = DatabaseManager::executeSelect("SELECT PHONE_NUMBER FROM $tableName WHERE PHONE_NUMBER = ?", [$phone]);
        
        return !isset($ownerData[0]) ? false : true;
    }

    public static function idNumber(string $idNumber, string $tableName) : bool
    {
        $ownerData = DatabaseManager::executeSelect("SELECT ID_NUMBER FROM $tableName WHERE ID_NUMBER = ?", [$idNumber]);
        
        return !isset($ownerData[0]) ? false : true;
    }
}