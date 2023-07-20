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

    public static function validateOwnerRegistration(array $values) : bool
    {        
        $email    = $values['EMAIL'];
        $domain   = substr($email, strpos($email, '@') + 1);
        $phone    = PhoneNumber::format($values['PHONE_NUMBER']);

        if(!$phone) throw new \Exception('Enter a valid phone number');
        if(!checkdnsrr($domain, 'MX')) throw new \Exception('Enter a valid email');
        if(ValidationModel::email($email, 'OWNER'))  throw new \Exception('Email is not unique');
        if(ValidationModel::phone($phone, 'OWNER'))  throw new \Exception('Phone number is not unique');
        if(ValidationModel::idNumber($values['ID_NUMBER'], 'OWNER'))  throw new \Exception('ID number is not unique');

        $rules = [
            'LAST_NAME'        => 'required',
            'FIRST_NAME'       => 'required',
            'PASSWORD'         => 'required|min:6',
            'EMAIL'            => 'required|email',
            'ID_NUMBER'        => 'required|numeric',
            'PHONE_NUMBER'     => 'required|numeric',
            'CONFIRM_PASSWORD' => 'required|same:PASSWORD',
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'User registration validation failed: ' . $e->getMessage());
            throw new \Exception('User registration validation failed', 400);
        }
    }

    public static function validateContactForm(array $values) : bool
    {
        $rules = [
            'name'    => 'required',
            'email'   => 'required|email',
            'message' => 'required'
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Contact form validation failed');
            throw new \Exception('Contact form validation failed', 400);
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

    public static function validateProductCreation(array $values) : bool
    {
        $rules = [
            'name'        => 'required',
            'price'       => 'required|numeric',
            'description' => 'required'
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'Product creation validation failed');
            throw new \Exception('Product creation validation failed', 400);
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
}