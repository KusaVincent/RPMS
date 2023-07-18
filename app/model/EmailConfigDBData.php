<?php

namespace RPMS\App\Model;

use RPMS\App\Model\DatabaseManager;

class EmailConfigDBData {
    public static function getMailConfig(string $emailHost) : array
    {
        $emailData = DatabaseManager::executeSelect("SELECT HOST, PORT, SECURE FROM EMAIL_CONFIG WHERE NAME = ?", [$emailHost]);
        
        $emailData = $emailData[0];

        return [
            'host'   => $emailData['HOST'],
            'port'   => $emailData['PORT'],
            'secure' => $emailData['SECURE']
        ];
    }
}