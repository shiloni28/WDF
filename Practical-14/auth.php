
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "db.php";

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT role, status FROM users WHERE id = ?"
);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if (
    !$currentUser ||
    $currentUser["role"] !== "admin" ||
    $currentUser["status"] !== "active"
) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

function audit_log($action, $details = "") {
    global $conn;

    $userId = (int) $_SESSION["user_id"];
    $ip = $_SERVER["REMOTE_ADDR"] ?? "Unknown";

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
         (user_id, action, details, ip_address)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $userId, $action, $details, $ip);
    $stmt->execute();
}
?>
