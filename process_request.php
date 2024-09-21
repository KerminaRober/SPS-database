<?php
// Database connection parameters
require 'db.php'; // Load database connection settings from db.php

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $Email = $_POST['Email'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $national_id = $_POST['national_id'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $hobbies = $_POST['hobbies'] ?? '';

    // Validate required fields
    if (empty($name) || empty($grade) || empty($national_id) || empty($phone)) {
        die("Please fill all required fields.");
    }

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoFileName = basename($_FILES['photo']['name']);
        $photoTempName = $_FILES['photo']['tmp_name'];
        $photoUploadPath = 'uploads/photos/' . $photoFileName;

        if (!move_uploaded_file($photoTempName, $photoUploadPath)) {
            die("Failed to upload photo.");
        }
    } else {
        die("Photo is required.");
    }

    // Handle certificates upload
    $certificatePaths = [];
    if (isset($_FILES['certificates']) && !empty($_FILES['certificates']['name'][0])) {
        $certificates = $_FILES['certificates'];
        foreach ($certificates['tmp_name'] as $key => $tmpName) {
            $fileName = basename($certificates['name'][$key]);
            $fileTmpName = $certificates['tmp_name'][$key];
            $fileUploadPath = 'uploads/certificates/' . $fileName;

            if (move_uploaded_file($fileTmpName, $fileUploadPath)) {
                $certificatePaths[] = $fileUploadPath;
            }
        }
    }

    // Prepare SQL statement
    $sql = "INSERT INTO requests (name, Email, grade, national_id, phone, hobbies, photo, certificates) VALUES (:name, :Email, :grade, :national_id, :phone, :hobbies, :photo, :certificates)";
    $stmt = $pdo->prepare($sql);

    // Prepare the certificates string
    $certificatesString = implode(',', $certificatePaths);

    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':Email', $Email);
    $stmt->bindParam(':grade', $grade);
    $stmt->bindParam(':national_id', $national_id);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':hobbies', $hobbies);
    $stmt->bindParam(':photo', $photoUploadPath);
    $stmt->bindParam(':certificates', $certificatesString);

    // Execute statement
    if ($stmt->execute()) {
        header("Location: success.html"); // Redirect to success page
        exit;
    } else {
        echo "Failed to submit request.";
    }
} else {
    echo "";
}
?>
