
<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request. Please submit the profile form.");
}
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
}
$name = clean_input($_POST["name"] ?? "");
$age = clean_input($_POST["age"] ?? "");
$gender = clean_input($_POST["gender"] ?? "");
$department = clean_input($_POST["department"] ?? "");
$email = clean_input($_POST["email"] ?? "");
$enrollment_id = clean_input($_POST["enrollment_id"] ?? "");
$contact = clean_input($_POST["contact"] ?? "");
$blood_group = clean_input($_POST["blood_group"] ?? "");
$caste = clean_input($_POST["caste"] ?? "");
$nationality = clean_input($_POST["nationality"] ?? "");
$address = clean_input($_POST["address"] ?? "");
$school_10th = clean_input($_POST["school_10th"] ?? "");
$percentage_10th = clean_input($_POST["percentage_10th"] ?? "");
$rank_10th = clean_input($_POST["rank_10th"] ?? "");

$school_12th = clean_input($_POST["school_12th"] ?? "");
$percentage_12th = clean_input($_POST["percentage_12th"] ?? "");
$rank_12th = clean_input($_POST["rank_12th"] ?? "");

$errors = [];
$required_fields = [
    "Name" => $name,
    "Age" => $age,
    "Gender" => $gender,
    "Department" => $department,
    "Email" => $email,
    "Enrollment ID" => $enrollment_id,
    "Contact number" => $contact,
    "Blood group" => $blood_group,
    "Caste" => $caste,
    "Nationality" => $nationality,
    "Address" => $address
];

foreach ($required_fields as $field => $value) {
    if ($value === "") {
        $errors[] = $field . " is required.";
    }
}
if ($name !== "" && !preg_match("/^[a-zA-Z ]+$/", $name)) {
    $errors[] = "Name should contain only letters and spaces.";
}
if (
    $age !== "" &&
    (!ctype_digit($age) || (int)$age < 1 || (int)$age > 100)
) {
    $errors[] = "Age must be between 1 and 100.";
}
if (
    $gender !== "" &&
    !in_array($gender, ["Male", "Female"], true)
) {
    $errors[] = "Invalid gender selected.";
}
if (
    $email !== "" &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $errors[] = "Please enter a valid email address.";
}
if ($contact !== "" && !preg_match("/^[0-9]{10}$/", $contact)) {
    $errors[] = "Contact number must contain exactly 10 digits.";
}

if ($percentage_10th !== "") {
    if (
        !is_numeric($percentage_10th) ||
        $percentage_10th < 0 ||
        $percentage_10th > 100
    ) {
        $errors[] = "10th percentage must be between 0 and 100.";
    }
}
if ($percentage_12th !== "") {
    if (
        !is_numeric($percentage_12th) ||
        $percentage_12th < 0 ||
        $percentage_12th > 100
    ) {
        $errors[] = "12th percentage must be between 0 and 100.";
    }
}

if (
    $rank_10th !== "" &&
    (!ctype_digit($rank_10th) || (int)$rank_10th < 1)
) {
    $errors[] = "10th rank must be a positive number.";
}

if (
    $rank_12th !== "" &&
    (!ctype_digit($rank_12th) || (int)$rank_12th < 1)
) {
    $errors[] = "12th rank must be a positive number.";
}
if (!empty($errors)) {

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Profile Error</title>";
    echo "<link rel='stylesheet' href='/Studenthub-portal/CSS/style.css'>";
    echo "</head>";
    echo "<body>";

    echo "<center>";
    echo "<h1>Profile Could Not Be Saved</h1>";
    echo "<h3>Please correct the following errors:</h3>";
    echo "<ul style='display:inline-block; text-align:left;'>";

    foreach ($errors as $error) {
        echo "<li>";
        echo htmlspecialchars($error, ENT_QUOTES, "UTF-8");
        echo "</li>";
    }

    echo "</ul><br>";

    echo "<a href='/Studenthub-portal/html/profile(2).html'>";
    echo "Go Back to Profile";
    echo "</a>";

    echo "</center>";
    echo "</body>";
    echo "</html>";

    exit;
}

$csv_file = __DIR__ . "/profiles.csv";

$file = fopen($csv_file, "a");

if ($file === false) {
    die("Error: Unable to open CSV file.");
}

if (!flock($file, LOCK_EX)) {
    fclose($file);
    die("Error: Unable to lock CSV file.");
}

clearstatcache(true, $csv_file);

if (filesize($csv_file) === 0) {

    $header = [
        "Name",
        "Age",
        "Gender",
        "Department",
        "Email",
        "Enrollment ID",
        "Contact",
        "Blood Group",
        "Caste",
        "Nationality",
        "Address",
        "10th School",
        "10th Percentage",
        "10th Rank",
        "12th School",
        "12th Percentage",
        "12th Rank",
        "Submitted At"
    ];

    if (fputcsv($file, $header) === false) {
        flock($file, LOCK_UN);
        fclose($file);
        die("Error: Unable to write CSV header.");
    }
}

$data = [
    $name,
    $age,
    $gender,
    $department,
    $email,
    $enrollment_id,
    $contact,
    $blood_group,
    $caste,
    $nationality,
    $address,
    $school_10th,
    $percentage_10th,
    $rank_10th,
    $school_12th,
    $percentage_12th,
    $rank_12th,
    date("Y-m-d H:i:s")
];
$result = fputcsv($file, $data);
flock($file, LOCK_UN);
fclose($file);
if ($result === false) {
    die("Error: Unable to save profile data.");
}
header("Location: /Studenthub-portal/html/profile%282%29.html?saved=1");
exit();
?>
