<?php

namespace App\Validation;

use App\Log\LogHandler;
use App\Security\CustomValidation;

class OwnerValidation extends CustomValidation {
    public static function validateOwnerRegistration(array $values) : array
    {
        [$email, $phone, $idNumber] = parent::repetitiveValues('OWNER', $values['EMAIL'], $values['PHONE_NUMBER'], $values['ID_NUMBER']);

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

    public static function validateOwnerUpdate(array $values, string $id) : array
    {
        [$email, $phone, $idNumber] = self::repetitiveValues('OWNER', $values['EMAIL'], $values['PHONE_NUMBER'], $values['ID_NUMBER'], $id);

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