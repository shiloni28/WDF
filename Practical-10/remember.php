<?php

function issueRememberToken(PDO $pdo, int $userId): void
{
    $selector = bin2hex(random_bytes(12));
    $secret = bin2hex(random_bytes(32));
    $hash = hash("sha256", $secret);

    $expires = time() + 60 * 60 * 24 * 30;

    $stmt = $pdo->prepare(
        "INSERT INTO remember_tokens
         (user_id, selector, token_hash, expires_at)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $selector,
        $hash,
        date("Y-m-d H:i:s", $expires)
    ]);

    setcookie("remember_me", $selector . ":" . $secret, [
        "expires" => $expires,
        "path" => "/Studenthub-portal/Practical-10/",
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"])
            && $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax"
    ]);
}

function clearRememberCookie(): void
{
    setcookie("remember_me", "", [
        "expires" => time() - 3600,
        "path" => "/Studenthub-portal/Practical-10/",
        "httponly" => true,
        "secure" => !empty($_SERVER["HTTPS"])
            && $_SERVER["HTTPS"] !== "off",
        "samesite" => "Lax"
    ]);
}

function restoreRememberedLogin(): void
{
    global $pdo;

    if (!empty($_SESSION["user_id"])
        || empty($_COOKIE["remember_me"])) {
        return;
    }

    $parts = explode(":", $_COOKIE["remember_me"], 2);

    if (count($parts) !== 2 ||
        !ctype_xdigit($parts[0]) ||
        !ctype_xdigit($parts[1]) ||
        strlen($parts[0]) !== 24 ||
        strlen($parts[1]) !== 64) {
        clearRememberCookie();
        return;
    }

    [$selector, $secret] = $parts;

    $stmt = $pdo->prepare(
        "SELECT rt.*, u.username, u.role
         FROM remember_tokens rt
         JOIN users u ON u.id = rt.user_id
         WHERE rt.selector = ? AND rt.expires_at > NOW()"
    );
    $stmt->execute([$selector]);
    $row = $stmt->fetch();

    if (!$row ||
        !hash_equals(
            $row["token_hash"],
            hash("sha256", $secret)
        )) {
        clearRememberCookie();
        return;
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int)$row["user_id"];
    $_SESSION["username"] = $row["username"];
    $_SESSION["role"] = $row["role"];
    $_SESSION["last_activity"] = time();
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    $pdo->prepare(
        "DELETE FROM remember_tokens WHERE id = ?"
    )->execute([$row["id"]]);

    clearRememberCookie();
    issueRememberToken($pdo, (int)$row["user_id"]);
}