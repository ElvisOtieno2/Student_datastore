<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/db.php';
$message = "";

// Register student
if ($_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['register_student'])) {

    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);

    if ($name !== "" && filter_var($email, FILTER_VALIDATE_EMAIL)
        && $course !== "") {

        $sql = "INSERT INTO students
                (full_name, email, course)
                VALUES (:name, :email, :course)";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':course' => $course
        ]);

        header("Location: index.php?success=registered");
        exit;
    } else {
        $message = "Please enter valid student details.";
    }
}

// Success message
if (isset($_GET['success'])) {
    $message = "Student registered successfully!";
}

if (isset($_GET['updated'])) {
    $message = "Student updated successfully!";
}

if (isset($_GET['deleted'])) {
    $message = "Student deleted successfully!";
}

// Search students
$search = trim($_GET['search'] ?? '');

if ($search !== "") {

    $sql = "SELECT * FROM students
            WHERE full_name ILIKE :search
            OR email ILIKE :search
            OR course ILIKE :search
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':search' => "%$search%"
    ]);

} else {

    $sql = "SELECT * FROM students ORDER BY id DESC";
    $stmt = $conn->query($sql);
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total students
$totalStudents = $conn->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

// Escape output
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// Get student distribution by course
$courseQuery = "
    SELECT course, COUNT(*) AS total_students
    FROM students
    GROUP BY course
    ORDER BY total_students DESC
";

$courseStmt = $conn->query($courseQuery);
$courseStats = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

// Count distinct courses
$totalCourses = count($courseStats);

// Get the five most recently registered students
$recentQuery = "
    SELECT id, full_name, email, course, created_at
    FROM students
    ORDER BY created_at DESC
    LIMIT 5
";

$recentStmt = $conn->query($recentQuery);
$recentStudents = $recentStmt->fetchAll(PDO::FETCH_ASSOC);


// Get students grouped by course
$courseQuery = "
    SELECT course, COUNT(*) AS total_students
    FROM students
    GROUP BY course
    ORDER BY total_students DESC
";

$courseStmt = $conn->query($courseQuery);

$courseStats = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

// Count distinct courses
$totalCourses = count($courseStats);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management Dashboard</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="app-shell">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="brand">
            <h2>SMS</h2>
            <p>Student Manager</p>
        </div>

        <nav>
            <a href="#dashboard" class="active">Dashboard</a>
            <a href="#registration">Register Student</a>
            <a href="#records">Student Records</a>
        </nav>

        <div class="sidebar-bottom">
            <p>Administrator</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar" id="dashboard">

            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= e($_SESSION['admin_username']) ?>.</p>
            </div>

            <span class="admin-label">Administrator</span>

        </header>

        <?php if ($message): ?>
            <p class="success"><?= e($message) ?></p>
        <?php endif; ?>

        <!-- Statistics -->
        <section class="stats">

            <div class="stat-card">
                <div>
                    <p>Total Registered Students</p>
                    <h2><?= e($totalStudents) ?></h2>
                </div>

                <div class="stat-icon">👨‍🎓</div>
            </div>

        </section>

        <div class="stat-card">
    <div>
        <p>Total Courses</p>
        <h2><?= e($totalCourses) ?></h2>
    </div>

    <div class="stat-icon">📚</div>
</div>

<section class="panel">

    <div class="section-heading">
        <h2>Students Per Course</h2>
        <p>Student enrolment distribution.</p>
    </div>

    <section class="panel">
    <div class="section-heading">
        <h2>Course Distribution Chart</h2>
        <p>Visual representation of student enrolment per course.</p>
    </div>

    <div style="max-width: 700px; margin: 20px auto;">
        <canvas id="courseChart"></canvas>
    </div>
</section>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Total Students</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($courseStats as $course): ?>
                    <tr>
                        <td><?= e($course['course']) ?></td>
                        <td><?= e($course['total_students']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>

<section class="panel">
    <div class="section-heading">
        <h2>Recent Registrations</h2>
        <p>The five latest students registered in the system.</p>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Date Registered</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($recentStudents)): ?>
                    <tr>
                        <td colspan="4">No students registered yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentStudents as $student): ?>
                        <tr>
                            <td><?= e($student['full_name']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['course']) ?></td>
                            <td>
                                <?= e(date('d M Y', strtotime($student['created_at']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

        <!-- Registration Form -->
        <section class="panel registration" id="registration">

            <div class="section-heading">
                <div>
                    <h2>Register New Student</h2>
                    <p>Add a new student to the system.</p>
                </div>
            </div>

            <form method="POST" class="student-form">

                <input
                    type="text"
                    name="full_name"
                    placeholder="Full Name"
                    required
                >

                <input
                    type="email"
                    name="email"
                    placeholder="Email Address"
                    required
                >

                <input
                    type="text"
                    name="course"
                    placeholder="Course"
                    required
                >

                <button type="submit" name="register_student">
                    Register Student
                </button>

            </form>

        </section>

        <!-- Student Records -->
        <section class="panel records" id="records">

            <div class="section-heading">
                <div>
                    <h2>Student Records</h2>
                    <p>View and manage registered students.</p>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name, email or course..."
                    value="<?= e($search) ?>"
                >

                <button type="submit">Search</button>

                <a href="index.php" class="clear-btn">Clear</a>

            </form>

            <!-- Records Table -->
            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Date Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($students) > 0): ?>

                            <?php foreach ($students as $student): ?>

                                <tr>

                                    <td><?= e($student['id']) ?></td>
                                    <td><?= e($student['full_name']) ?></td>
                                    <td><?= e($student['email']) ?></td>
                                    <td><?= e($student['course']) ?></td>
                                    <td><?= e($student['created_at']) ?></td>

                                    <td class="actions">

                                        <a
                                            href="edit.php?id=<?= e($student['id']) ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="delete.php"
                                            onsubmit="return confirm('Are you sure you want to delete this student?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= e($student['id']) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="empty-state">
                                    No students found.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <footer class="footer">
            Student Management System &copy; <?= date('Y') ?>
        </footer>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const courseLabels = <?= json_encode(array_column($courseStats, 'course')) ?>;

const studentTotals = <?= json_encode(
    array_map('intval', array_column($courseStats, 'total_students'))
) ?>;

const ctx = document.getElementById('courseChart');

new Chart(ctx, {
    type: 'bar',

    data: {
        labels: courseLabels,

        datasets: [{
            label: 'Number of Students',
            data: studentTotals,
            backgroundColor: '#003366',
            borderRadius: 6
        }]
    },

    options: {
        responsive: true,

        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                },
                title: {
                    display: true,
                    text: 'Number of Students'
                }
            },

            x: {
                title: {
                    display: true,
                    text: 'Courses'
                }
            }
        }
    }
});
</script>

</body>
</html>
