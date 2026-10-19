
<?php
session_start();
require_once "db.php";

if (
    isset($_SESSION["user_id"]) &&
    ($_SESSION["role"] ?? "") === "admin"
) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role, status
         FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (
        $user &&
        $user["status"] === "active" &&
        password_verify($password, $user["password"])
    ) {
        if ($user["role"] !== "admin") {
            $error = "Access denied. Admin access only.";
        } else {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            $log = $conn->prepare(
                "INSERT INTO audit_logs
                 (user_id, action, details, ip_address)
                 VALUES (?, ?, ?, ?)"
            );
            $action = "LOGIN";
            $details = "Administrator logged in";
            $ip = $_SERVER["REMOTE_ADDR"] ?? "Unknown";
            $log->bind_param("isss", $user["id"], $action, $details, $ip);
            $log->execute();

            header("Location: index.php");
            exit;
        }
    } else {
        $error = "Invalid credentials or inactive account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Practical 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-card">
    <h2>StudentHub Admin</h2>
    <p class="text-secondary">Sign in to manage your portal</p>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   required autocomplete="username">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control"
                   required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
