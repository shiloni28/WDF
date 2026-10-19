<?php
require_once "auth.php";
audit_log("VIEW_AUDIT_LOGS", "Opened audit log page");

$rows = $conn->query(
    "SELECT a.id, a.action, a.details, a.ip_address,
            a.created_at, u.name
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC LIMIT 200"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include "menu.php"; ?>
    <main class="main-content">
        <h2>Audit Logs</h2>
        <p class="text-secondary">Recent recorded activities.</p>

        <section class="content-card mt-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr>
                        <th>ID</th><th>Admin</th><th>Action</th>
                        <th>Details</th><th>IP Address</th><th>Date</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($r = $rows->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$r["id"] ?></td>
                            <td><?= htmlspecialchars($r["name"] ?? "Deleted user") ?></td>
                            <td><?= htmlspecialchars($r["action"]) ?></td>
                            <td><?= htmlspecialchars($r["details"] ?? "") ?></td>
                            <td><?= htmlspecialchars($r["ip_address"] ?? "") ?></td>
                            <td><?= htmlspecialchars($r["created_at"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
