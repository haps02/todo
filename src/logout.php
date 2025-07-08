<?php
session_start();

// Unset all session variables
session_unset();

// Destroy session data on the server
session_destroy();

// Delete the session cookie on the client
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Redirect to login
header("Location: login.php");
exit();

