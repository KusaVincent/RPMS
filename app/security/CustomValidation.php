<?php

namespace RPMS\App\Security;

use RPMS\App\Log\LogHandler;
use Rakit\Validation\Validator;

class CustomValidation
{
    private static $logName;
    private static $validator;

    private static function initialize()
    {
        self::$logName   = 'validation';
        self::$validator = new Validator();
    }

    private static function validateAndGetErrors($values, $rules, $messages = [])
    {
        self::initialize();

        $validation = self::$validator->make($values, $rules, $messages);
        $validation->validate();

        if ($validation->fails()) {
            throw new \Exception('Validation failed', 400);
        }

        return true;
    }

    public static function validateUserRegistration($values)
    {
        $rules = [
            'name'      => 'required',
            'email'     => 'required|email',
            'password'  => 'required|min:6',
            'confirm_password' => 'required|same:password'
        ];

        try {
            self::validateAndGetErrors($values, $rules);
            return true;
        } catch (\Exception $e) {
            LogHandler::handle(self::$logName, 'User registration validation failed');
            throw new \Exception('User registration validation failed', 400);
        }
    }

    public static function validateContactForm($values)
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

    public static function validateDateRange($values)
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

    public static function validateProductCreation($values)
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

    public static function validateLoginForm($values)
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