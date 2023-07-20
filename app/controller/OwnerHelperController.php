<?php

namespace App\Controller;

use App\Model\OwnerModel;
use App\Security\Encryption;
use App\Security\PasswordManager;
use App\Util\IdGenerator;

class OwnerHelperController {
    public static function register(array $ownerData) 
    {
        $ownerValues = array();
        $id = IdGenerator::create('OWNER');

        $email    = $ownerData['EMAIL'];
        $password = new PasswordManager($id);

        unset($ownerData['EMAIL']);

        foreach ($ownerData as $key => $value)
        {
            if($key == 'PASSWORD') $ownerValues['PASSWORD'] = $password->hashPassword($value);
            if($key !== 'PASSWORD') $ownerValues[$key] = Encryption::salt($id)->encrypt($value);
        }

        $ownerValues['ID']    = $id;
        $ownerValues['EMAIL'] = $email;

        return OwnerModel::create($ownerValues);
    }

    public static function getData(string $id) : array
    {
        $getOwner = OwnerModel::get($id);

        return [
            'EMAIL'      => $getOwner['EMAIL'],
            'ID_NUMBER'  => Encryption::salt($id)->decrypt($getOwner['ID_NUMBER']),
            'LAST_NAME'  => Encryption::salt($id)->decrypt($getOwner['LAST_NAME']),
            'FIRST_NAME' => Encryption::salt($id)->decrypt($getOwner['FIRST_NAME'])
        ];
    }

    public static function login (array $ownerData) 
    {
        
        $getOwner = OwnerModel::login($ownerData['EMAIL']);

        if(empty($getOwner)) return "Wrong credentials passed";

        $pass = new PasswordManager($getOwner['ID']);

        if(!$pass->verifyPassword($ownerData['PASSWORD'], $getOwner['PASSWORD']))
        {
            return "Wrong credentials passed";
        }

        return [
            'EMAIL'      => $getOwner['EMAIL'],
            'ID_NUMBER'  => Encryption::salt($getOwner['ID'])->decrypt($getOwner['ID_NUMBER']),
            'LAST_NAME'  => Encryption::salt($getOwner['ID'])->decrypt($getOwner['LAST_NAME']),
            'FIRST_NAME' => Encryption::salt($getOwner['ID'])->decrypt($getOwner['FIRST_NAME'])
        ];
    }
}