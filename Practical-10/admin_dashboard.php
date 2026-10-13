
<?php
require_once "auth.php";
requireRole("admin");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <center>
        <h1>Student Hub Portal</h1>
        <fieldset>
            <h2>Admin Dashboard</h2>

            <h3>Welcome,
                <?php echo htmlspecialchars($_SESSION["name"]); ?>!
            </h3>

            <p>Email:
                <?php echo htmlspecialchars($_SESSION["email"]); ?>
            </p>

            <p>Role: Admin</p>

            <h3>Administration</h3>
            <p>Admin access verified successfully.</p>

            <a href="logout.php">Logout</a>
        </fieldset>
    </center>
</body>
</html>
