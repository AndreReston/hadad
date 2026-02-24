<?php
session_start();

/* ✅ Clear all session variables */
$_SESSION = [];
session_unset();

/* ✅ If session uses cookies, delete the cookie too */
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

/* ✅ Destroy the session completely */
session_destroy();

/* ✅ Prevent caching */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

/* ✅ Redirect */
header("Location: login.php");
exit;
?>
