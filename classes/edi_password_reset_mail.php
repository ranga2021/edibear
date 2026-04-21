<?php

/**
 * Public site base URL (no trailing slash), for links in emails.
 */
function edibear_public_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
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
 * Send HTML password reset message. Uses PHP mail(); configure server or MAIL_FROM env.
 *
 * @return bool Whether mail() reported success (still not a guarantee of delivery).
 */
function edibear_send_password_reset_email($toEmail, $toName, $resetUrl)
{
    $toEmail = trim((string) $toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $subject = 'Reset your Edibear password';
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

    $from = getenv('MAIL_FROM');
    if ($from === false || $from === '') {
        $host = preg_replace('/^www\./i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $from = 'noreply@' . $host;
    }
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: Edibear <' . $from . ">\r\n";

    return @mail($toEmail, $subject, $html, $headers);
}
