<?php
session_start();
require "db.php";
require "remember.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$csrf = $_POST["csrf_token"] ?? "";

if (!isset($_SESSION["csrf_token"]) ||
    !is_string($csrf) ||
    !hash_equals($_SESSION["csrf_token"], $csrf)) {
    http_response_code(403);
    exit("Invalid logout request.");
}

if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare(
        "DELETE FROM remember_tokens WHERE user_id = ?"
    );
    $stmt->execute([$_SESSION["user_id"]]);
}

clearRememberCookie();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => "Lax"
    ]);
}

session_destroy();

header("Location: index.php");
exit;