<?php

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    die("Invalid request. Please submit the form using profile(2).html.");

}


function clean_input($data)
{
    $data = trim($data);

    $data = stripslashes($data);

    $data = htmlspecialchars(
        $data,
        ENT_QUOTES,
        "UTF-8"
    );

    return $data;
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


$school_10th = clean_input(
    $_POST["school_10th"] ?? ""
);

$percentage_10th = clean_input(
    $_POST["percentage_10th"] ?? ""
);

$rank_10th = clean_input(
    $_POST["rank_10th"] ?? ""
);


$school_12th = clean_input(
    $_POST["school_12th"] ?? ""
);

$percentage_12th = clean_input(
    $_POST["percentage_12th"] ?? ""
);

$rank_12th = clean_input(
    $_POST["rank_12th"] ?? ""
);


$errors = [];

if ($name == "") {

    $errors[] = "Name is required.";

}


if ($age == "") {

    $errors[] = "Age is required.";

}


if ($gender == "") {

    $errors[] = "Please select gender.";

}


if ($department == "") {

    $errors[] = "Department is required.";

}


if ($email == "") {

    $errors[] = "Email is required.";

}


if ($enrollment_id == "") {

    $errors[] = "Enrollment ID is required.";

}


if ($contact == "") {

    $errors[] = "Contact number is required.";

}


if ($blood_group == "") {

    $errors[] = "Blood group is required.";

}


if ($caste == "") {

    $errors[] = "Caste is required.";

}


if ($nationality == "") {

    $errors[] = "Nationality is required.";

}


if ($address == "") {

    $errors[] = "Address is required.";

}


if (
    $name != "" &&
    !preg_match("/^[a-zA-Z ]+$/", $name)
) {

    $errors[] =
        "Name should contain only letters and spaces.";

}


if (
    $age != "" &&
    (
        !ctype_digit($age) ||
        $age < 1 ||
        $age > 100
    )
) {

    $errors[] =
        "Age must be a valid number between 1 and 100.";

}

if (
    $gender != "" &&
    $gender != "Male" &&
    $gender != "Female"
) {

    $errors[] = "Invalid gender selected.";

}


if (
    $email != "" &&
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    $errors[] =
        "Please enter a valid email address.";

}

if (
    $contact != "" &&
    !preg_match(
        "/^[0-9]{10}$/",
        $contact
    )
) {

    $errors[] =
        "Contact number must contain exactly 10 digits.";

}

if ($percentage_10th != "") {

    if (
        !is_numeric($percentage_10th) ||
        $percentage_10th < 0 ||
        $percentage_10th > 100
    ) {

        $errors[] =
            "10th percentage must be between 0 and 100.";

    }

}

if ($percentage_12th != "") {

    if (
        !is_numeric($percentage_12th) ||
        $percentage_12th < 0 ||
        $percentage_12th > 100
    ) {

        $errors[] =
            "12th percentage must be between 0 and 100.";

    }

}


if (
    $rank_10th != "" &&
    (
        !ctype_digit($rank_10th) ||
        $rank_10th < 1
    )
) {

    $errors[] =
        "10th rank must be a positive number.";

}


if (
    $rank_12th != "" &&
    (
        !ctype_digit($rank_12th) ||
        $rank_12th < 1
    )
) {

    $errors[] =
        "12th rank must be a positive number.";

}


if (count($errors) > 0) {

    echo "<!DOCTYPE html>";

    echo "<html>";

    echo "<head>";

    echo "<title>Profile Error</title>";

    echo "</head>";

    echo "<body>";

    echo "<center>";

    echo "<h1>Profile Could Not Be Saved</h1>";

    echo "<h3>Please correct the following errors:</h3>";

    echo "<ul style='display:inline-block; text-align:left;'>";


    foreach ($errors as $error) {

        echo "<li>";

        echo htmlspecialchars($error);

        echo "</li>";

    }


    echo "</ul>";

    echo "<br>";

    echo "<a href='profile(2).html'>";
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

if (flock($file, LOCK_EX)) {

    if (filesize($csv_file) == 0) {

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


        fputcsv(
            $file,
            $header
        );

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


    fputcsv(
        $file,
        $data
    );


    flock(
        $file,
        LOCK_UN
    );

}


fclose($file);


echo "<script>";

echo "alert('Profile Saved Successfully!');";

echo "window.location.href = 'profile(2).html';";

echo "</script>";

?>