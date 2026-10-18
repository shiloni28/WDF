<?php
require_once "db.php";
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function redirectToList($message, $type = "success") {
    header(
        "Location: index.php?message=" .
        urlencode($message) .
        "&type=" . urlencode($type)
    );
    exit;
}

$message = $_GET["message"] ?? "";
$type = $_GET["type"] ?? "success";
$editStudent = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    try {
        if ($action === "add" || $action === "update") {
            $name = trim($_POST["name"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $course = trim($_POST["course"] ?? "");
            if ($name === "" || $email === "" || $course === "") {
                redirectToList("All fields are required.", "error");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                redirectToList("Enter a valid email address.", "error");
            }

            if (
                strlen($name) > 100 ||
                strlen($email) > 150 ||
                strlen($course) > 100
            ) {
                redirectToList(
                    "One or more fields exceed the allowed length.",
                    "error"
                );
            }

            $allowedCourses = ["BCA", "BSc IT", "BTech CSE"];

            if (!in_array($course, $allowedCourses, true)) {
                redirectToList("Please select a valid course.", "error");
            }

            if ($action === "add") {
                $sql = "INSERT INTO students
                        (name, email, course)
                        VALUES (:name, :email, :course)";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ":name" => $name,
                    ":email" => $email,
                    ":course" => $course
                ]);

                redirectToList("Student added successfully.");
            }

            if ($action === "update") {
                $id = filter_var(
                    $_POST["id"] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id || $id < 1) {
                    redirectToList("Invalid student ID.", "error");
                }

                $sql = "UPDATE students
                        SET name = :name,
                            email = :email,
                            course = :course
                        WHERE id = :id";

                $stmt = $conn->prepare($sql);

                $stmt->execute([
                    ":name" => $name,
                    ":email" => $email,
                    ":course" => $course,
                    ":id" => $id
                ]);

                redirectToList("Student updated successfully.");
            }
        }

        if ($action === "delete") {
            $id = filter_var(
                $_POST["id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                redirectToList("Invalid student ID.", "error");
            }

            $stmt = $conn->prepare(
                "DELETE FROM students WHERE id = :id"
            );

            $stmt->execute([":id" => $id]);

            if ($stmt->rowCount() > 0) {
                redirectToList("Student deleted successfully.");
            }

            redirectToList("Student record was not found.", "error");
        }

    } catch (PDOException $ex) {
        error_log($ex->getMessage());

        if ($ex->getCode() === "23000") {
            redirectToList(
                "This email already exists. Please use another email.",
                "error"
            );
        }

        redirectToList(
            "Database operation failed. Please try again.",
            "error"
        );
    }
}
if (isset($_GET["edit"])) {
    $editId = filter_var(
        $_GET["edit"],
        FILTER_VALIDATE_INT
    );
    if ($editId && $editId > 0) {
        $stmt = $conn->prepare(
            "SELECT * FROM students WHERE id = :id"
        );
        $stmt->execute([":id" => $editId]);
        $editStudent = $stmt->fetch();
        if (!$editStudent) {
            $message = "Student record was not found.";
            $type = "error";
        }
    }
}
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$allowedCourses = ["BCA", "BSc IT", "BTech CSE"];
if (
    $courseFilter !== "" &&
    !in_array($courseFilter, $allowedCourses, true)
) {
    $courseFilter = "";
}
$where = [];
$params = [];
if ($search !== "") {
    $where[] = "(name LIKE :name
                 OR email LIKE :email
                 OR course LIKE :searchCourse)";

    $term = "%" . $search . "%";

    $params[":name"] = $term;
    $params[":email"] = $term;
    $params[":searchCourse"] = $term;
}
if ($courseFilter !== "") {
    $where[] = "course = :filterCourse";
    $params[":filterCourse"] = $courseFilter;
}
$whereSQL = count($where) > 0
    ? " WHERE " . implode(" AND ", $where)
    : "";
$countStmt = $conn->prepare(
    "SELECT COUNT(*) FROM students" . $whereSQL
);
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$limit = 5;
$page = filter_var(
    $_GET["page"] ?? 1,
    FILTER_VALIDATE_INT
);

$page = ($page && $page > 0) ? $page : 1;
$totalPages = max(
    1,
    (int)ceil($totalRecords / $limit)
);
$page = min($page, $totalPages);
$offset = ($page - 1) * $limit;
$sql = "SELECT * FROM students" . $whereSQL .
       " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();
$queryParams = [];

if ($search !== "") {
    $queryParams["search"] = $search;
}

if ($courseFilter !== "") {
    $queryParams["course"] = $courseFilter;
}

$baseQuery = http_build_query($queryParams);
$separator = ($baseQuery === "") ? "" : "&";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header class="page-header">
        <h1>Student Management System</h1>
        <p>Manage student records with CRUD, search and filters.</p>
    </header>
    <?php if ($message !== ""): ?>
        <div class="message <?= e($type) ?>">
            <?= e($message) ?>
        </div>
    <?php endif; ?>
    <section class="panel">
        <h2>
            <?= $editStudent ? "Edit Student" : "Add New Student" ?>
        </h2>
        <form method="POST" action="index.php" class="student-form">
            <input type="hidden"
                   name="action"
                   value="<?= $editStudent ? "update" : "add" ?>">
            <?php if ($editStudent): ?>
                <input type="hidden"
                       name="id"
                       value="<?= e($editStudent["id"]) ?>">
            <?php endif; ?>
            <label for="name">Student Name</label>
            <input type="text"
                   id="name"
                   name="name"
                   maxlength="100"
                   required
                   value="<?= e($editStudent["name"] ?? "") ?>">

            <label for="email">Email Address</label>
            <input type="email"
                   id="email"
                   name="email"
                   maxlength="150"
                   required
                   value="<?= e($editStudent["email"] ?? "") ?>">

            <label for="course">Course</label>

            <select id="course" name="course" required>
                <option value="">Select a course</option>

                <?php foreach ($allowedCourses as $course): ?>
                    <option value="<?= e($course) ?>"
                        <?= (($editStudent["course"] ?? "") === $course)
                            ? "selected" : "" ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">
                <?= $editStudent ? "Update Student" : "Add Student" ?>
            </button>

            <?php if ($editStudent): ?>
                <a href="index.php" class="btn secondary">
                    Cancel Edit
                </a>
            <?php endif; ?>

        </form>
    </section>
    <section class="panel">
        <h2>Search and Filter Students</h2>
        <form method="GET" action="index.php" class="filter-form">
            <input type="text"
                   name="search"
                   placeholder="Search name, email or course"
                   value="<?= e($search) ?>">
            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($allowedCourses as $course): ?>
                    <option value="<?= e($course) ?>"
                        <?= $courseFilter === $course
                            ? "selected" : "" ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Search</button>
            <a href="index.php" class="btn secondary">Reset</a>
            <a href="export_csv.php" class="btn export">
                Export CSV
            </a>
        </form>
        <p class="record-count">
            Matching records: <?= $totalRecords ?>
        </p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student["id"]) ?></td>
                            <td><?= e($student["name"]) ?></td>
                            <td><?= e($student["email"]) ?></td>
                            <td><?= e($student["course"]) ?></td>
                            <td><?= e($student["created_at"]) ?></td>
                            <td class="actions">
                                <a class="btn small"
                                   href="index.php?edit=<?= e($student["id"]) ?>">
                                    Edit
                                </a>
                                <form method="POST"
                                      action="index.php"
                                      onsubmit="return confirm('Are you sure you want to permanently delete this student?');">
                                    <input type="hidden"
                                           name="action"
                                           value="delete">
                                    <input type="hidden"
                                           name="id"
                                           value="<?= e($student["id"]) ?>">
                                    <button type="submit"
                                            class="btn danger small">
                                        Delete
                                    </button>

                                </form>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">
                            No matching student records found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= e($baseQuery) ?><?= $separator ?>page=<?= $page - 1 ?>">
                    Previous
                </a>
            <?php endif; ?>
            <span>
                Page <?= $page ?> of <?= $totalPages ?>
            </span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= e($baseQuery) ?><?= $separator ?>page=<?= $page + 1 ?>">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </section>
</div>
</body>
</html>
