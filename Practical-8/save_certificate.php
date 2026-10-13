
<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Please submit the certificate form.");
}

$certificate_name = trim($_POST["certificate_name"] ?? "");
$category = $_POST["category"] ?? "";
$event_name = trim($_POST["event_name"] ?? "");
$organized_by = trim($_POST["organized_by"] ?? "");
$status = $_POST["status"] ?? "";
$completion_date = !empty($_POST["completion_date"])
    ? $_POST["completion_date"]
    : null;
$description = trim($_POST["description"] ?? "");

$allowed_categories = ["Tech", "Non-Tech", "Workshop", "Seminar"];
$allowed_statuses = ["Approved", "Rejected", "Pending"];

if (
    $certificate_name === "" ||
    $event_name === "" ||
    $organized_by === "" ||
    !in_array($category, $allowed_categories, true) ||
    !in_array($status, $allowed_statuses, true)
) {
    exit("Please enter valid certificate details.");
}

$certificate_file = "";

if (
    isset($_FILES["certificate_file"]) &&
    $_FILES["certificate_file"]["error"] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES["certificate_file"]["error"] !== UPLOAD_ERR_OK) {
        exit("Certificate upload failed.");
    }

    $extension = strtolower(
        pathinfo($_FILES["certificate_file"]["name"], PATHINFO_EXTENSION)
    );

    $allowed_extensions = ["pdf", "jpg", "jpeg", "png"];

    if (!in_array($extension, $allowed_extensions, true)) {
        exit("Only PDF, JPG, JPEG and PNG files are allowed.");
    }

    if ($_FILES["certificate_file"]["size"] > 5 * 1024 * 1024) {
        exit("File size must not exceed 5 MB.");
    }

    $upload_dir = __DIR__ . "/../uploads/";

    if (!is_dir($upload_dir) &&
        !mkdir($upload_dir, 0755, true)) {
        exit("Could not create the upload folder.");
    }

    $new_filename = uniqid("certificate_", true) . "." . $extension;
    $destination = $upload_dir . $new_filename;

    if (!move_uploaded_file(
        $_FILES["certificate_file"]["tmp_name"],
        $destination
    )) {
        exit("Could not save the uploaded certificate.");
    }
    $certificate_file = "uploads/" . $new_filename;
}

$sql = "INSERT INTO certificate_details
        (certificate_name, category, event_name, organized_by,
         status, completion_date, description, certificate_file)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    exit("Could not prepare the database query.");
}

$stmt->bind_param(
    "ssssssss",
    $certificate_name,
    $category,
    $event_name,
    $organized_by,
    $status,
    $completion_date,
    $description,
    $certificate_file
);

if ($stmt->execute()) {
    echo "<h2>Certificate details saved successfully!</h2>";
    echo "<p>Your certificate data has been stored in MySQL.</p>";
    echo '<p><a href="../html/certificates(4).html">Add another certificate</a></p>';
    echo '<p><a href="../html/studentdashboard(2).html">Back to Dashboard</a></p>';
} else {
    echo "Unable to save certificate details.";
}

$stmt->close();
$conn->close();
?>



