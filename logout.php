<?php
include "include/config.inc";

// Unset all the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"]  //removed ", $params["httponly"]" - required php 5.2$params["httponly"]
    );
}


session_destroy();
header("location:login.php?action=logout");