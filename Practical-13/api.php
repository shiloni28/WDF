
<?php
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function readInput() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
        respond(400, [
            "success" => false,
            "message" => "Invalid JSON request."
        ]);
    }

    return $data;
}

function validateStudent($data) {
    $name = trim($data["name"] ?? "");
    $email = trim($data["email"] ?? "");
    $course = trim($data["course"] ?? "");

    if ($name === "" || $email === "" || $course === "") {
        respond(422, [
            "success" => false,
            "message" => "All fields are required."
        ]);
    }

    if (strlen($name) > 100 || strlen($course) > 100) {
        respond(422, [
            "success" => false,
            "message" => "Name and course must not exceed 100 characters."
        ]);
    }

    if (strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(422, [
            "success" => false,
            "message" => "Enter a valid email address."
        ]);
    }

    return [$name, $email, $course];
}

require_once "db.php";

try {
    $method = $_SERVER["REQUEST_METHOD"];
    if ($method === "GET") {
        $stmt = $pdo->query(
            "SELECT id, name, email, course, created_at
             FROM students ORDER BY id DESC"
        );

        respond(200, [
            "success" => true,
            "data" => $stmt->fetchAll()
        ]);
    }

    if ($method !== "POST") {
        header("Allow: GET, POST");

        respond(405, [
            "success" => false,
            "message" => "Method not allowed."
        ]);
    }

    $data = readInput();
    $action = $data["action"] ?? "";
    if ($action === "create") {
        [$name, $email, $course] = validateStudent($data);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO students (name, email, course)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $email, $course]);

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                respond(409, [
                    "success" => false,
                    "message" => "This email is already registered."
                ]);
            }
            throw $e;
        }

        respond(201, [
            "success" => true,
            "message" => "Student added successfully.",
            "id" => (int) $pdo->lastInsertId()
        ]);
    }
    if ($action === "update") {
        $id = filter_var(
            $data["id"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if ($id === false || $id === null) {
            respond(422, [
                "success" => false,
                "message" => "A valid student ID is required."
            ]);
        }

        [$name, $email, $course] = validateStudent($data);

        try {
            $stmt = $pdo->prepare(
                "UPDATE students
                 SET name = ?, email = ?, course = ?
                 WHERE id = ?"
            );
            $stmt->execute([$name, $email, $course, $id]);

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                respond(409, [
                    "success" => false,
                    "message" => "This email is already registered."
                ]);
            }
            throw $e;
        }

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare(
                "SELECT id FROM students WHERE id = ?"
            );
            $check->execute([$id]);

            if (!$check->fetch()) {
                respond(404, [
                    "success" => false,
                    "message" => "Student not found."
                ]);
            }
        }

        respond(200, [
            "success" => true,
            "message" => "Student updated successfully."
        ]);
    }
    if ($action === "delete") {
        $id = filter_var(
            $data["id"] ?? null,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 1]]
        );

        if ($id === false || $id === null) {
            respond(422, [
                "success" => false,
                "message" => "A valid student ID is required."
            ]);
        }

        $stmt = $pdo->prepare(
            "DELETE FROM students WHERE id = ?"
        );
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            respond(404, [
                "success" => false,
                "message" => "Student not found."
            ]);
        }

        respond(200, [
            "success" => true,
            "message" => "Student deleted successfully."
        ]);
    }

    respond(400, [
        "success" => false,
        "message" => "Unknown action."
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    respond(500, [
        "success" => false,
        "message" => "An internal server error occurred."
    ]);
}
?>
