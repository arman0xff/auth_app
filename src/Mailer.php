<?php

namespace Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;

        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '782e3128ea9335';
        $mail->Password = '2a6546011da3a4';

        $mail->setFrom('from@example.com', 'Mailer');

        return $mail;
    }

    public static function sendMail(string $to, string $subject, string $body): void
    {
        $mail = self::getMailer();

        try {
            //Recipients
            $mail->addAddress($to);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch
        (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public static function sendVerificationMail(string $email, string $name, string $token): void {
        // rate limiting need

        $link = "localhost:8000" . VERIFY_EMAIL_ROUTE . "?token=" . $token;

        $verification_message = 'Your verification link is ' . $link;

        Mailer::sendMail($email, 'Hello, ' . $name . '!', $verification_message);
    }
}