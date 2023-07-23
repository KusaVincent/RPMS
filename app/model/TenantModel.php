<?php

namespace App\Model;

class TenantModel {
    private static string $tableName = 'TALENT';

    public static function create(array $talentValues) : int
    {
        $result = DatabaseManager::executeInsert(self::$tableName, $talentValues);
        if(gettype($result) === 'boolean') throw new \Exception('Insert Unsuccessful');
        return $result;
    }

    public static function update(string $id, array $talentValues) : int
    {
        $result = DatabaseManager::executeUpdate(self::$tableName, $talentValues, ['ID' => $id]);
        if(gettype($result) === 'boolean') throw new \Exception('Update Unsuccessful');
        return $result;
    }

    public static function get(string $id) : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, 'ID', $id);

        $talentData = DatabaseManager::executeSelect($sql, $param);
        
        if(!isset($talentData[0])) throw new \Exception('Record not found');

        $talentData = $talentData[0];

        return [
            'EMAIL'              => $talentData['EMAIL'],
            'ID_IMAGE'           => $talentData['ID_IMAGE'],
            'PASSWORD'           => $talentData['PASSWORD'],
            'ID_NUMBER'          => $talentData['ID_NUMBER'],
            'LAST_NAME'          => $talentData['LAST_NAME'],
            'FIRST_NAME'         => $talentData['FIRST_NAME'],
            'PHONE_NUMBER'       => $talentData['PHONE_NUMBER'],
            'LEASE_PERIOD'       => $talentData['LEASE_PERIOD'],
            'LEASE_END_DATE'     => $talentData['LEASE_END_DATE'],
            'RENTAL_UNIT_ID'     => $talentData['RENTAL_UNIT_ID'],
            'NEGOTIATED_RENT'    => $talentData['NEGOTIATED_RENT'],
            'LEASE_START_DATE'   => $talentData['LEASE_START_DATE'],
            'NEGOTIATED_DEPOSIT' => $talentData['NEGOTIATED_DEPOSIT']
        ];
    }

    public static function getAll() : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, id:true);

        $talentData = DatabaseManager::executeSelect($sql, $param);
        
        if(gettype($talentData) === 'boolean') throw new \Exception('Record fetching error');
        
        return $talentData;
    }

    public static function login(string $email) : array
    {
        $tableName = self::$tableName;

        [$sql, $param] = self::query($tableName, 'EMAIL', $email, true, true);

        $talentData = DatabaseManager::executeSelect($sql, $param);
        
        if(!isset($talentData[0])) throw new \Exception('Wrong credentials passed');

        $talentData = $talentData[0];

        return [
            'ID'                 => $talentData['ID'],
            'EMAIL'              => $talentData['EMAIL'],
            'ID_IMAGE'           => $talentData['ID_IMAGE'],
            'PASSWORD'           => $talentData['PASSWORD'],
            'ID_NUMBER'          => $talentData['ID_NUMBER'],
            'LAST_NAME'          => $talentData['LAST_NAME'],
            'FIRST_NAME'         => $talentData['FIRST_NAME'],
            'PHONE_NUMBER'       => $talentData['PHONE_NUMBER'],
            'LEASE_PERIOD'       => $talentData['LEASE_PERIOD'],
            'LEASE_END_DATE'     => $talentData['LEASE_END_DATE'],
            'RENTAL_UNIT_ID'     => $talentData['RENTAL_UNIT_ID'],
            'NEGOTIATED_RENT'    => $talentData['NEGOTIATED_RENT'],
            'LEASE_START_DATE'   => $talentData['LEASE_START_DATE'],
            'NEGOTIATED_DEPOSIT' => $talentData['NEGOTIATED_DEPOSIT']
        ];
    }

    private static function query(string $tableName, ?string $whereColumn = null, ?string $columnValue = null, bool $id = false, bool $login = false) : array
    {
        $column = "FIRST_NAME, LAST_NAME, EMAIL, ID_NUMBER, PHONE_NUMBER, ID_IMAGE, LEASE_PERIOD, LEASE_END_DATE, RENTAL_UNIT_ID, NEGOTIATED_RENT, LEASE_START_DATE, NEGOTIATED_DEPOSIT";

        if($id) $column .= ', ID';
        if($login) $column .= ', PASSWORD';

        $sql = "
            SELECT 
                $column 
            FROM 
                $tableName
            WHERE
                TALENT_STATUS = ?
        ";

        if($whereColumn !== null) $sql .= " AND $whereColumn = ?";

        $sqlParam  = $whereColumn !== null ? ['active', $columnValue]: ['active'];

        return [$sql, $sqlParam];
    }
}