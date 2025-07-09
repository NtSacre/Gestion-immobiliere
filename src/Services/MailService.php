<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Config SMTP
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'sacrentandou2.0@gmail.com';
        $this->mail->Password = 'mettretonmotdepasseici'; // Remplacez par votre mot de passe réel  que chacun va creer une apk sur google
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port = 587;

        // Paramètres email
        $this->mail->setFrom('sacrentandou2.0@gmail.com', 'ImmoApp');
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }

    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erreur mail : {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
