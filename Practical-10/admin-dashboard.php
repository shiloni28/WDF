<?php
require "auth.php";
requireRole("admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <h1>Admin Dashboard</h1>

    <fieldset>
        <h2>Welcome, Admin!</h2>
        <p>Username: <?= htmlspecialchars($_SESSION["username"]) ?></p>
        <p>Role: <?= htmlspecialchars($_SESSION["role"]) ?></p>
        <p>You have administrator access.</p>

        <form action="logout.php" method="POST">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">
            <input type="submit" value="Logout">
        </form>
    </fieldset>
</body>
</html>