
<?php
require_once "db.php";

$accounts = [
    ["Admin User", "admin@studenthub.com", "Admin@123", "admin"],
    ["Demo User", "user@studenthub.com", "User@123", "user"]
];

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
     name = VALUES(name), password = VALUES(password),
     role = VALUES(role), status = 'active'"
);

foreach ($accounts as $account) {
    [$name, $email, $plainPassword, $role] = $account;
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt->bind_param("ssss", $name, $email, $hash, $role);
    $stmt->execute();
}

echo "Setup complete. Test accounts are ready.";
echo "<br><a href='login.php'>Go to Login</a>";
?>
