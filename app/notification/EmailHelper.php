<?php
declare(strict_types=1);

namespace RPMS\App\Notification;

use RPMS\App\Model\EmailConfigModel;

class EmailHelper extends Email {
    private string $senderEmail;
    private string $senderPassword;

    public function __construct(string $senderEmail, string $senderPassword)
    {
        $this->senderEmail    = $senderEmail;
        $this->senderPassword = $senderPassword;

        parent::__construct($this->senderEmail, $this->senderPassword, $this->emailConfig());
    }

    private function emailConfig() : array
    {
        $email = explode('@', $this->senderEmail);

        $emailHost = explode('.', $email[1])[0];

        return EmailConfigModel::getConfig($emailHost);
    }
}