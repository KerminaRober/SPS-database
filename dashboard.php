<?php
// Include database connection
require_once 'db.php';

// Fetch total students
$total_students_query = $pdo->query("SELECT COUNT(*) as total FROM students");
$total_students = $total_students_query->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch total requests waiting for approval
$total_waiting_query = $pdo->query("SELECT COUNT(*) as total FROM requests");
$total_waiting = $total_waiting_query->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch total hobbies
$total_hobbies_query = $pdo->query("SELECT COUNT(*) as total FROM hobbies");
$total_hobbies = $total_hobbies_query->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch hobbies distribution data
$hobbies_distribution_query = $pdo->query("
    SELECT hobby_name, COUNT(*) as student_count
    FROM (
        SELECT h1 as hobby_id FROM students UNION ALL
        SELECT h2 as hobby_id FROM students UNION ALL
        SELECT h3 as hobby_id FROM students UNION ALL
        SELECT h4 as hobby_id FROM students UNION ALL
        SELECT h5 as hobby_id FROM students UNION ALL
        SELECT h6 as hobby_id FROM students UNION ALL
        SELECT h7 as hobby_id FROM students UNION ALL
        SELECT h8 as hobby_id FROM students UNION ALL
        SELECT h9 as hobby_id FROM students UNION ALL
        SELECT h10 as hobby_id FROM students
    ) AS all_hobbies
    JOIN hobbies ON all_hobbies.hobby_id = hobbies.id
    GROUP BY hobby_name
");
$hobbies_distribution = $hobbies_distribution_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch top hobbies data (optional, for top 5 most popular hobbies)
$top_hobbies_query = $pdo->query("
    SELECT hobby_name, COUNT(*) as student_count
    FROM (
        SELECT h1 as hobby_id FROM students UNION ALL
        SELECT h2 as hobby_id FROM students UNION ALL
        SELECT h3 as hobby_id FROM students UNION ALL
        SELECT h4 as hobby_id FROM students UNION ALL
        SELECT h5 as hobby_id FROM students UNION ALL
        SELECT h6 as hobby_id FROM students UNION ALL
        SELECT h7 as hobby_id FROM students UNION ALL
        SELECT h8 as hobby_id FROM students UNION ALL
        SELECT h9 as hobby_id FROM students UNION ALL
        SELECT h10 as hobby_id FROM students
    ) AS all_hobbies
    JOIN hobbies ON all_hobbies.hobby_id = hobbies.id
    GROUP BY hobby_name
    ORDER BY student_count DESC
    LIMIT 5
");
$top_hobbies = $top_hobbies_query->fetchAll(PDO::FETCH_ASSOC);

// Handle hobby addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hobby'])) {
    $hobby_name = trim($_POST['hobby_name']);
    if (!empty($hobby_name)) {
        $stmt = $pdo->prepare("INSERT INTO hobbies (hobby_name) VALUES (:hobby_name)");
        $stmt->execute(['hobby_name' => $hobby_name]);
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission
        exit;
    }
}

// Handle hobby deletion
if (isset($_GET['delete_hobby'])) {
    $hobby_id = intval($_GET['delete_hobby']);
    $stmt = $pdo->prepare("DELETE FROM hobbies WHERE id = :id");
    $stmt->execute(['id' => $hobby_id]);
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid deletion repeat
    exit;
}

// Fetch all hobbies for the list
$all_hobbies_query = $pdo->query("SELECT * FROM hobbies");
$all_hobbies = $all_hobbies_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

body {
    background-color: #f0f0f5;
    color: #333333;
}

header {
    background-color: #5f9ada;
    color: #ffffff;
    padding: 20px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
}

header img {
    width: 100px; /* Adjust image size for small screens */
    margin: 10px;
}

header h1 {
    font-size: 24px;
    margin: 5px 0;
    text-align: center;
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
    width: 150px;
}

.search-bar button {
    background: none;
    border: none;
    color: #0d47a1;
    cursor: pointer;
    padding-left: 10px;
}

.container {
    display: flex;
    margin-top: 10px;
}

aside {
    background-color: #5f9ada;
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
    background-color: #ff5252;
}

main {
    flex: 1;
    padding: 20px;
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #0d47a1;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.card {
    background-color: #ffffff;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    color: #333333;
}

.card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.card i {
    font-size: 36px;
    margin-bottom: 10px;
}

.card p {
    font-size: 18px;
    font-weight: bold;
}

.total-students {
    background-color: #0d47a1;
    color: #ffffff;
}

.total-waiting {
    background-color: #ff5252;
    color: #ffffff;
}

.total-hobbies {
    background-color: #1565c0;
    color: #ffffff;
}

.pie-chart,
.donut-chart {
    background-color: #ffffff;
}

.top-left-image, .top-right-image {
    position: absolute;
    top: 0;
    width: 150px;
}

.top-left-image {
    left: 0;
}

.top-right-image {
    right: 0;
}

.error-message {
    color: #ff0000;
    margin-top: 10px;
}

.chart-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.chart-container .card {
    flex: 1;
}

#h1 {
    font-size: 35px;
    padding-bottom: 10px;
}

#h1-s {
    padding-bottom: 10px;
}

/* MEDIA QUERIES */
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
}

.add-hobby, .show-hobbies {
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin: 10px;
}

.add-hobby h3, .show-hobbies h3 {
    font-size: 1.5em;
    margin-bottom: 15px;
    color: #333;
}

.add-hobby form, .show-hobbies ul {
    margin: 0;
    padding: 0;
}

.add-hobby input[type="text"] {
    width: calc(100% - 120px);
    padding: 10px;
    margin-right: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.add-hobby button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    background-color: #4285F4;
    color: white;
    cursor: pointer;
}

.add-hobby button:hover {
    background-color: #357ae8;
}

.show-hobbies ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.show-hobbies li {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.show-hobbies a {
    color: #ff4d4d;
    text-decoration: none;
    font-weight: bold;
}

.show-hobbies a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <header>
        <img src="http://future-x.eu.org/uploads/spslogo.png" class="top-left-image" alt="Top Left Image">
        <img src="http://future-x.eu.org/uploads/techdevlogo.png" class="top-right-image" alt="Top Right Image">
        <h1 id="h1">Hobby Connect</h1>
        <h1 id="h1-s">Salam Prep Secondary School (SPS)</h1>
        <h1>Technological Development</h1>
    </header>

    <div class="container">
        <aside>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="manage_requests.php"><i class="fas fa-user-cog"></i> Manage Requests</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <main>
            <h2>Dashboard</h2>
            <div class="dashboard-cards">
                <div class="card total-students">
                    <i class="fas fa-user-graduate"></i>
                    <p>Total Students: <?= $total_students ?></p>
                </div>
                <div class="card total-waiting">
                    <i class="fas fa-user-clock"></i>
                    <p>Students Waiting Management: <?= $total_waiting ?></p>
                </div>
                <div class="card total-hobbies">
                    <i class="fas fa-list"></i>
                    <p>Total Hobbies: <?= $total_hobbies ?></p>
                </div>
                <div class="card pie-chart">
                    <h3>Hobbies Distribution</h3>
                    <canvas id="hobbiesDistributionChart"></canvas>
                </div>
                <div class="card bar-chart">
                    <h3>Top 5 Most Popular Hobbies</h3>
                    <canvas id="topHobbiesChart"></canvas>
                </div>
                <!-- New card for adding hobbies -->
                <div class="card add-hobby">
                    <h3>Add Hobby</h3>
                    <form method="POST">
                        <input type="text" name="hobby_name" placeholder="Enter new hobby" required>
                        <button type="submit" name="add_hobby">Add Hobby</button>
                    </form>
                </div>
                <!-- New card to show all hobbies with delete option -->
                <div class="card show-hobbies">
                    <h3>All Hobbies</h3>
                    <ul>
                        <?php foreach ($all_hobbies as $hobby): ?>
                            <li>
                                <?= htmlspecialchars($hobby['hobby_name']) ?>
                                <a href="?delete_hobby=<?= $hobby['id'] ?>" onclick="return confirm('Are you sure you want to delete this hobby?');">Delete</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Convert PHP data to JavaScript
        const hobbiesDistributionData = {
            labels: <?= json_encode(array_column($hobbies_distribution, 'hobby_name')) ?>,
            datasets: [ {
                data: <?= json_encode(array_column($hobbies_distribution, 'student_count')) ?>,
                backgroundColor: ['#ff9999', '#66b3ff', '#99ff99', '#ffcc99', '#c2c2f0'],
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        };

        const topHobbiesData = {
            labels: <?= json_encode(array_column($top_hobbies, 'hobby_name')) ?>,
            datasets: [ {
                label: 'Top Hobbies',
                data: <?= json_encode(array_column($top_hobbies, 'student_count')) ?>,
                backgroundColor: '#4285F4',
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        };

        // Create charts
        const hobbiesDistributionChart = new Chart(document.getElementById('hobbiesDistributionChart'), {
            type: 'pie',
            data: hobbiesDistributionData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                                const value = tooltipItem.raw;
                                const percentage = ((value / total) * 100).toFixed(2);
                                return tooltipItem.label + ': ' + percentage + '%';
                            }
                        }
                    }
                }
            }
        });

        const topHobbiesChart = new Chart(document.getElementById('topHobbiesChart'), {
            type: 'bar',
            data: topHobbiesData,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: { display: true, text: 'Hobby' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Students' }
                    }
                }
            }
        });
    </script>
</body>
</html>