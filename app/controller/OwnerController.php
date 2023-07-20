<?php

namespace App\Controller;

use App\Model\OwnerModel;
use App\Util\{IdGenerator, PhoneNumber};
use App\Security\{CustomValidation, Encryption, PasswordManager};

class OwnerController {
    public static function signUp(array $ownerData) 
    {
        $id = IdGenerator::create('OWNER');

        $password  = new PasswordManager($id);
        CustomValidation::validateOwnerRegistration($ownerData);

        $ownerValues = array();

        $ownerValues['ID']           = $id;

        unset($ownerData['CONFIRM_PASSWORD']);

        foreach ($ownerData as $key => $value)
        {
            if($key == 'PASSWORD') $ownerValues['PASSWORD'] = $password->hashPassword($value);
            if($key !== 'PASSWORD') $ownerValues[$key] = $value;
        }

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

    public static function modify(string $id, array $updateData)
    {
        // CustomValidation::validateOwnerRegistration($updateData);
        return OwnerModel::update($id, $updateData);
    }

    public static function login(array $ownerData) 
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