<?php
/**
 * Manual sitemap rebuild (sidebar / bookmarks). Content saves regenerate inline — see classes/edi_sitemap.php.
 */
require_once __DIR__ . '/../classes/session_config.php';
require_once __DIR__ . '/../classes/class.user.php';
require_once __DIR__ . '/../classes/edi_sitemap.php';

$user = new USER();

if (!$user->is_loggedin()) {
    $user->doLogout();
}

if (!$user->checkTimeout()) {
    $user->doLogout();
}

$redirect = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : '';
if ($redirect === '' || !preg_match('/^[a-z0-9_-]+$/i', $redirect)) {
    $user->redirect('./dashboard');
}

edi_regenerate_public_sitemap($user);
$user->redirect('./' . $redirect);
