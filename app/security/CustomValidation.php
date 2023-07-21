<?php
declare(strict_types=1);

namespace App\Security;

use App\Log\LogHandler;
use App\Model\ValidationModel;
use App\Util\PhoneNumber;
use Rakit\Validation\Validator;

class CustomValidation
{
    private static string $logName;
    private static object $validator;

    private static function initialize()
    {
        self::$logName   = 'validation';
        self::$validator = new Validator();
    }

    private static function validateAndGetErrors(array $values, array $rules, array $messages = []) : bool
    {
        self::initialize();

        $validation = self::$validator->make($values, $rules, $messages);
        $validation->validate();

        if ($validation->fails()) {
            throw new \Exception('Validation failed', 400);
        }

        return true;
    }

    public static function validateOwnerRegistration(array $values) : array
    {
        [$email, $phone, $idNumber] = self::repetitiveValues('OWNER', $values['EMAIL'], $values['PHONE_NUMBER'], $values['ID_NUMBER']);

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
            self::validateAndGetErrors($values, $rules);
            return [$email, $phone, $idNumber];
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Registration validation failed: ' . $e->getMessage());
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
    
    public static function validateDateRange(array $values) : bool
    {
        $rules = [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:' . $values['start_date']
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Date range validation failed');
            throw new \Exception('Date range validation failed', 400);
        }
    }

    public static function validateLoginForm(array $values) : bool
    {
        $rules = [
            'email'    => 'required|email',
            'password' => 'required'
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Login form validation failed');
            throw new \Exception('Login form validation failed', 400);
        }
    }

    private static function repetitiveValues(string $tableName, ?string $email = null, ?string $phoneNumber = null, ?string $idNumber = null, ?string $id = null) : array
    {     
        $phone    = PhoneNumber::format($phoneNumber);

        if(!$phone) throw new \Exception('Enter a valid phone number');

        $email    = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->encrypt($email);
        $phone    = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->encrypt($phone);
        $idNumber = Encryption::salt(ImmutableVariable::getValue('staticSalt'))->encrypt($idNumber);
        
        if(ValidationModel::email($email, $tableName, $id)) throw new \Exception('Email is not unique');
        if(ValidationModel::phone($phone, $tableName, $id)) throw new \Exception('Phone number is not unique');
        if(ValidationModel::idNumber($idNumber, $tableName, $id)) throw new \Exception('ID number is not unique');

        return [$email, $phone, $idNumber];
    }
}