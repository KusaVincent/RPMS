<?php

namespace App\Model;

class OwnerModel {
    private static string $tableName = 'OWNER';

    public static function create(array $ownerValues) : int
    {
        $result = DatabaseManager::executeInsert(self::$tableName, $ownerValues);
        if(gettype($result) === 'boolean') throw new \Exception('Insert Unsuccessful');
        return $result;
    }

    public static function update(string $id, array $ownerValues) : int
    {
        $result = DatabaseManager::executeUpdate(self::$tableName, $ownerValues, ['ID' => $id]);
        if(gettype($result) === 'boolean') throw new \Exception('Update Unsuccessful');
        return $result;
    }

    public static function get(string $id) : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, 'ID', $id);

        $ownerData = DatabaseManager::executeSelect($sql, $param);
        
        if(!isset($ownerData[0])) throw new \Exception('Record not found');

        $ownerData = $ownerData[0];

        return [
            'EMAIL'         => $ownerData['EMAIL'],
            'ID_NUMBER'     => $ownerData['ID_NUMBER'],
            'LAST_NAME'     => $ownerData['LAST_NAME'],
            'FIRST_NAME'    => $ownerData['FIRST_NAME'],
            'PHONE_NUMBER'  => $ownerData['PHONE_NUMBER']
        ];
    }

    public static function getAll() : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, id:true);

        $ownerData = DatabaseManager::executeSelect($sql, $param);
        
        if(gettype($ownerData) === 'boolean') throw new \Exception('Record fetching error');
        
        return $ownerData;
    }

    public static function login(string $email) : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, 'EMAIL', $email, true, true);

        $ownerData = DatabaseManager::executeSelect($sql, $param);
        
        if(!isset($ownerData[0])) throw new \Exception('Wrong credentials passed');

        $ownerData = $ownerData[0];

        return [
            'ID'            => $ownerData['ID'],
            'EMAIL'         => $ownerData['EMAIL'],
            'PASSWORD'      => $ownerData['PASSWORD'],
            'ID_NUMBER'     => $ownerData['ID_NUMBER'],
            'LAST_NAME'     => $ownerData['LAST_NAME'],
            'FIRST_NAME'    => $ownerData['FIRST_NAME'],
            'PHONE_NUMBER'  => $ownerData['PHONE_NUMBER']
        ];
    }

    private static function query(string $tableName, ?string $whereColumn = null, ?string $columnValue = null, bool $id = false, bool $login = false) : array
    {
        $column = "FIRST_NAME, LAST_NAME, EMAIL, ID_NUMBER, PHONE_NUMBER";

        if($id) $column .= ', ID';
        if($login) $column .= ', PASSWORD';

        $sql = "
            SELECT 
                $column 
            FROM 
                $tableName
            WHERE
                OWNER_STATUS = ?
        ";

        if($whereColumn !== null) $sql .= " AND $whereColumn = ?";

        $sqlParam  = $whereColumn !== null ? ['active', $columnValue]: ['active'];

        return [$sql, $sqlParam];
    }
}