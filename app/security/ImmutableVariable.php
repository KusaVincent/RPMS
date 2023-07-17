<?php

namespace RPMS\App\Security;

use RPMS\App\Log\LogHandler;
use RPMS\App\Model\DatabaseManager;

class ImmutableVariable {
    private static array $dotEnvValue;
    private static array $databaseValue;

    public static function init()
    {
        self::$dotEnvValue = [
            'life'             => $_ENV['LIFE'],
            'dbName'           => $_ENV['DB_NAME'],
            'dbHost'           => $_ENV['DB_HOST'],
            'appName'          => $_ENV['APP_NAME'],
            'baseURI'          => $_ENV['BASE_URI'],
            'baseURL'          => $_ENV['BASE_URL'],
            'logPath'          => $_ENV['LOG_PATH'],
            'dbDriver'         => $_ENV['DB_DRIVER'],
            'IVLength'         => $_ENV['IV_LENGTH'],
            'queryURL'         => $_ENV['QUERY_URL'],
            'tokenURL'         => $_ENV['TOKEN_URL'],
            'tagLength'        => $_ENV['TAG_LENGTH'],
            'staticSalt'       => $_ENV['STATIC_SALT'],
            'dbPassword'       => $_ENV['DB_PASSWORD'],
            'dbUsername'       => $_ENV['DB_USERNAME'],
            'countryCode'      => $_ENV['COUNTRY_CODE'],
            'callbackURL'      => $_ENV['CALLBACK_URL'],
            'endpointURL'      => $_ENV['ENDPOINT_URL'],
            'encryptMethod'    => $_ENV['ENCRYPT_METHOD'],
            'mpesaSaltedIV'    => $_ENV['MPESA_SALTED_IV'],
            'allowedOrigins'   => $_ENV['ALLOWED_ORIGINS'],
            'safaricomBaseURL' => $_ENV['SAFARICOM_BASE_URL']
        ];

        self::$databaseValue = [
            '' => ''
        ];
    }

    public static function getValue(string $variable) : string
    {
        self::init();

        if (array_key_exists($variable, self::$dotEnvValue)) return self::getValueFromDotEnv($variable);

        if (array_key_exists($variable, self::$databaseValue)) return self::getValueFromDatabase($variable);

        LogHandler::handle('staic-variables', "key ($variable) entered not found");
        return $variable;
    }

    private static function getValueFromDotEnv(string $variable) : string
    {
        return self::$dotEnvValue[$variable];
    }

    private static function getValueFromDatabase(string $variable) : string
    {
        $columnValue    = self::$databaseValue[$variable];
        $returnedValue  =  DatabaseManager::executeSelect('', [$columnValue]); //query to be determined once db is set

        return $returnedValue[0][''];
    }
}