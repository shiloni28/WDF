
<?php
session_start();
require_once "db.php";

if (isset($_SESSION["user_id"])) {
    $userId = (int)$_SESSION["user_id"];
    $action = "LOGOUT";
    $details = "User logged out";
    $ip = $_SERVER["REMOTE_ADDR"] ?? "Unknown";

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
         (user_id, action, details, ip_address)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $userId, $action, $details, $ip);
    $stmt->execute();
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit;
?>
