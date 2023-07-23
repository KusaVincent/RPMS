<?php

namespace App\Controller;

use App\Model\OwnerModel;
use App\Util\IdGenerator;
use App\Validation\OwnerValidation;
use App\Security\{Encryption, ImmutableVariable, PasswordManager};

class OwnerController {
    private static string $staticSalt;

    private static function init()
    {
        self::$staticSalt = ImmutableVariable::getValue('staticSalt');
    }

    public static function signUp(array $ownerData) : int
    {
        $id = IdGenerator::create('OWNER');

        $password  = new PasswordManager($id);

        [$email, $phoneNumber, $idNumber] = OwnerValidation::validateOwnerRegistration($ownerData);

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
        self::init();
        $getOwner   = OwnerModel::get($id);

        return [
            'LAST_NAME'     => Encryption::salt($id)->decrypt($getOwner['LAST_NAME']),
            'FIRST_NAME'    => Encryption::salt($id)->decrypt($getOwner['FIRST_NAME']),
            'EMAIL'         => Encryption::salt(self::$staticSalt)->decrypt($getOwner['EMAIL']),
            'ID_NUMBER'     => Encryption::salt(self::$staticSalt)->decrypt($getOwner['ID_NUMBER']),
            'PHONE_NUMBER'  => Encryption::salt(self::$staticSalt)->decrypt($getOwner['PHONE_NUMBER'])
        ];
    }

    public static function getAllRecords() : array
    {
        self::init();
        $owners = OwnerModel::getAll();
        $ownerDataInside = $ownerData  = array();
        
        if (!empty($owners))
        {
            foreach($owners as $owner)
            {
                $id = $ownerData['ID'] = $owner['ID'];

                if(isset($id)) {
                    if($owner['LAST_NAME'])  $ownerData['LAST_NAME']  = Encryption::salt($id)->decrypt($owner['LAST_NAME']);
                    if($owner['FIRST_NAME']) $ownerData['FIRST_NAME'] = Encryption::salt($id)->decrypt($owner['FIRST_NAME']);
                }
                
                $ownerData['EMAIL']        = Encryption::salt(self::$staticSalt)->decrypt($owner['EMAIL']);
                $ownerData['ID_NUMBER']    = Encryption::salt(self::$staticSalt)->decrypt($owner['ID_NUMBER']);
                $ownerData['PHONE_NUMBER'] = Encryption::salt(self::$staticSalt)->decrypt($owner['PHONE_NUMBER']);

                $ownerDataInside[$id] = $ownerData;
            }
        }

        return $ownerDataInside;
    }

    public static function modify(string $id, array $updateData)
    {
        [$email, $phoneNumber, $idNumber] = OwnerValidation::validateOwnerUpdate($updateData, $id);
        
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

        return OwnerModel::update($id, $updateValues);
    }

    public static function login(array $ownerData) 
    {
        self::init();

        $getOwner   = OwnerModel::login(
            Encryption::salt(self::$staticSalt)->encrypt($ownerData['EMAIL'])
        );

        $pass = new PasswordManager($getOwner['ID']);

        if(!$pass->verifyPassword($ownerData['PASSWORD'], $getOwner['PASSWORD'])) throw new \Exception('Wrong credentials passed');

        return [
            'ID'            => $getOwner['ID'],
            'EMAIL'         => $ownerData['EMAIL'],
            'LAST_NAME'     => Encryption::salt($getOwner['ID'])->decrypt($getOwner['LAST_NAME']),
            'FIRST_NAME'    => Encryption::salt($getOwner['ID'])->decrypt($getOwner['FIRST_NAME']),
            'ID_NUMBER'     => Encryption::salt(self::$staticSalt)->decrypt($getOwner['ID_NUMBER']),
            'PHONE_NUMBER'  => Encryption::salt(self::$staticSalt)->decrypt($getOwner['PHONE_NUMBER'])
        ];
    }
}