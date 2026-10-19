<?php
require "db.php";

$users = [
    ["admin", "Admin@123", "admin"],
    ["student", "Student@123", "student"]
];

$sql = "INSERT INTO users (username, password_hash, role)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        password_hash = VALUES(password_hash),
        role = VALUES(role)";

$stmt = $pdo->prepare($sql);

foreach ($users as $user) {
    $stmt->execute([
        $user[0],
        password_hash($user[1], PASSWORD_DEFAULT),
        $user[2]
    ]);
}

echo "Demo accounts created successfully.";