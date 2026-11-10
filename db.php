<?php

$host = "localhost";
$port = "5432";
$dbname = "student_management";
$user = "postgres";
$password = "otieno2002";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conn->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>