<?php
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"])
        && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

if (!empty($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

require "remember.php";
restoreRememberedLogin();

if (!empty($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Login - Student Hub</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <h1>WELCOME TO STUDENT HUB PORTAL!</h1>

    <fieldset>
        <legend>SECURE LOGIN</legend>

        <?php if (isset($_GET["error"])): ?>
            <p>Invalid username or password.</p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label>Enter Student ID / Username</label><br>
            <input type="text" name="username"
                   required maxlength="100" autocomplete="username">

            <br><br>

            <label>Enter Password</label><br>
            <input type="password" name="password"
                   required autocomplete="current-password">

            <br><br>

            <label>
                <input type="checkbox" name="remember" value="1">
                Remember Me
            </label>

            <br><br>

            <input type="hidden" name="csrf_token"
                   value="<?php
                   echo htmlspecialchars(
                       $_SESSION["csrf_token"] ??=
                       bin2hex(random_bytes(32))
                   );
                   ?>">

            <input type="submit" value="Login">
        </form>
    </fieldset>
</body>
</html>