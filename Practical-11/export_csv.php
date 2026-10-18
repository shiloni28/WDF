<?php
require_once "db.php";
try {
    $stmt = $conn->query(
        "SELECT id, name, email, course, created_at
         FROM students
         ORDER BY id ASC"
    );

    header("Content-Type: text/csv; charset=UTF-8");
    header(
        'Content-Disposition: attachment; filename="students.csv"'
    );
    $output = fopen("php://output", "w");
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv(
        $output,
        ["ID", "Name", "Email", "Course", "Created At"]
    );
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        foreach ($row as $key => $value) {
            if (
                is_string($value) &&
                preg_match('/^[\s]*[=+\-@]/', $value)
            ) {
                $row[$key] = "'" . $value;
            }
        }

        fputcsv($output, array_values($row));
    }

    fclose($output);
    exit;

} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit("Unable to export student records.");
}
?>
