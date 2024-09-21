<?php
// Start the session to store the password temporarily
session_start();

// Database connection
require 'db.php';

// Fetch requests from the database
$sql = "SELECT * FROM requests";
$stmt = $pdo->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch hobbies for dropdown options
$sqlHobbies = "SELECT * FROM hobbies";
$stmtHobbies = $pdo->query($sqlHobbies);
$hobbies = $stmtHobbies->fetchAll(PDO::FETCH_ASSOC);

// Generate a random password
function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

// Handle approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $requestId = intval($_POST['request_id']);

    // Fetch the request details
    $sql = "SELECT * FROM requests WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle hobbies
    $selectedHobbies = isset($_POST['hobbies']) ? $_POST['hobbies'] : [];
    
    // Fetch hobby IDs from the hobbies table
    $hobbyIds = [];
    foreach ($hobbies as $hobby) {
        $hobbyIds[$hobby['hobby_name']] = $hobby['id'];
    }

    // Prepare hobby data for student insertion
    $hobbyData = [];
    for ($i = 0; $i < 10; $i++) {
        $hobbyData[$i] = isset($selectedHobbies[$i]) ? $hobbyIds[$selectedHobbies[$i]] ?? null : null;
    }

    // Generate a random password
    $randomPassword = generateRandomPassword();

    // Store the plain password in the session to display it later
    $_SESSION['plain_password'] = $randomPassword;

    // Prepare SQL to insert into students table
    $sqlInsertStudent = "INSERT INTO students 
        (name, password, Email, grade, national_id, phone, h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, photo, certificates, submission_date)
        VALUES (:name, :password, :Email, :grade, :national_id, :phone, :h1, :h2, :h3, :h4, :h5, :h6, :h7, :h8, :h9, :h10, :photo, :certificates, :submission_date)";
    
    $stmtInsert = $pdo->prepare($sqlInsertStudent);

    $stmtInsert->execute([
        'name' => $request['name'],
        'password' => password_hash($randomPassword, PASSWORD_DEFAULT),
        'Email' => $request['Email'],
        'grade' => $request['grade'],
        'national_id' => $request['national_id'],
        'phone' => $request['phone'],
        'h1' => $hobbyData[0],
        'h2' => $hobbyData[1],
        'h3' => $hobbyData[2],
        'h4' => $hobbyData[3],
        'h5' => $hobbyData[4],
        'h6' => $hobbyData[5],
        'h7' => $hobbyData[6],
        'h8' => $hobbyData[7],
        'h9' => $hobbyData[8],
        'h10' => $hobbyData[9],
        'photo' => $request['photo'],
        'certificates' => $request['certificates'],
        'submission_date' => $request['submission_date'],
    ]);

    // Delete request from requests table
    $sqlDelete = "DELETE FROM requests WHERE id = :id";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute(['id' => $requestId]);

    // Redirect to the same page to show success message
    header('Location: manage_requests.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database - Manage Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css"> <!-- Your CSS file -->
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
            overflow-x: hidden;
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
            color: #ff6f61;
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
            color: #0d47a1;
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

        .content {
            width: 80%;
            padding: 30px;
        }

        .input-container select:focus {
            border-color: #0d47a1;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        .submit {
            display: block;
            padding: 0.75rem 1.25rem;
            background-color: #0d47a1;
            color: #ffffff;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 500;
            width: 100%;
            border-radius: 0.5rem;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit:hover {
            background-color: #0b377b;
        }
        .request-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            width: 100%;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: left;
        }
        .request-card img {
            width: 100px;
            height: auto;
            border-radius: 10px;
        }
        .request-card h3 {
            margin: 5px 0;
            color: #0d47a1;
        }
        .request-card p {
            margin: 5px 0;
        }
        .grade-heading {
            font-size: 1.5em;
            margin-top: 20px;
            border-top: 2px solid #0b377b;
            padding-top: 10px;
            color: #0b377b;
        }
        .success-message {
            color: #10B981; /* Green color for success */
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        .hidden {
            display: none;
        }
        .button-container {
            margin-top: 1rem;
        }
        .hobby-dropdown-container {
            margin-bottom: 1rem;
            position: relative;
        }
        .hobby-dropdown {
            background-color: #fff;
            padding: 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .hobby-dropdown:focus {
            border-color: #0d47a1;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        .add-hobby-button {
            background-color: #0d47a1;
            color: #fff;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
            line-height: 1.25rem;
            text-transform: uppercase;
            transition: background-color 0.3s;
            margin-top: 0.5rem;
        }
        .add-hobby-button:hover {
            background-color: #0b377b;
        }

        .top-left-image,
        .top-right-image {
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

        #h1 {
            font-size: 35px;
            padding-bottom: 10px;
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
    </style>
</head>
<body>
    <header>
        <img src="http://future-x.eu.org/uploads/spslogo.png" class="top-left-image" alt="Top Left Image">
        <img src="http://future-x.eu.org/uploads/techdevlogo.png" class="top-right-image" alt="Top Right Image">
        <h1 id="h1">Hobby Connect</h1>
        <h1 id="h1-s">Salam Prep Secondary School (SPS)</h1>
        <h1>Technological Development</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search by name" id="searchBar" oninput="filterStudents()">
            <button><i class="fas fa-search"></i></button>
        </div>
    </header>
    <div class="container">
  <aside>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="students.php" ><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="manage_requests.php" class="active"><i class="fas fa-user-cog"></i> Manage Requests</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>
        <div class="content">
            <h2>Manage Requests</h2>
            <?php if (isset($_GET['success']) && isset($_SESSION['plain_password'])): ?>
    <div class="success-message">
        Request approved successfully!<br>
        <strong>Password:</strong> <?php echo htmlspecialchars($_SESSION['plain_password']); ?>
    </div>
    <?php unset($_SESSION['plain_password']); // Clear the password from session ?>
<?php endif; ?>

            <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <h3><?php echo htmlspecialchars($request['name']); ?></h3>
                    <p><strong>Grade:</strong> <?php echo htmlspecialchars($request['grade']); ?></p>
                    <p><strong>Submitted At:</strong> <?php echo htmlspecialchars($request['submission_date']); ?></p>
                    <p><strong>Hobbies:</strong> <?php echo htmlspecialchars($request['hobbies']); ?></p>
                    <img src="<?php echo htmlspecialchars($request['photo']); ?>" alt="Student Photo">
                    <form method="post" action="">
                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                        <div id="hobby-selection-container-<?php echo htmlspecialchars($request['id']); ?>" class="form-container"></div>
                        <div class="button-container">
                            <button type="submit" name="approve" class="submit">Approve Request</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hobbies = <?php echo json_encode($hobbies); ?>;
            const hobbyCount = 10;

            document.querySelectorAll('form').forEach(form => {
                const requestId = form.querySelector('input[name="request_id"]').value;
                const container = document.getElementById('hobby-selection-container-' + requestId);

                const selectedHobbies = new Set(); // To track selected hobbies

                function renderHobbyDropdown(index) {
                    if (index >= hobbyCount) return;

                    const dropdownContainer = document.createElement('div');
                    dropdownContainer.classList.add('hobby-dropdown-container');

                    const select = document.createElement('select');
                    select.name = 'hobbies[]';
                    select.id = 'hobby-' + (index + 1);
                    select.classList.add('hobby-dropdown');
                    select.innerHTML = '<option value="" disabled selected>Select hobby ' + (index + 1) + '</option>';

                    hobbies.forEach(hobby => {
                        if (!selectedHobbies.has(hobby.hobby_name)) { // Check if hobby is not already selected
                            const option = document.createElement('option');
                            option.value = hobby.hobby_name;
                            option.textContent = hobby.hobby_name;
                            select.appendChild(option);
                        }
                    });

                    dropdownContainer.appendChild(select);

                    if (index < hobbyCount - 1) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = 'Add Hobby ' + (index + 2);
                        button.classList.add('add-hobby-button');
                        button.onclick = () => {
                            if (select.value !== "") {
                                selectedHobbies.add(select.value); // Add selected hobby to set only if a hobby is chosen
                                button.classList.add('hidden');
                                renderHobbyDropdown(index + 1);
                            } else {
                                alert("Please select a hobby before adding another.");
                            }
                        };
                        dropdownContainer.appendChild(button);
                    }

                    container.appendChild(dropdownContainer);
                }

                renderHobbyDropdown(0);
            });
        });

        // Function to filter students based on the search input
        function filterStudents() {
            const searchValue = document.getElementById('searchBar').value.toLowerCase();
            const requestCards = document.querySelectorAll('.request-card');

            requestCards.forEach(card => {
                const studentName = card.querySelector('h3').textContent.toLowerCase(); // Get the student's name from the <h3>
                if (studentName.includes(searchValue)) {
                    card.style.display = 'block';  // Show the card if it matches the search
                } else {
                    card.style.display = 'none';  // Hide the card if it doesn't match
                }
            });
        }
    </script>
</body>
</html>
