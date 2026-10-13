
<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/remember.php";

function endAuthSession(): void
{
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
}

function redirectTo(string $page): void
{
    header("Location: " . $page);
    exit;
}

// Check inactivity timeout: 15 minutes.
if (isset($_SESSION["user_id"])) {
    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] > 900
    ) {
        deleteRememberToken($pdo);
        endAuthSession();
        redirectTo("timeout.php");
    }

    $_SESSION["last_activity"] = time();
}

// Attempt Remember Me authentication when there is no session.
if (!isset($_SESSION["user_id"])) {
    $cookie = $_COOKIE["studenthub_remember"] ?? "";

    if (preg_match('/^([a-f0-9]{24}):([a-f0-9]{64})$/', $cookie, $m)) {
        $selector = $m[1];
        $validator = $m[2];

        $stmt = $pdo->prepare(
            "SELECT rt.user_id, rt.token_hash, rt.expires_at,
                    u.student_id, u.name, u.email, u.role
             FROM remember_tokens rt
             JOIN users u ON u.id = rt.user_id
             WHERE rt.selector = ?"
        );

        $stmt->execute([$selector]);
        $token = $stmt->fetch();

        if (
            $token &&
            strtotime($token["expires_at"]) > time() &&
            hash_equals(
                $token["token_hash"],
                hash("sha256", $validator)
            )
        ) {
            // Consume the old token before creating a replacement.
            $stmt = $pdo->prepare(
                "DELETE FROM remember_tokens WHERE selector = ?"
            );
            $stmt->execute([$selector]);

            session_regenerate_id(true);

            $_SESSION["user_id"] = (int) $token["user_id"];
            $_SESSION["student_id"] = $token["student_id"];
            $_SESSION["name"] = $token["name"];
            $_SESSION["email"] = $token["email"];
            $_SESSION["role"] = $token["role"];
            $_SESSION["last_activity"] = time();

            createRememberToken($pdo, (int) $token["user_id"]);
        } else {
            deleteRememberToken($pdo);
        }
    }
}

function requireLogin(): void
{
    if (!isset($_SESSION["user_id"])) {
        redirectTo("../index.html");
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if (($_SESSION["role"] ?? "") !== $role) {
        redirectTo("unauthorized.php");
    }
}
?>
