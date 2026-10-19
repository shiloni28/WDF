
<?php
require_once 'db.php';
require_once 'upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php?error=' . urlencode('Invalid event ID.'));
    exit;
}

try {
    $stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        header('Location: index.php?error=' . urlencode('Event not found.'));
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    removePoster($event['poster']);

    header('Location: index.php?msg=' . urlencode('Event deleted successfully.'));
    exit;

} catch (Throwable $ex) {
    header('Location: index.php?error=' . urlencode('Unable to delete the event.'));
    exit;
}
?>
