<?php

namespace App\Model;

class ValidationModel {
    public static function email(string $email, string $tableName, ?string $id = null) : bool
    {
        [$sql, $sqlParam] = self::query($tableName, 'EMAIL', $email, $id);

        $ownerData = DatabaseManager::executeSelect($sql, $sqlParam);
        
        return !isset($ownerData[0]) ? false : true;
    }

    public static function phone(string $phone, string $tableName, ?string $id = null) : bool
    {
        [$sql, $sqlParam] = self::query($tableName, 'PHONE_NUMBER', $phone, $id);

        $ownerData = DatabaseManager::executeSelect($sql, $sqlParam);
        
        return !isset($ownerData[0]) ? false : true;
    }

    public static function idNumber(string $idNumber, string $tableName, ?string $id = null) : bool
    {
        [$sql, $sqlParam] = self::query($tableName, 'ID_NUMBER', $idNumber, $id);

        $ownerData = DatabaseManager::executeSelect($sql, $sqlParam);
        
        return !isset($ownerData[0]) ? false : true;
    }

    private static function query(string $tableName, string $column, string $columnValue, ?string $id = null) : array
    {
        $sql = "
            SELECT 
                $column 
            FROM 
                $tableName 
            WHERE 
                $column = ?
        ";

        if($id !== null) $sql .= " AND ID != ?";

        $sqlParam  = $id !== null ? [$columnValue, $id]: [$columnValue];

        return [$sql, $sqlParam];
    }
}