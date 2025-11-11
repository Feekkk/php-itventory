<?php
/**
 * Logout Handler
 * Destroys session and redirects to login page
 */

require_once __DIR__ . '/session.php';

// Logout user
logout();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: login.php');
exit();

