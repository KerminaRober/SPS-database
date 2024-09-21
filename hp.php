<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash a Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f0f0f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }

        .hash-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .hash-container h2 {
            margin-bottom: 20px;
            color: #0d47a1;
        }

        .hash-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .hash-container button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #0d47a1;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        .hash-container button:hover {
            background-color: #1e3a6e;
        }

        .hashed-output {
            background-color: #e0e0e0;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="hash-container">
        <h2>Hash a Password</h2>
        <form method="POST" action="">
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Hash Password</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the password from the form
            $password = $_POST['password'];

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Display the hashed password
            echo "<div class='hashed-output'>Hashed Password: <br>" . htmlspecialchars($hashed_password) . "</div>";
        }
        ?>
    </div>
</body>
</html>
