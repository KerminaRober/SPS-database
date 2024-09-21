<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];

    // Prepare the delete query
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = :student_id");
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Student deleted successfully.";
    } else {
        $_SESSION['flash_message'] = "Failed to delete student.";
    }

    // Redirect back to the students page
    header("Location: students.php");
    exit();
}
?>
