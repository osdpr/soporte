<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

function send_mail(string $to, string $subject, string $htmlBody, ?string $bcc=null): bool {
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;           // smtp.gmail.com
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;           // sopportdpr@gmail.com
    $mail->Password   = SMTP_PASS;           // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // tls
    $mail->Port       = (int)SMTP_PORT;      // 587

    // 👇 Esto evita el “relajo” de caracteres
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom(SMTP_USER, 'Soporte DPR');
    $mail->addAddress($to);

    // BCC solo si existe y es distinto del TO (evita rebotes)
    if ($bcc && filter_var($bcc, FILTER_VALIDATE_EMAIL) && strcasecmp($bcc,$to)!==0) {
      $mail->addBCC($bcc);
    }

    // 👇 MUY IMPORTANTE: enviar como HTML
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlBody;          // NO uses htmlspecialchars aquí
    $mail->AltBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody));

    return $mail->send();
  } catch (Exception $e) {
    error_log('Mailer Error: '.$mail->ErrorInfo);
    return false;
  }
}
