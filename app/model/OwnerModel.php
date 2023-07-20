<?php

namespace App\Model;

class OwnerModel {
    private static string $tableName = 'OWNER';

    public static function create(array $ownerValues) : bool | int
    {
        return DatabaseManager::executeInsert(self::$tableName, $ownerValues);
    }

    public static function get(string $id) : array
    {
        $tableName = self::$tableName;

        $ownerData = DatabaseManager::executeSelect("SELECT FIRST_NAME, LAST_NAME, EMAIL, ID_NUMBER, PASSWORD FROM $tableName WHERE ID = ?", [$id]);
        
        $ownerData = $ownerData[0];

        return [
            'EMAIL'      => $ownerData['EMAIL'],
            'PASSWORD'   => $ownerData['PASSWORD'],
            'ID_NUMBER'  => $ownerData['ID_NUMBER'],
            'LAST_NAME'  => $ownerData['LAST_NAME'],
            'FIRST_NAME' => $ownerData['FIRST_NAME'],
        ];
    }

    public static function login(string $email) : array
    {
        $tableName = self::$tableName;

        $ownerData = DatabaseManager::executeSelect("SELECT ID, FIRST_NAME, LAST_NAME, EMAIL, ID_NUMBER, PASSWORD FROM $tableName WHERE EMAIL = ?", [$email]);
        
        if(!isset($ownerData[0])) return [];

        $ownerData = $ownerData[0];

        return [
            'ID'         => $ownerData['ID'],
            'EMAIL'      => $ownerData['EMAIL'],
            'PASSWORD'   => $ownerData['PASSWORD'],
            'ID_NUMBER'  => $ownerData['ID_NUMBER'],
            'LAST_NAME'  => $ownerData['LAST_NAME'],
            'FIRST_NAME' => $ownerData['FIRST_NAME'],
        ];
    }
}