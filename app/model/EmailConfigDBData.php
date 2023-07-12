<?php

namespace RPMS\App\Model;

use RPMS\App\Model\DatabaseManager;

class EmailConfigDBData {
    public static function getMailConfig(string $emailHost) : array
    {
        $emailData = DatabaseManager::executeSelect('SELECT * FROM EMAIL_CONFIG WHERE name = ?', [$emailHost]);
        return [
            'host'   => $emailData['host'],
            'port'   => $emailData['port'],
            'secure' => $emailData['secure']
        ];
    }
}