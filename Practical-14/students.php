
<?php
require_once "auth.php";
audit_log("VIEW_STUDENTS", "Opened student records");

$rows = $conn->query(
    "SELECT id, name, email, course, created_at
     FROM students ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include "menu.php"; ?>
    <main class="main-content">
        <h2>Student Records</h2>
        <section class="content-card mt-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr>
                        <th>ID</th><th>Name</th><th>Email</th>
                        <th>Course</th><th>Created At</th>
                    </tr></thead>
                    <tbody>
                    <?php while ($r = $rows->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$r["id"] ?></td>
                            <td><?= htmlspecialchars($r["name"]) ?></td>
                            <td><?= htmlspecialchars($r["email"]) ?></td>
                            <td><?= htmlspecialchars($r["course"]) ?></td>
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
