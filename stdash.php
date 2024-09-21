<?php
session_start();
require 'db.php'; // Include database connection

// Ensure the student is logged in
if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
    header("Location: login.php");
    exit();
}

// Get the logged-in student's email
$studentEmail = $_SESSION['student_email'];

// Fetch student details, grade, and hobbies
$stmt = $pdo->prepare("
    SELECT s.name, s.email, s.grade, s.photo, g.grade_name, g.grade_club, 
           h1.hobby_name AS hobby1, h1.hobby_club AS club1,
           h2.hobby_name AS hobby2, h2.hobby_club AS club2,
           h3.hobby_name AS hobby3, h3.hobby_club AS club3
    FROM students s
    LEFT JOIN grades g ON s.grade = g.id
    LEFT JOIN hobbies h1 ON s.h1 = h1.id
    LEFT JOIN hobbies h2 ON s.h2 = h2.id
    LEFT JOIN hobbies h3 ON s.h3 = h3.id
    WHERE s.email = :email
");
$stmt->bindParam(':email', $studentEmail);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if student not found
if (!$student) {
    echo "Student not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .dashboard-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        .dashboard-container h2 {
            margin-bottom: 20px;
            color: #0d47a1;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .info-section {
            text-align: left;
            margin-bottom: 20px;
        }

        .info-section strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }

        .club-section {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <img src="<?php echo $student['photo']; ?>" alt="Profile Photo" class="profile-photo">
        <h2>Welcome, <?php echo htmlspecialchars($student['name']); ?>!</h2>

        <div class="info-section">
            <strong>Email:</strong>
            <?php echo htmlspecialchars($student['email']); ?>
        </div>

        <div class="info-section">
            <strong>Grade:</strong>
            <?php echo htmlspecialchars($student['grade_name']); ?>
            <small>(Club: <?php echo htmlspecialchars($student['grade_club']); ?>)</small>
        </div>

        <div class="info-section">
            <strong>Hobbies:</strong>
            <?php if (!empty($student['hobby1'])): ?>
                <div class="club-section"><?php echo htmlspecialchars($student['hobby1']); ?> (Club: <?php echo htmlspecialchars($student['club1']); ?>)</div>
            <?php endif; ?>
            <?php if (!empty($student['hobby2'])): ?>
                <div class="club-section"><?php echo htmlspecialchars($student['hobby2']); ?> (Club: <?php echo htmlspecialchars($student['club2']); ?>)</div>
            <?php endif; ?>
            <?php if (!empty($student['hobby3'])): ?>
                <div class="club-section"><?php echo htmlspecialchars($student['hobby3']); ?> (Club: <?php echo htmlspecialchars($student['club3']); ?>)</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
