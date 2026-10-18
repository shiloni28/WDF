
<?php
require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.html");
    exit;
}

$username = trim($_POST["username"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (!preg_match('/\A[A-Za-z0-9_]{3,20}\z/', $username)) {
    exit("Invalid username. Use 3-20 letters, numbers or underscores.");
}

if (
    strlen($email) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    exit("Please enter a valid email address.");
}

if (strlen($password) < 8 || strlen($password) > 72) {
    exit("Password must contain 8-72 characters.");
}

if ($password !== $confirmPassword) {
    exit("Passwords do not match.");
}

try {

    $check = $conn->prepare(
        "SELECT username, email
         FROM users
         WHERE username = :username OR email = :email"
    );

    $check->execute([
        "username" => $username,
        "email" => $email
    ]);

    $existingUser = $check->fetch();

    if ($existingUser) {
        if (strtolower($existingUser["email"]) === $email) {
            exit("This email address is already registered.");
        }

        if ($existingUser["username"] === $username) {
            exit("This username is already taken.");
        }
    }

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

  
    $insert = $conn->prepare(
        "INSERT INTO users (username, email, password)
         VALUES (:username, :email, :password)"
    );

    $insert->execute([
        "username" => $username,
        "email" => $email,
        "password" => $passwordHash
    ]);

    echo "<h2>Registration Successful!</h2>";
    echo "<p>Your account has been created successfully.</p>";
    echo '<a href="register.html">Register another account</a>';

} catch (PDOException $e) {
    if ($e->getCode() === "23000") {
        http_response_code(409);
        exit("This username or email is already registered.");
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit("Registration failed. Please try again.");
}
?>
