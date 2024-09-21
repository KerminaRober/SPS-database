<?php
session_start();
include 'db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $delete_query->bindParam(':id', $delete_id);
    $delete_query->execute();
    header("Location: students.php");
    exit();
}
// Fetch all grades for the filter
$grades_query = $pdo->query("SELECT id, grade_name FROM grades");
$grades = $grades_query->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch filters
$hobby_filter = $_GET['hobby'] ?? '';
$grade_filter = $_GET['grade'] ?? '';

// Prepare the query to fetch students
$query = "SELECT students.*, grades.grade_name AS grade_name FROM students 
          LEFT JOIN grades ON students.grade = grades.id WHERE 1";

if ($grade_filter) {
    $query .= " AND students.grade = :grade_filter";
}

if ($hobby_filter) {
    $query .= " AND (h1 = :hobby_filter OR h2 = :hobby_filter OR h3 = :hobby_filter OR h4 = :hobby_filter OR h5 = :hobby_filter OR h6 = :hobby_filter OR h7 = :hobby_filter OR h8 = :hobby_filter OR h9 = :hobby_filter OR h10 = :hobby_filter)";
}

$query .= " ORDER BY FIELD(students.grade, '1st primary', '2nd primary', '3rd primary', '4th primary', '5th primary', '6th primary', '1st preparatory', '2nd preparatory', '3rd preparatory', '1st secondary', '2nd secondary', '3rd secondary') ASC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);

if ($grade_filter) {
    $stmt->bindParam(':grade_filter', $grade_filter);
}

if ($hobby_filter) {
    $stmt->bindParam(':hobby_filter', $hobby_filter);
}

$stmt->execute();
$students = $stmt->fetchAll();

$hobbies_query = $pdo->query("SELECT id, hobby_name FROM hobbies");
$hobbies_list = $hobbies_query->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}

body {
    background-color: #f0f0f5; /* Very light gray-blue */
    color: #333333; /* Dark gray for text */
}

header {
    background-color: #5f9ada;
    color: #ffffff;
    padding: 20px 40px; /* Adjusted padding for better spacing */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
}

header img {
    width: 120px; /* Adjust image size for better balance */
    margin: 10px; /* Add margin around images */
}

header h1 {
    font-size: 28px; /* Adjusted font size */
    margin: 5px 0; /* Add margin for spacing between headings */
    text-align: center;
}

header h2 {
    font-size: 20px; /* Optional: add an additional heading level */
    margin: 5px 0; /* Add margin for spacing */
}

nav ul {
    list-style-type: none;
    display: flex;
    margin-top: 10px;
}

nav ul li {
    margin: 10px 15px;
}

nav ul li a {
    text-decoration: none;
    color: #ffffff;
    font-weight: bold;
    transition: color 0.3s ease;
}

nav ul li a:hover {
    color: #ff6f61; /* Soft red on hover */
}

.search-bar {
    display: flex;
    background-color: #ffffff;
    border-radius: 5px;
    padding: 5px;
    margin-top: 10px;
}

.search-bar input {
    border: none;
    outline: none;
    padding: 8px;
    border-radius: 5px;
    width: 200px;
}

.search-bar button {
    background: none;
    border: none;
    color: #0d47a1; /* Dark blue */
    cursor: pointer;
    padding-left: 10px;
}

.container {
    display: flex;
    margin-top: 10px;
}

aside {
    background-color: #5f9ada; /* Medium blue */
    width: 250px;
    padding: 30px 20px;
    height: 100vh;
    color: #ffffff;
}

aside ul {
    list-style-type: none;
}

aside ul li {
    margin-bottom: 20px;
}

aside ul li a {
    text-decoration: none;
    color: #ffffff;
    font-size: 18px;
    display: flex;
    align-items: center;
    padding: 10px 5px;
    border-radius: 5px;
    transition: background 0.3s ease;
}

aside ul li a i {
    margin-right: 10px;
}

aside ul li a:hover,
aside ul li a.active {
    background-color: #ff5252; /* Bright red */
}

main {
    flex: 1;
    padding: 20px;
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #0d47a1; /* Dark blue */
}
.top-left-image, .top-right-image {
            position: absolute;
            top: 0;
            width: 150px; /* Adjust the size */
        }

        .top-left-image {
            left: 0;
        }

        .top-right-image {
            right: 0;
        }
        #h1{
            font-size: 35px;
            padding-bottom: 10px;
        }
	.delete-button {
    background-color: #ff5252; /* Bright red for delete action */
    color: #ffffff; /* White text color */
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
    display: inline-block;
}

.delete-button:hover {
    background-color: #d32f2f; /* Darker red on hover */
    transform: scale(1.05); /* Slightly enlarge on hover */
}

.delete-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 82, 82, 0.5); /* Outline for accessibility */
}

	.content {
    flex: 1;
    padding: 20px;
    background-color: #ffffff; /* White background for main content */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin: 0 auto; /* Center horizontally */
    max-width: 1200px; /* Max width for better alignment */
}

        main {
    flex: 1;
    padding: 20px;
    background-color: #ffffff; /* White background for main content */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    margin-left: 20px;
    margin-right: 20px;
}

.form-container {
    margin-bottom: 20px;
}

.input-container {
    margin-bottom: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

.submit {
    background-color: #0d47a1; /* Dark blue */
    width: 100%;
    color: #ffffff;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
    margin-bottom: 20px;
}

.submit:hover {
    background-color: #0056a1; /* Darker blue for hover effect */
}

.grade-heading {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #0d47a1; /* Dark blue */
    text-align: center;
    border-top: 2px solid #0056a1;
}

.grade-section {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.student-card {
    background-color: #ffffff; /* White background for student cards */
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    width: 100%;
    max-width: 300px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Light shadow */
    text-align: center;
}

.student-card h3 {
    margin-bottom: 10px;
    font-size: 20px;
    color: #333333; /* Dark gray for text */
}

.student-card p {
    margin-bottom: 10px;
    color: #666666; /* Light gray for text */
}

.student-card img {
    width: 100%;
    height: auto;
    border-radius: 50%;
    margin-top: 10px;
}

#h1-s{
            padding-bottom: 10px;
        }
        @media (max-width: 768px) {
    header {
        padding: 15px 20px;
    }

    header img {
        display: none;
    }

    header h1 {
        font-size: 20px;
    }

    aside {
        width: 250px;
        padding: 20px;
    }

    .dashboard-cards {
        grid-template-columns: 1fr; /* Stack cards on top of each other */
        gap: 10px;
    }

    .card {
        padding: 15px;
    }

    main {
        padding: 10px;
    }

    h2 {
        font-size: 22px;
    }

    .login-container {
        width: 90%; /* Reduce login container width */
        padding: 15px;
    }

    .login-container input, .login-container button {
        padding: 8px;
    }

    .chart-container {
        gap: 15px;
    }
}

@media (max-width: 480px) {
    header {
        padding: 10px;
    }

    header img {
        display: none;
    }

    header h1 {
        font-size: 18px;
    }

    aside {
        width: 170px;
        padding: 15px;
    }

    .card {
        padding: 10px;
    }

    h2 {
        font-size: 18px;
    }

    .login-container {
        width: 80%; /* Take full screen width on small devices */
        padding: 10px;
        height: 70vh;
    }

    .login-container input, .login-container button {
        padding: 6px;
    }
}
.student-photo {
    width: 100px; /* or your desired width */
    height: 100px; /* or your desired height */
    object-fit: cover; /* Ensures the image covers the container */
}

    </style>
</head>
<body>
    <header>
        <img src="http://future-x.eu.org/uploads/spslogo.png" class="top-left-image" alt="Top Left Image">
        <img src="http://future-x.eu.org/uploads/techdevlogo.png" class="top-right-image" alt="Top Right Image">
        <h1>Hobby Connect - SPS</h1>
        <h2>View Students</h2>
        <div class="search-bar">
            <input type="text" placeholder="Search by name" id="searchBar" oninput="filterStudents()">
            <button><i class="fas fa-search"></i></button>
        </div>
    </header>
    
    <div class="container">
        <aside>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="students.php" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="manage_requests.php"><i class="fas fa-user-cog"></i> Manage Requests</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <div class="content">
            <h2>Student List</h2>
            <form method="get" action="" class="form-container">
              <div class="input-container">
    <label for="grade">Filter by Grade:</label>
    <select name="grade" id="grade">
        <option value="">All Grades</option>
        <?php foreach ($grades as $id => $grade_name): ?>
            <option value="<?php echo htmlspecialchars($id); ?>" <?php if ($grade_filter == $id) echo 'selected'; ?>>
                <?php echo htmlspecialchars($grade_name); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                <div class="input-container">
                    <label for="hobby">Filter by Hobby:</label>
                    <select name="hobby" id="hobby">
                        <option value="">All Hobbies</option>
                        <!-- Dynamically populate the hobby filter options -->
                        <?php foreach ($hobbies_list as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php if ($hobby_filter == $id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="submit">Filter</button>
            </form>

            <?php if ($students): ?>
                <div class="grade-section">
                    <?php foreach ($students as $student): ?>
                        <div class="student-card">
                            <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo" class="student-photo">
                            <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                            <p><strong>Grade:</strong> <?php echo htmlspecialchars($student['grade_name']); ?></p>
                            <p><strong>National ID:</strong> <?php echo htmlspecialchars($student['national_id']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                            <p><strong>Hobbies:</strong>
                                <?php
                                $hobbies = array_filter([
                                    $hobbies_list[$student['h1']] ?? null,
                                    $hobbies_list[$student['h2']] ?? null,
                                    $hobbies_list[$student['h3']] ?? null,
                                    $hobbies_list[$student['h4']] ?? null,
                                    $hobbies_list[$student['h5']] ?? null,
                                    $hobbies_list[$student['h6']] ?? null,
                                    $hobbies_list[$student['h7']] ?? null,
                                    $hobbies_list[$student['h8']] ?? null,
                                    $hobbies_list[$student['h9']] ?? null,
                                    $hobbies_list[$student['h10']] ?? null
                                ]);
                                echo htmlspecialchars(implode(', ', $hobbies));
                                ?>
                            </p>
                            <p><strong>Certificates:</strong>
                                <?php
                                $certificates = explode(',', $student['certificates']);
                                foreach ($certificates as $cert) {
                                    echo '<a href="' . htmlspecialchars($cert) . '" target="_blank">Certificate</a><br>';
                                }
                                ?>
                            </p>
                            <form method="get" action="">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No students found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterStudents() {
            const searchBar = document.getElementById('searchBar');
            const studentCards = document.querySelectorAll('.student-card');
            const searchValue = searchBar.value.toLowerCase();

            studentCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                if (name.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>