
<?php
require_once "auth.php";
requireRole("student");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <center>
        <h1>Student Hub Portal</h1>
        <fieldset>
            <h2>Student Dashboard</h2>

            <h3>Welcome,
                <?php echo htmlspecialchars($_SESSION["name"]); ?>!
            </h3>

            <p>Student ID:
                <?php echo htmlspecialchars($_SESSION["student_id"] ?? ""); ?>
            </p>

            <p>Email:
                <?php echo htmlspecialchars($_SESSION["email"]); ?>
            </p>

            <p>Role: Student</p>

            <a href="logout.php">Logout</a>
        </fieldset>
    </center>
</body>
</html>
