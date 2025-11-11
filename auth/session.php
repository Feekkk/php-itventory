<?php
/**
 * Session Management
 * Start session and provide helper functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a session message
 * 
 * @param string $type Message type: 'success', 'error', 'info', 'warning'
 * @param string $message Message content
 */
function setSessionMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'content' => $message
    ];
}

/**
 * Get and clear session message
 * 
 * @return array|null Message array or null
 */
function getSessionMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * 
 * @return array|null
 */
function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'staff_id' => $_SESSION['staff_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ];
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

