<?php

namespace App\Validation;

use App\Log\LogHandler;
use App\Security\CustomValidation;

class TenantValidation extends CustomValidation{
    public static function validateTenantRegistration(array $values) : array
    {
        [$email, $phone, $idNumber] = parent::repetitiveValues('TENANT', $values['EMAIL'], $values['PHONE_NUMBER'], $values['ID_NUMBER']);

        $rules = [
            'LAST_NAME'        => 'required',
            'FIRST_NAME'       => 'required',
            'PASSWORD'         => 'required|min:6',
            'EMAIL'            => 'required|email',
            'PHONE_NUMBER'     => 'required|numeric',
            'ID_NUMBER'        => 'required|numeric|min:8',
            'CONFIRM_PASSWORD' => 'required|same:PASSWORD',
        ];

        try {
            parent::validateAndGetErrors($values, $rules);
            return [$email, $phone, $idNumber];
        } catch (\Exception $e) {
            LogHandler::handle(parent::$logName, 'Registration validation failed: ' . $e->getMessage());
            throw new \Exception('Registration validation failed', 400);
        }
    }

    public static function validateTenantUpdate(array $values, string $id) : array
    {
        [$email, $phone, $idNumber] = self::repetitiveValues('TENANT', $values['EMAIL'], $values['PHONE_NUMBER'], $values['ID_NUMBER'], $id);

        $rules = [
            'LAST_NAME'        => 'required',
            'FIRST_NAME'       => 'required',
            'EMAIL'            => 'required|email',
            'PHONE_NUMBER'     => 'required|numeric',
            'ID_NUMBER'        => 'required|numeric|min:8',
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return [$email, $phone, $idNumber];
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Registration validation failed: ' . $e->getMessage());
            throw new \Exception('Registration validation failed', 400);
        }
    }
}