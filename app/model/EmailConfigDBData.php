<?php

namespace RPMS\App\Model;

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