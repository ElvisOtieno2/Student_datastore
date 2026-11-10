
<?php

require_once 'db.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid student ID.");
}

$sql = "DELETE FROM students WHERE id = :id";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

header("Location: index.php");
exit;

?>
