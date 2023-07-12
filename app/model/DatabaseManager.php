<?php

namespace RPMS\App\Model;

use eftec\PdoOneORM;
use RPMS\App\Log\LogHandler;

class DatabaseManager extends PdoOneORM {
    public function __construct() {
        parent::__construct($_ENV['DB_DRIVER'], $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
        parent::connect();
    }

    //for debugging
    public function checkConnection() {
        try {
            $databaseManager = new self();
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

    public static function executeSelect(string $sql, array $params = []) : array | bool
    {
        try {
            $databaseManager = new self();
            return $databaseManager->runRawQuery($sql, $params);
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to execute the query: " . $e->getMessage());
            return false;
        }
    }

    public static function executeInsert(string $table, array $data) : bool | int
    {
        try {
            $databaseManager = new self();
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
            $databaseManager = new self();
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
            $databaseManager = new self();
            $result = $databaseManager->delete($table, $where);
            return $result;
        } catch (\Exception $e) {
            LogHandler::handle('Database', "Failed to delete data from the table: " . $e->getMessage());
            return false;
        }
    }
}