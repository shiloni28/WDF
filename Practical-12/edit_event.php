
<?php
require_once 'db.php';
require_once 'upload_helper.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$id || $id < 1) {
    header('Location: index.php?error=' . urlencode('Invalid event ID.'));
    exit;
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    header('Location: index.php?error=' . urlencode('Event not found.'));
    exit;
}

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
            $upload = uploadPoster(
                $_FILES['poster'] ?? null,
                $event['poster']
            );

            $poster = $upload['poster'];

            $stmt = $conn->prepare(
                "UPDATE events
                 SET title=?, category=?, description=?, event_date=?,
                     venue=?, status=?, poster=?
                 WHERE id=?"
            );

            $stmt->bind_param(
                "sssssssi",
                $title,
                $category,
                $description,
                $event_date,
                $venue,
                $status,
                $poster,
                $id
            );

            $stmt->execute();

            if (
                $upload['new_file'] &&
                $event['poster'] !== $poster
            ) {
                removePoster($event['poster']);
            }

            header('Location: index.php?msg=' . urlencode('Event updated successfully.'));
            exit;
        } catch (Throwable $ex) {
            if ($upload && $upload['new_file']) {
                removePoster($upload['poster']);
            }

            $error = $ex instanceof RuntimeException
                ? $ex->getMessage()
                : 'Unable to update the event. Please try again.';
        }
    }

    $event['title'] = $title;
    $event['category'] = $category;
    $event['description'] = $description;
    $event['event_date'] = $event_date;
    $event['venue'] = $venue;
    $event['status'] = $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Event - Practical 12</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit Event</h1>

    <?php if ($error !== ''): ?>
        <p class="message error"><?= e($error) ?></p>
    <?php endif; ?>

    <fieldset>
        <legend>Update Event Details</legend>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">

            <div class="field">
                <label>Event Title</label>
                <input type="text" name="title" maxlength="150"
                       value="<?= e($event['title']) ?>" required>
            </div>

            <div class="field">
                <label>Category</label>
                <input type="text" name="category" maxlength="100"
                       value="<?= e($event['category']) ?>" required>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description" required><?= e($event['description']) ?></textarea>
            </div>

            <div class="field">
                <label>Event Date</label>
                <input type="date" name="event_date"
                       value="<?= e($event['event_date']) ?>" required>
            </div>

            <div class="field">
                <label>Venue</label>
                <input type="text" name="venue" maxlength="200"
                       value="<?= e($event['venue']) ?>" required>
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" required>
                    <option value="Open" <?= $event['status'] === 'Open' ? 'selected' : '' ?>>
                        Open
                    </option>
                    <option value="Closed" <?= $event['status'] === 'Closed' ? 'selected' : '' ?>>
                        Closed
                    </option>
                </select>
            </div>

            <div class="field">
                <label>Current Poster</label>

                <?php if ($event['poster']): ?>
                    <img class="poster-preview"
                         src="uploads/<?= rawurlencode(basename($event['poster'])) ?>"
                         alt="Current poster">
                <?php else: ?>
                    <p>No poster uploaded.</p>
                <?php endif; ?>

                <label>Upload Replacement (optional, maximum 2 MB)</label>
                <input type="file" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>

            <button type="submit">Update Event</button>
            <a href="index.php">Cancel</a>
        </form>
    </fieldset>
</div>
</body>
</html>
