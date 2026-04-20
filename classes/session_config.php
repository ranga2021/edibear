<?php
// Increase session security but ensure it works across the domain
if (session_status() === PHP_SESSION_NONE) {
    // This ensures the cookie is available for the whole domain
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', ''); // Leaves it to default or set to your domain
    ini_set('session.gc_maxlifetime', 1200);
    
    session_start();
}