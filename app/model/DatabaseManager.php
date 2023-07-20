<?php
declare(strict_types=1);

namespace App\Model;

use eftec\PdoOneORM;
use App\Log\LogHandler;
use App\Security\ImmutableVariable;

class DatabaseManager extends PdoOneORM {
    private string $dbHost;
    private string $dbName;
    private string $dbDriver;
    private string $dbUsername;
    private string $dbPassword;
    private static ?self $databaseInstance = null;

    public function __construct() {
        $this->dbHost     = ImmutableVariable::getValueAndDecryptBeforeUse('dbHost');
        $this->dbName     = ImmutableVariable::getValueAndDecryptBeforeUse('dbName');
        $this->dbDriver   = ImmutableVariable::getValueAndDecryptBeforeUse('dbDriver');
        $this->dbUsername = ImmutableVariable::getValueAndDecryptBeforeUse('dbUsername');
        $this->dbPassword = ImmutableVariable::getValueAndDecryptBeforeUse('dbPassword');

        parent::__construct($this->dbDriver, $this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
        parent::connect();
    }

    //for debugging
    public function checkConnection() {
        try {
            $databaseManager = self::getInstance();
            $connected = $databaseManager->connect();
            
            if(!$connected){
                LogHandler::handle('Database', "Failed to connect to the database" .  $connected);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to connect to the database: " . $e->getMessage());
            return "Failed to connect to the database: " . $e->getMessage();
        }
    }

    private static function getInstance(): self
    {
        if (self::$databaseInstance === null) {
            self::$databaseInstance = new self();
        }

        return self::$databaseInstance;
    }

    public static function executeSelect(string $sql, array $params = []) : array | bool
    {
        try {
            $databaseManager = self::getInstance();
            return $databaseManager->runRawQuery($sql, $params);
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to execute the query: " . $e->getMessage());
            return false;
        }
    }

    public static function executeInsert(string $table, array $data) : bool | int
    {
        try {
            $databaseManager = self::getInstance();
            $result = $databaseManager->insert($table, $data);
            return $result;
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to insert data into the table: " . $e->getMessage());
            return false;
        }
    }

    public static function executeUpdate(string $table, array $data, array $where) : bool| int
    {
        try {
            $databaseManager = self::getInstance();
            $result = $databaseManager->update($table, $data, $where);
            return $result;
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to update data in the table: " . $e->getMessage());
            return false;
        }
    }

    public static function executeDelete(string $table, ?array $where) : bool | int
    {
        try {
            $databaseManager = self::getInstance();
            $result = $databaseManager->delete($table, $where);
            return $result;
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to delete data from the table: " . $e->getMessage());
            return false;
        }
    }
}