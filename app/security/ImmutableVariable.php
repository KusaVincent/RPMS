<?php
declare(strict_types=1);

namespace App\Security;

use App\Log\LogHandler;
use App\Model\ImmutableModel;

class ImmutableVariable {
    private static string $logName;
    private static array $dotEnvValue;
    private static array $databaseValue;

    public static function init() : void
    {
        self::$logName     = 'static-variables';

        self::$dotEnvValue = [
            'dbName'           => $_ENV['DB_NAME'],
            'dbHost'           => $_ENV['DB_HOST'],
            'idString'         => $_ENV['ID_STRING'],
            'dbDriver'         => $_ENV['DB_DRIVER'],
            'IVLength'         => $_ENV['IV_LENGTH'],
            'tagLength'        => $_ENV['TAG_LENGTH'],
            'staticSalt'       => $_ENV['STATIC_SALT'],
            'dbPassword'       => $_ENV['DB_PASSWORD'],
            'dbUsername'       => $_ENV['DB_USERNAME'],
            'cookieLife'       => $_ENV['COOKIE_LIFE'],
            'encryptMethod'    => $_ENV['ENCRYPT_METHOD'],
            'mpesaSaltedIV'    => $_ENV['MPESA_SALTED_IV'],
            'passwordPepper'   => $_ENV['PASSWORD_PEPPER']
        ];

        self::$databaseValue = [
            'baseURI'               => 'BaseURI',
            'baseURL'               => 'BaseURL',
            'appName'               => 'AppName',
            'tokenURL'              => 'TokenURL',
            'queryURL'              => 'QueryURL',
            'countryCode'           => 'CountryCode',
            'callbackURL'           => 'CallbackURL',
            'methodArray'           => 'MethodArray',
            'endpointURL'           => 'EndpointURL',
            'mobitechApiKey'        => 'MobitechApiKey',
            'allowedOrigins'        => 'AllowedOrigins',
            'mobitechBaseUrl'       => 'MobitechBaseUrl',
            'safaricomBaseURL'      => 'SafaricomBaseURL',
            'mobitechSenderName'    => 'MobitechSenderName',
            'africasTalkingApiKey'  => 'AfricasTalkingApiKey',
            'africasTalkingUserName'=> 'AfricasTalkingUserName'
        ];
    }

    public static function getValueAndDecryptBeforeUse(string $variable) : string
    {
        $output = self::getValue($variable);

        try {
            $valueOutput = Encryption::salt(self::getValue('staticSalt'))->decrypt($output);
        }catch (\Exception $e) {
            LogHandler::handle(self::$logName, $variable . ': ' . $e->getMessage());
            $valueOutput = $output;
        }

        return $valueOutput;
    }

    public static function getValue(string $variable) : string
    {
        self::init();

        if (array_key_exists($variable, self::$dotEnvValue)) return self::getValueFromDotEnv($variable);

        if (array_key_exists($variable, self::$databaseValue)) return self::getValueFromDatabase($variable);

        LogHandler::handle(self::$logName, "key ($variable) entered not found");
        
        return $variable;
    }

    private static function getValueFromDotEnv(string $variable) : string
    {
        return self::$dotEnvValue[$variable];
    }

    private static function getValueFromDatabase(string $variable) : string
    {
        $columnValue    = self::$databaseValue[$variable];
        $returnedValue  = ImmutableModel::getValue($columnValue);

        return $returnedValue;
    }
}