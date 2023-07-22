<?php

namespace App\Controller;

use App\Model\OwnerModel;
use App\Util\IdGenerator;
use App\Security\{CustomValidation, Encryption, ImmutableVariable, PasswordManager};

class OwnerController {
    public static function signUp(array $ownerData) : int
    {
        $id = IdGenerator::create('OWNER');

        $password  = new PasswordManager($id);

        [$email, $phoneNumber, $idNumber] = CustomValidation::validateOwnerRegistration($ownerData);

        $ownerValues       = array();

        $ownerValues['ID']           = $id;
        $ownerValues['EMAIL']        = $email;
        $ownerValues['ID_NUMBER']    = $idNumber;
        $ownerValues['PHONE_NUMBER'] = $phoneNumber;

        unset($ownerData['EMAIL']);
        unset($ownerData['ID_NUMBER']);
        unset($ownerData['PHONE_NUMBER']);
        unset($ownerData['CONFIRM_PASSWORD']);

        foreach ($ownerData as $key => $value)
        {
            if($key == 'PASSWORD')  $ownerValues['PASSWORD'] = $password->hashPassword($value);
            if($key !== 'PASSWORD') $ownerValues[$key]       = Encryption::salt($id)->encrypt($value);
        }

        return OwnerModel::create($ownerValues);
    }

    public static function getRecord(string $id) : array
    {
        $getOwner = OwnerModel::get($id);

        return [
            'LAST_NAME'     => Encryption::salt($id)->decrypt($getOwner['LAST_NAME']),
            'FIRST_NAME'    => Encryption::salt($id)->decrypt($getOwner['FIRST_NAME']),
            'EMAIL'         => Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($getOwner['EMAIL']),
            'ID_NUMBER'     => Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($getOwner['ID_NUMBER']),
            'PHONE_NUMBER'  => Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($getOwner['PHONE_NUMBER'])
        ];
    }

    public static function getAllRecords() : array
    {
        $owners = OwnerModel::getAll();
        $ownerDataInside = $ownerData  = array();

        if (!empty($owners))
        {
            foreach($owners as $owner)
            {
                $id = $ownerData['ID_'] = $owner['ID'];

                if(isset($id)) {
                    if($owner['LAST_NAME'])  $ownerData['LAST_NAME']  = Encryption::salt($id)->decrypt($owner['LAST_NAME']);
                    if($owner['FIRST_NAME']) $ownerData['FIRST_NAME'] = Encryption::salt($id)->decrypt($owner['FIRST_NAME']);
                }
                
                $ownerData['EMAIL']        = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($owner['EMAIL']);
                $ownerData['ID_NUMBER']    = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($owner['ID_NUMBER']);
                $ownerData['PHONE_NUMBER'] = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->decrypt($owner['PHONE_NUMBER']);

                $ownerDataInside[$id] = $ownerData;
            }
        }

        return $ownerDataInside;
    }

    public static function modify(string $id, array $updateData) : int
    {
        [$email, $phoneNumber, $idNumber] = CustomValidation::validateOwnerUpdate($updateData, $id);
        
        $updateValues['EMAIL']        = $email;
        $updateValues['ID_NUMBER']    = $idNumber;
        $updateValues['PHONE_NUMBER'] = $phoneNumber;

        unset($updateData['EMAIL']);
        unset($updateData['ID_NUMBER']);
        unset($updateData['PHONE_NUMBER']);
        
        foreach ($updateData as $key => $value)
        {
            $updateValues[$key] = Encryption::salt($id)->encrypt($value);
        }

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