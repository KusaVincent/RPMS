<?php

namespace App\Util;

use App\Model\IdGeneratorModel;
use App\Security\ImmutableVariable;

class IdGenerator {
    private static array $tablePrefixes = [
        'ADMIN'        => 'ADM',
        'OWNER'        => 'OWN',
        'TENANT'       => 'TNT',
        'PROPERTY'     => 'PTY',
        'PROPERTYUNIT' => 'PTU'
    ];

    public static function create(string $tableName) : string
    {
        $lastId = self::getLastId($tableName);
        
        $dateToday   = date('Ymd');
        $idCut       = substr($lastId, 0, -7);
        $tablePrefix = self::$tablePrefixes[$tableName];
        $combination = $tablePrefix . $dateToday;

        if($idCut !== $combination) return $combination . ImmutableVariable::getValue('idString');

        return self::incrementString($lastId);
    }

    private static function getLastId(string $tableName) : string
    {
        return IdGeneratorModel::lastId($tableName);
    }

    private static function incrementString($str) : string
    {
        return StringIncrementer::NewStr($str);
    }    
}