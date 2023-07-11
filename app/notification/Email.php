<?php
namespace RPMS\App\Notification;

use RPMS\App\Log\LogHandler;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private string $host;
    private string $sender;
    private object $mailer;
    private string $logName;

    public function __construct(string $senderEmail, string $senderPassword, string $host)
    {
        $this->host   = $host;
        $this->logName = 'email'; 
        $this->sender = $senderEmail;
        $this->mailer = new PHPMailer(true);

        $this->configureMailer($senderEmail, $senderPassword);
    }

    private function configureMailer(string $senderEmail, string $senderPassword): void
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->SMTPSecure = 'ssl';
            $this->mailer->Port = 465;
            $this->mailer->Username = $senderEmail;
            $this->mailer->Password = $senderPassword;
        } catch (\Exception $e) {
            LogHandler::handle($this->logName, 'Failed to configure mailer: ' . $e->getMessage());
            throw new \Exception('Failed to configure mailer: ' . $e->getMessage());
        }
    }

    public function send(array $emails, string $subject, string $header, string $message): array
    {
        $results = [];

        foreach ($emails as $email) {
            try {
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $subject;
                $this->mailer->SetFrom($this->sender, $header);
                $this->mailer->Body = $message;
                $this->mailer->AddAddress($email);

                if (!$this->mailer->Send()) {
                    $results[$email] = false;
                } else {
                    $results[$email] = true;
                }
            } catch (\Exception $e) {
                LogHandler::handle($this->logName, 'Failed to send email to ' . $email . ': ' . $e->getMessage());

                $results[$email] = false;
            } finally {
                $this->mailer->ClearAddresses();
            }
        }

        $this->mailer->smtpClose();

        return $results;
    }
}