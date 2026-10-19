<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"])
            && $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax"
    ]);

    session_start();
}

require_once "db.php";
require_once "remember.php";

$timeout = 15 * 60;

if (isset($_SESSION["user_id"])) {
    if (time() - ($_SESSION["last_activity"] ?? 0) > $timeout) {
        $_SESSION = [];
        session_destroy();

        clearRememberCookie();

        header("Location: index.php?timeout=1");
        exit;
    }

    $_SESSION["last_activity"] = time();
} else {
    restoreRememberedLogin();

    if (empty($_SESSION["user_id"])) {
        header("Location: index.php");
        exit;
    }
}

function requireRole(string $role): void
{
    if (($_SESSION["role"] ?? "") !== $role) {
        http_response_code(403);
        exit("403 Forbidden: You are not authorized to access this page.");
    }
}