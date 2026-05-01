<?php

/**
 * Public site base URL (no trailing slash), for links in emails.
 * Set PUBLIC_BASE_URL or EDIBEAR_PUBLIC_URL in the environment if the site sits behind a proxy
 * or if SCRIPT_NAME does not reflect the real public URL (e.g. https://edibear.groovymark.com).
 */
function edibear_public_base_url()
{
    $override = getenv('PUBLIC_BASE_URL') ?: getenv('EDIBEAR_PUBLIC_URL');
    if (is_string($override) && $override !== '') {
        return rtrim($override, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '/';
    $dir = rtrim(dirname($script), '/');
    if ($dir === '' || $dir === '.' || $dir === '/') {
        return $scheme . '://' . $host;
    }
    return $scheme . '://' . $host . $dir;
}

/**
 * Build HTML body for password reset (same for SMTP and mail()).
 */
function edibear_password_reset_email_html($toName, $resetUrl)
{
    $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
    $name = trim((string) $toName);
    $greet = $name !== '' ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : 'there';

    $html = '<!DOCTYPE html><html><body style="font-family:Segoe UI,Poppins,sans-serif;font-size:15px;color:#444;">';
    $html .= '<p>Hi ' . $greet . ',</p>';
    $html .= '<p>We received a request to reset your Edibear password. Use the button below. This link is valid for <strong>1 hour</strong>.</p>';
    $html .= '<p><a href="' . $safeUrl . '" style="display:inline-block;background:#33a675;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Reset password</a></p>';
    $html .= '<p style="word-break:break-all;font-size:13px;">Or copy this link:<br>' . $safeUrl . '</p>';
    $html .= '<p>If you did not request a reset, you can ignore this email.</p>';
    $html .= '</body></html>';

    return $html;
}

/**
 * Send HTML password reset message.
 *
 * Preferred: set SMTP env vars so PHPMailer delivers reliably (most hosts block plain mail()).
 *   MAIL_SMTP_HOST, MAIL_SMTP_PORT (default 587), MAIL_SMTP_USER, MAIL_SMTP_PASS
 *   MAIL_SMTP_SECURE: tls | ssl | (empty for none)
 *   MAIL_FROM (required for SMTP), MAIL_FROM_NAME (optional)
 *
 * Fallback: PHP mail() + MAIL_FROM or noreply@HTTP_HOST
 *
 * @return bool Whether sending reported success (delivery still depends on DNS/SPF/reputation).
 */
function edibear_send_password_reset_email($toEmail, $toName, $resetUrl)
{
    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = 'Reset your Edibear password';
    $html = edibear_password_reset_email_html($toName, $resetUrl);

    $smtpHost = getenv('MAIL_SMTP_HOST');
    if (is_string($smtpHost) && $smtpHost !== '') {
        return edibear_send_password_reset_via_smtp($toEmail, $subject, $html);
    }

    $from = getenv('MAIL_FROM');
    if ($from === false || $from === '') {
        $host = preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $from = 'noreply@' . $host;
    }
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: Edibear <' . $from . ">\r\n";

    return @mail($toEmail, $subject, $html, $headers);
}

/**
 * @internal
 */
function edibear_send_password_reset_via_smtp($toEmail, $subject, $html)
{
    $from = getenv('MAIL_FROM');
    if (!is_string($from) || $from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
        error_log('edibear password reset: MAIL_FROM must be a valid email when MAIL_SMTP_HOST is set.');
        return false;
    }

    $fromName = getenv('MAIL_FROM_NAME');
    if (!is_string($fromName) || $fromName === '') {
        $fromName = 'Edibear';
    }

    $host = getenv('MAIL_SMTP_HOST');
    $port = (int) (getenv('MAIL_SMTP_PORT') ?: 587);
    $user = getenv('MAIL_SMTP_USER');
    $pass = getenv('MAIL_SMTP_PASS');
    $secure = strtolower((string) (getenv('MAIL_SMTP_SECURE') ?: 'tls'));

    require_once __DIR__ . '/../src/Exception.php';
    require_once __DIR__ . '/../src/PHPMailer.php';
    require_once __DIR__ . '/../src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->Username = $user !== false ? $user : '';
        $mail->Password = $pass !== false ? $pass : '';
        $mail->SMTPAuth = ($mail->Username !== '' && $mail->Password !== '');
        if ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($secure === 'tls' || $secure === 'starttls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('edibear password reset SMTP error: ' . $e->getMessage());
        return false;
    }
}
