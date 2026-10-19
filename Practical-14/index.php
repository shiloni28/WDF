
<?php
require_once "auth.php";

audit_log("VIEW_DASHBOARD", "Opened admin dashboard");

function getCount($conn, $table) {
    $allowed = ["users", "students", "events", "registrations"];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    return (int) $result->fetch_assoc()["total"];
}

$userCount = getCount($conn, "users");
$studentCount = getCount($conn, "students");
$eventCount = getCount($conn, "events");
$registrationCount = getCount($conn, "registrations");

$latest = $conn->query(
    "SELECT id, name, email, created_at
     FROM students ORDER BY id DESC LIMIT 5"
);

$recentLogs = $conn->query(
    "SELECT a.action, a.details, a.created_at, u.name
     FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.id DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Practical 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
    <?php include "menu.php"; ?>

    <main class="main-content">
        <div class="topbar">
            <div>
                <h2>Admin Dashboard</h2>
                <p class="text-secondary mb-0">
                    Welcome, <?= htmlspecialchars($_SESSION["name"]) ?>!
                </p>
            </div>
            <span class="badge text-bg-success">Administrator</span>
        </div>

        <div class="row g-3 my-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <p>Total Users</p><h2><?= $userCount ?></h2>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <p>Total Students</p><h2><?= $studentCount ?></h2>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <p>Total Events</p><h2><?= $eventCount ?></h2>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <p>Registrations</p><h2><?= $registrationCount ?></h2>
                </div>
            </div>
        </div>

        <section class="content-card">
            <h4>Latest Student Registrations</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Created At</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($s = $latest->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$s["id"] ?></td>
                            <td><?= htmlspecialchars($s["name"]) ?></td>
                            <td><?= htmlspecialchars($s["email"]) ?></td>
                            <td><?= htmlspecialchars($s["created_at"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="content-card mt-4">
            <h4>Recent Audit Activity</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr><th>Admin</th><th>Action</th><th>Details</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($log = $recentLogs->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($log["name"] ?? "Deleted user") ?></td>
                            <td><?= htmlspecialchars($log["action"]) ?></td>
                            <td><?= htmlspecialchars($log["details"] ?? "") ?></td>
                            <td><?= htmlspecialchars($log["created_at"]) ?></td>
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
