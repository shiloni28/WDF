
<?php
$host = "localhost";
$dbname = "studenthub_secure_login";
$dbuser = "root";
$dbpass = ""; // Default XAMPP configuration

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Database connection failed. Check XAMPP MySQL settings.");
}
?>
