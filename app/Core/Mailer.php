<?php
/**
 * Mailer: email sender.
 *
 * Uses PHPMailer if available (via composer autoload), otherwise falls back to
 * PHP's built-in mail() with proper headers. SMTP settings come from .env.
 *
 * SECURITY: never logs mail body or credentials. Used for email verification,
 * password reset, and outbid/winner notifications.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $from = Config::get('SMTP_FROM', 'no-reply@carauction.local');
        $fromName = Config::get('SMTP_FROM_NAME', 'Car Auction');

        // Prefer PHPMailer if installed
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return self::sendWithPhpMailer($to, $subject, $htmlBody, $textBody, $from, $fromName);
        }
        return self::sendWithMail($to, $subject, $htmlBody, $textBody, $from, $fromName);
    }

    private static function sendWithPhpMailer(string $to, string $subject, string $html, string $text, string $from, string $fromName): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $host = Config::get('SMTP_HOST');
            if ($host) {
                $mail->isSMTP();
                $mail->Host = $host;
                $mail->Port = (int) Config::get('SMTP_PORT', 587);
                $mail->SMTPAuth = true;
                $mail->Username = Config::get('SMTP_USER', '');
                $mail->Password = Config::get('SMTP_PASS', '');
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $text ?: strip_tags($html);
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            Logger::error('Mail send failed (PHPMailer): ' . $e->getMessage());
            return false;
        }
    }

    private static function sendWithMail(string $to, string $subject, string $html, string $text, string $from, string $fromName): bool
    {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . mb_encode_mimeheader($fromName) . ' <' . $from . '>';
        $body = $text !== '' ? $text : $html;
        $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
        if (!$ok) {
            Logger::error('Mail send failed (mail()) to ' . $to);
        }
        return $ok;
    }
}
