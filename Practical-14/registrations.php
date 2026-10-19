
<?php
require_once "auth.php";
audit_log("VIEW_REGISTRATIONS", "Opened event registrations");

$rows = $conn->query(
    "SELECT r.id, s.name AS student_name,
            e.title AS event_title, r.registered_at
     FROM registrations r
     JOIN students s ON r.student_id = s.id
     JOIN events e ON r.event_id = e.id
     ORDER BY r.id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include "menu.php"; ?>
    <main class="main-content">
        <h2>Event Registrations</h2>
        <section class="content-card mt-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr>
                        <th>ID</th><th>Student</th>
                        <th>Event</th><th>Registered At</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($r = $rows->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$r["id"] ?></td>
                            <td><?= htmlspecialchars($r["student_name"]) ?></td>
                            <td><?= htmlspecialchars($r["event_title"]) ?></td>
                            <td><?= htmlspecialchars($r["registered_at"]) ?></td>
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
