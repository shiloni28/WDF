
<?php
require_once 'db.php';
require_once 'upload_helper.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$title = '';
$category = '';
$description = '';
$event_date = '';
$venue = '';
$status = 'Open';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $status = $_POST['status'] ?? 'Open';

    if (
        $title === '' || strlen($title) > 150 ||
        $category === '' || strlen($category) > 100 ||
        $description === '' ||
        $venue === '' || strlen($venue) > 200
    ) {
        $error = 'Fill in all required fields within the allowed limits.';
    } elseif (!in_array($status, ['Open', 'Closed'], true)) {
        $error = 'Select a valid event status.';
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date) ||
        !checkdate(
            (int)substr($event_date, 5, 2),
            (int)substr($event_date, 8, 2),
            (int)substr($event_date, 0, 4)
        )
    ) {
        $error = 'Select a valid event date.';
    } else {
        $upload = null;

        try {
            $upload = uploadPoster($_FILES['poster'] ?? null);

            $stmt = $conn->prepare(
                "INSERT INTO events
                (title, category, description, event_date, venue, status, poster)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssss",
                $title,
                $category,
                $description,
                $event_date,
                $venue,
                $status,
                $upload['poster']
            );

            $stmt->execute();

            header('Location: index.php?msg=' . urlencode('Event added successfully.'));
            exit;
        } catch (Throwable $ex) {
            if ($upload && $upload['new_file']) {
                removePoster($upload['poster']);
            }

            $error = $ex instanceof RuntimeException
                ? $ex->getMessage()
                : 'Unable to save the event. Please check the database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Event - Practical 12</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Add New Event</h1>

    <?php if ($error !== ''): ?>
        <p class="message error"><?= e($error) ?></p>
    <?php endif; ?>

    <fieldset>
        <legend>Event Details</legend>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">

            <div class="field">
                <label>Event Title</label>
                <input type="text" name="title" maxlength="150"
                       value="<?= e($title) ?>" required>
            </div>

            <div class="field">
                <label>Category</label>
                <input type="text" name="category" maxlength="100"
                       placeholder="Workshop, Seminar, Sports..."
                       value="<?= e($category) ?>" required>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description" required><?= e($description) ?></textarea>
            </div>

            <div class="field">
                <label>Event Date</label>
                <input type="date" name="event_date"
                       value="<?= e($event_date) ?>" required>
            </div>

            <div class="field">
                <label>Venue</label>
                <input type="text" name="venue" maxlength="200"
                       value="<?= e($venue) ?>" required>
            </div>

            <div class="field">
                <label>Event Status</label>
                <select name="status" required>
                    <option value="Open" <?= $status === 'Open' ? 'selected' : '' ?>>
                        Open
                    </option>
                    <option value="Closed" <?= $status === 'Closed' ? 'selected' : '' ?>>
                        Closed
                    </option>
                </select>
            </div>

            <div class="field">
                <label>Poster (optional, JPG/PNG/WebP, maximum 2 MB)</label>
                <input type="file" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>

            <button type="submit">Save Event</button>
            <a href="index.php">Cancel</a>
        </form>
    </fieldset>
</div>
</body>
</html>
