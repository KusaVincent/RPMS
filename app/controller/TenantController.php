<?php

namespace App\Controller;

use App\Model\OwnerModel;
use App\Util\IdGenerator;
use App\Validation\TenantValidation;
use App\Security\{Encryption, ImmutableVariable, PasswordManager};

class TenantController {
    private static string $staticSalt;

    private static function init()
    {
        self::$staticSalt = ImmutableVariable::getValue('staticSalt');
    }

    public static function signUp(array $tenantData) : int
    {
        $id = IdGenerator::create('TENANT');

        $password  = new PasswordManager($id);

        [$email, $phoneNumber, $idNumber] = TenantValidation::validateTenantRegistration($tenantData);

        $tenantValues       = array();

        $tenantValues['ID']           = $id;
        $tenantValues['EMAIL']        = $email;
        $tenantValues['ID_NUMBER']    = $idNumber;
        $tenantValues['PHONE_NUMBER'] = $phoneNumber;

        unset($tenantData['EMAIL']);
        unset($tenantData['ID_NUMBER']);
        unset($tenantData['PHONE_NUMBER']);
        unset($tenantData['CONFIRM_PASSWORD']);

        foreach ($tenantData as $key => $value)
        {
            if($key == 'PASSWORD')  $tenantValues['PASSWORD'] = $password->hashPassword($value);
            if($key !== 'PASSWORD') $tenantValues[$key]       = Encryption::salt($id)->encrypt($value);
        }

        return OwnerModel::create($tenantValues);
    }

    public static function getRecord(string $id) : array
    {
        self::init();
        $recordData =  array();
        $getTenant  = OwnerModel::get($id);

        $recordData['EMAIL']         = Encryption::salt(self::$staticSalt)->decrypt($getTenant['EMAIL']);
        $recordData['ID_NUMBER']     = Encryption::salt(self::$staticSalt)->decrypt($getTenant['ID_NUMBER']);
        $recordData['PHONE_NUMBER']  = Encryption::salt(self::$staticSalt)->decrypt($getTenant['PHONE_NUMBER']);

        unset($tenantData['EMAIL']);
        unset($tenantData['ID_NUMBER']);
        unset($tenantData['PHONE_NUMBER']);

        foreach($recordData as $key => $value) 
        {
            $recordData[$key] = Encryption::salt($id)->decrypt($value);
        }

        return $recordData;
    }

    public static function getAllRecords() : array
    {
        self::init();
        $tenants = OwnerModel::getAll();
        $tenantDataInside = $tenantData  = array();

        if (!empty($tenants))
        {
            foreach($tenants as $tenant)
            {
                $id = $tenantData['ID'] = $tenant['ID'];

                if(isset($id)) {
                    if($tenant['LAST_NAME'])  $tenantData['LAST_NAME']  = Encryption::salt($id)->decrypt($tenant['LAST_NAME']);
                    if($tenant['FIRST_NAME']) $tenantData['FIRST_NAME'] = Encryption::salt($id)->decrypt($tenant['FIRST_NAME']);
                }
                
                $tenantData['EMAIL']        = Encryption::salt(self::$staticSalt)->decrypt($tenant['EMAIL']);
                $tenantData['ID_NUMBER']    = Encryption::salt(self::$staticSalt)->decrypt($tenant['ID_NUMBER']);
                $tenantData['PHONE_NUMBER'] = Encryption::salt(self::$staticSalt)->decrypt($tenant['PHONE_NUMBER']);

                $tenantDataInside[$id] = $tenantData;
            }
        }

        return $tenantDataInside;
    }

    public static function modify(string $id, array $updateData) : int
    {
        [$email, $phoneNumber, $idNumber] = TenantValidation::validateTenantUpdate($updateData, $id);
        
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

    public static function login(array $tenantData) 
    {
        self::init();
        
        $getTenant = OwnerModel::login(
            Encryption::salt(self::$staticSalt)->encrypt($tenantData['EMAIL'])
        );

        $pass = new PasswordManager($getTenant['ID']);

        if(!$pass->verifyPassword($tenantData['PASSWORD'], $getTenant['PASSWORD'])) throw new \Exception('Wrong credentials passed');

        return [
            'ID'            => $getTenant['ID'],
            'EMAIL'         => $tenantData['EMAIL'],
            'LAST_NAME'     => Encryption::salt($getTenant['ID'])->decrypt($getTenant['LAST_NAME']),
            'FIRST_NAME'    => Encryption::salt($getTenant['ID'])->decrypt($getTenant['FIRST_NAME']),
            'ID_NUMBER'     => Encryption::salt(self::$staticSalt)->decrypt($getTenant['ID_NUMBER']),
            'PHONE_NUMBER'  => Encryption::salt(self::$staticSalt)->decrypt($getTenant['PHONE_NUMBER'])
        ];
    }
}