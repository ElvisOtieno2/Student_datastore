
<?php
require_once 'db.php';

// Get student ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid student ID.");
}

// Fetch student
$stmt = $conn->prepare(
    "SELECT * FROM students WHERE id = :id"
);

$stmt->execute([':id' => $id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found.");
}

$message = "";

// Update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);

    if ($name !== "" && filter_var($email, FILTER_VALIDATE_EMAIL)
        && $course !== "") {

        $sql = "UPDATE students
                SET full_name = :name,
                    email = :email,
                    course = :course
                WHERE id = :id";

        $update = $conn->prepare($sql);

        $update->execute([
            ':name' => $name,
            ':email' => $email,
            ':course' => $course,
            ':id' => $id
        ]);

        header("Location: index.php");
        exit;

    } else {
        $message = "Please enter valid details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>Edit Student Details</h1>

    <p style="color:red;">
        <?= htmlspecialchars($message) ?>
    </p>

    <form method="POST">

        <label>Full Name</label>

        <input type="text"
               name="full_name"
               value="<?= htmlspecialchars($student['full_name']) ?>"
               required>

        <label>Email Address</label>

        <input type="email"
               name="email"
               value="<?= htmlspecialchars($student['email']) ?>"
               required>

        <label>Course</label>

        <input type="text"
               name="course"
               value="<?= htmlspecialchars($student['course']) ?>"
               required>

        <button type="submit">Update Student</button>

    </form>

    <p>
        <a href="index.php">Back to Dashboard</a>
    </p>

</div>

</body>
</html>
