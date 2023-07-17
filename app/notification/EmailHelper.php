<?php

namespace RPMS\App\Notification;

use RPMS\App\Model\EmailConfigDBData;

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

        $emailHost = explode('.', $email[0])[0];

        // run db... to be done once Database manager works
        return EmailConfigDBData::getMailConfig($emailHost);
        // return [
        //     'port' => 465,
        //     'secure' => 'ssl',
        //     'host' => 'mail.rentalskonekt.com'
        // ];
    }
}