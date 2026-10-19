
<?php
require_once 'db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR venue LIKE ? OR description LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term]);
    $types .= "sss";
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (in_array($status, ['Open', 'Closed'], true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY event_date DESC, id DESC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

$categories = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 12 - Event Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

    <header class="page-header">
        <h1>Event Management System</h1>
        <p>Practical 12: CRUD with Poster Upload and File Validation</p>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <p class="message success">
            <?= e($_GET['msg']) ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p class="message error">
            <?= e($_GET['error']) ?>
        </p>
    <?php endif; ?>

    <fieldset>
        <legend>Manage Events</legend>
        <div class="actions">
            <a href="add_event.php">+ Add New Event</a>
        </div>
    </fieldset>

    <fieldset>
        <legend>Search and Filter</legend>

        <form method="get" class="filter-form">
            <input type="text" name="search"
                   placeholder="Search title or venue"
                   value="<?= e($search) ?>">

            <select name="category">
                <option value="">All Categories</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= e($c['category']) ?>"
                        <?= $category === $c['category'] ? 'selected' : '' ?>>
                        <?= e($c['category']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="Open"
                    <?= $status === 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="Closed"
                    <?= $status === 'Closed' ? 'selected' : '' ?>>Closed</option>
            </select>

            <button type="submit">Apply Filter</button>
            <a href="index.php">Reset</a>
        </form>
    </fieldset>

    <h2>Event Records</h2>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Poster</th>
                <th>Title / Description</th>
                <th>Category</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$event['id'] ?></td>
                        <td>
                            <?php if ($event['poster']): ?>
                                <img src="uploads/<?= rawurlencode(basename($event['poster'])) ?>"
                                     alt="Event poster">
                            <?php else: ?>
                                No poster
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($event['title']) ?></strong>
                            <br>
                            <?= nl2br(e($event['description'])) ?>
                        </td>
                        <td><?= e($event['category']) ?></td>
                        <td><?= e($event['event_date']) ?></td>
                        <td><?= e($event['venue']) ?></td>
                        <td><?= e($event['status']) ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit_event.php?id=<?= (int)$event['id'] ?>">
                                    Edit
                                </a>

                                <form class="inline-form"
                                      action="delete_event.php"
                                      method="post"
                                      onsubmit="return confirm('Delete this event?');">
                                    <input type="hidden" name="id"
                                           value="<?= (int)$event['id'] ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No events found. Add your first event.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
