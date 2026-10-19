<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"])
        && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

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
    exit("Invalid request. Reload the login page.");
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $pdo->prepare(
    "SELECT id, username, password_hash, role
     FROM users WHERE username = ?"
);
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    header("Location: index.php?error=1");
    exit;
}
session_regenerate_id(true);

$_SESSION["user_id"] = (int)$user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];
$_SESSION["last_activity"] = time();
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));

$pdo->prepare(
    "UPDATE users SET last_login = NOW() WHERE id = ?"
)->execute([$user["id"]]);

if (isset($_POST["remember"]) && $_POST["remember"] === "1") {
    issueRememberToken($pdo, (int)$user["id"]);
} else {
    clearRememberCookie();
}

if ($user["role"] === "admin") {
    header("Location: admin-dashboard.php");
} else {
    header("Location: student-dashboard.php");
}
exit;