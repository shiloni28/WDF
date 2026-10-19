
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
$timeout = 60;

if (isset($_SESSION["user_id"])) {
    if (
        time() - ($_SESSION["last_activity"] ?? 0) > $timeout
    ) {
        $userId = (int) $_SESSION["user_id"];
        $stmt = $pdo->prepare(
            "DELETE FROM remember_tokens WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

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
