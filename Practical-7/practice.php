<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $age = $_POST["age"];
    $gender = $_POST["gender"];

    $fileName = __DIR__ . "/practice.csv";

    $fileExists = file_exists($fileName);

    $file = fopen($fileName, "a");

    if ($file === false) {
        die("Unable to create or open CSV file.");
    }

    if (!$fileExists) {
        fputcsv($file, array(
            "Name",
            "Email",
            "Contact Number",
            "Age",
            "Gender"
        ));
    }

    fputcsv($file, array(
        $name,
        $email,
        $contact,
        $age,
        $gender
    ));

    fclose($file);

    echo "Registration successful!";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Registration Form</title>
</head>

<body>

    <h1>Registration Form</h1>

    <form method="POST" action="practice.php">

        <label>Name:</label>
        <input type="text" name="name" required>

        <br><br>

        <label>Email:</label>
        <input type="email" name="email" required>

        <br><br>

        <label>Contact Number:</label>
        <input type="text" name="contact" required>

        <br><br>

        <label>Age:</label>
        <input type="number" name="age" required>

        <br><br>

        <label>Gender:</label>

        <input type="radio" name="gender" value="Male" required>
        <label>Male</label>

        <input type="radio" name="gender" value="Female">
        <label>Female</label>

        <br><br>

        <input type="submit" value="Register">

    </form>

</body>

</html>