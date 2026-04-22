<?php
// Increase session security but ensure it works across the domain
if (session_status() === PHP_SESSION_NONE) {
    $configured = ini_get('session.save_path');
    if (is_string($configured) && preg_match('/^\d+;(.+)$/', $configured, $m)) {
        $configured = $m[1];
    }
    $configured = is_string($configured) ? trim($configured) : '';
    $pathOk = $configured !== ''
        && @is_dir($configured)
        && @is_writable($configured);

    if (!$pathOk) {
        $fallback = sys_get_temp_dir();
        if (@is_dir($fallback) && @is_writable($fallback)) {
            session_save_path($fallback);
        } else {
            $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sessions';
            if (!is_dir($localPath)) {
                @mkdir($localPath, 0700, true);
            }
            if (@is_dir($localPath) && @is_writable($localPath)) {
                session_save_path($localPath);
            }
        }
    }

    // This ensures the cookie is available for the whole domain
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', ''); // Leaves it to default or set to your domain
    ini_set('session.gc_maxlifetime', 1200);

    session_start();
}