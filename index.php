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
        header("Location: success.php"); // Redirect to success page
        exit;
    } else {
        echo "Failed to submit request.";
    }
} else {
    echo "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Request</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Montserrat');
 
 /* Basic reset */
 * {
     margin: 0;
     padding: 0;
 }
 
 html {
     
     background: #b3d3dd; /* Fallback for old browsers */
     background: -webkit-linear-gradient(to left, #1582db, #073655); /* Chrome 10-25, Safari 5.1-6 */
 }
 
 body {
     font-family: Montserrat, Arial, Verdana;
     background: transparent;
 }
 
 /* Form styles */
 #msform {
     text-align: center;
     position: relative;
     margin-top: 30px;
     margin-bottom: 30px;
 }
 
 #msform fieldset {
     background: #f3f3f3;
     border: 0 none;
     border-radius: 10px;
     box-shadow: 0 0 15px 1px rgba(0, 0, 0, 0.4);
     padding: 20px 30px;
     box-sizing: border-box;
     width: 80%;
     margin: 30px 10%;
     position: relative;
 }
 
 /* Hide all except the first fieldset */
 #msform fieldset:not(:first-of-type) {
     display: none;
 }
 
 /* Inputs */
 #msform input, #msform textarea {
     padding: 15px;
     border: 1px solid #ccc;
     border-radius: 0px;
     margin-bottom: 10px;
     width: 100%;
     box-sizing: border-box;
     font-family: Montserrat;
     color: #2C3E50;
     font-size: 13px;
 }
 
 #msform input:focus, #msform textarea:focus {
     border: 1px solid #086a91;
     outline-width: 0;
     transition: All 0.5s ease-in;
 }
 
 /* Buttons */
 #msform .action-button {
     width: 100px;
     background: #086a91;
     font-weight: bold;
     color: white;
     border: 0 none;
     border-radius: 25px;
     cursor: pointer;
     padding: 10px 5px;
     margin: 10px 5px;
 }
 
 #msform .action-button:hover, #msform .action-button:focus {
     box-shadow: 0 0 0 2px white, 0 0 0 3px #086a91;
 }
 
 #msform .action-button-previous {
     width: 100px;
     background: #C5C5F1;
     font-weight: bold;
     color: white;
     border: 0 none;
     border-radius: 25px;
     cursor: pointer;
     padding: 10px 5px;
     margin: 10px 5px;
 }
 
 #msform .action-button-previous:hover, #msform .action-button-previous:focus {
     box-shadow: 0 0 0 2px white, 0 0 0 3px #C5C5F1;
 }
 
 /* Headings */
 .fs-title {
     font-size: 18px;
     text-transform: uppercase;
     color: #2C3E50;
     margin-bottom: 10px;
     letter-spacing: 2px;
     font-weight: bold;
 }
 
 .fs-subtitle {
     font-weight: normal;
     font-size: 13px;
     color: #666;
     margin-bottom: 20px;
 }
 /* Progressbar */
#progressbar {
    overflow: hidden;
    counter-reset: step;
    position: relative;
    z-index: 10; /* Ensure it stays above other content */
}

#progressbar li {

    list-style-type: none;
    color: white;
    text-transform: uppercase;
    font-size: 9px;
    width: 33.33%;
    float: left;
    position: relative;
    letter-spacing: 1px;
}

#progressbar li:before {
    content: counter(step);
    counter-increment: step;
    width: 24px;
    height: 24px;
    line-height: 26px;
    display: block;
    font-size: 12px;
    color: #333;
    background: white;
    border-radius: 25px;
    margin: 0 auto 10px auto;
}

#progressbar li:after {
    content: '';
    width: 100%;
    height: 2px;
    background: white;
    position: absolute;
    left: -50%;
    top: 9px;
    z-index: -1;
}

#progressbar li:first-child:after {
    content: none;
}

#progressbar li.active:before, #progressbar li.active:after {
    background: #086a91;
    color: white;
}

/* Adjust form container position if necessary */
#msform {
    text-align: center;
    position: relative;
}

 
 .input-container {
     position: relative;
     margin-bottom: 1.5rem;
 }
 
 .input-container input,
 .input-container select,
 .input-container textarea {
     width: 100%;
     padding: 0.75rem 1rem;
     font-size: 1rem;
     line-height: 1.5rem;
     border-radius: 10px !important;
     border: 1px solid #d1d5db;
     box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
     outline: none;
     transition: border-color 0.3s, box-shadow 0.3s;
     box-sizing: border-box;
     background: #f3f3f3;
 }
 
 .input-container input:focus,
 .input-container select:focus,
 .input-container textarea:focus {
     border-color: #4F46E5;
     box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
 }
 
 .input-container select {
     appearance: none;
     background: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="gray"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>') no-repeat right 1rem center;
     background-color: #f3f3f3;
     cursor: pointer;
 }
 
 .input-container textarea {
     resize: vertical;
     min-height: 100px;
     border-radius: 5px;
 }
 
 .input-container input[type="file"] {
     padding: 0;
     font-size: 0.875rem;
     line-height: 1.25rem;
     cursor: pointer;
     margin-top: 0.5rem;
 }
 
 .input-container label {
     display: block;
     text-align: left;
     font-size: 0.875rem;
     color: #6b7280;
     margin-bottom: 0.5rem;
 }
         /* Additional styles for photo preview overlay and modal */
         #multiple-photo-preview img {
             max-width: 100px;
             max-height: 100px;
             margin: 5px;
             cursor: pointer; /* Add pointer cursor for clickable images */
         }
 
         /* Modal Container */
         .modal-container {
             position: fixed;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             background: black;
             color: white;
             padding: 20px;
             border-radius: 10px;
             display: none;
             z-index: 1000;
             max-width: 90%;
             max-height: 90%;
             overflow-y: auto;
         }
 
         /* Modal Content */
         .modal-content {
             display: flex;
             flex-wrap: wrap;
         }
 
         /* Close Button */
         .modal-close {
             position: absolute;
             top: 10px;
             right: 10px;
             cursor: pointer;
             font-size: 20px;
             color: white;
         }
 
 .check-box{
     width: 50px;
     height: 50px;
     border-radius: 40px;
     box-shadow: 0 0 12px -2px rgba(0,0,0,0.5);
     position: absolute;
     top: 0;
     right: -40px;
     opacity: 0;
 }
 
 .check-box svg{
     width: 40px;
     margin: 5px;
 }
 
 svg path{
     stroke-width: 3;
     stroke: #fff;
     stroke-dasharray: 34;
     stroke-dashoffset: 34;
     stroke-linecap: round;
 }
 
 .active1 {
     background: #2cd687;
     transition: 1s;
 }
 
 .active1 .check-box {
     right: 0;
     opacity: 1;
     transition: 1s;
 }
 
 .active1 p {
     margin-right: 125px;
     transition: 1s;
 }
 
 .active1 svg path {
     stroke-dashoffset: 0;
     transition: 1s;
     transition-delay: 1s;
 }
 
 /* Hide previous button */
 #previous-button {
     display: none; /* Hide initially, but you can adjust this based on your needs */
 }
 
 #btn {
     width: 150px; /* Adjust width */
     height: 50px; /* Adjust height */
     border: none;
     outline: none;
     background: #2f2f2f;
     color: #fff;
     font-size: 16px; /* Adjust font size */
     border-radius: 25px; /* Adjust border radius */
     text-align: center;
     line-height: 50px; /* Align text vertically */
     box-shadow: 0 4px 15px -5px rgba(0,0,0,0.4); /* Adjust shadow if needed */
     position: relative;
     overflow: hidden;
     cursor: pointer;
     transition: all 0.3s ease; /* Smooth transition */
 }
 
 #btn.expanded {
     width: 150px; /* Maintain width */
     height: 50px; /* Maintain height */
     border-radius: 25px;
     transition: width 0.5s ease;
 }
 
 #btn p {
     margin: 0; /* Remove margin to center text */
     line-height: 50px; /* Align text vertically */
 }
 
 
 h1{
     text-align: center;
     color: #fff;
     margin-top: 30px;
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
        @media (max-width: 768px){
            .row{
                padding-top: 80px;
            }
            .top-left-image, .top-right-image {
            position: absolute;
            top: 0;
            width: 100px; /* Adjust the size */
        }
        }

         
     </style>

</head>
<body>
    <img src="http://future-x.eu.org/uploads/spslogo.png" class="top-left-image" alt="Top Left Image">
    
    <!-- Image on the top right of the page -->
    <img src="http://future-x.eu.org/uploads/techdevlogo.png" class="top-right-image" alt="Top Right Image">
    <div class="row" id="msform">
        <!-- Progressbar -->
        <ul id="progressbar">
            <li class="active">Personal Details</li>
            <li>Hobbies</li>
            <li>Photo</li>
        </ul>

        <!-- Form -->
        <form id="msform" method="POST" action="process_request.php" enctype="multipart/form-data">
<!-- Personal Details -->
<fieldset>
    <h2 class="fs-title">Personal Details</h2>
    <h3 class="fs-subtitle">Please enter your details</h3>
    <div class="input-container">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
    </div>
    <div class="input-container">
        <label for="name">Email</label>
        <input type="Email" id="Email" name="Email" required>
    </div>
    <div class="input-container">
        <label for="grade">Grade</label>
        
            <select id="grade" name="grade" required>
                <option value="" disabled selected>Select your grade</option>
                <option value="1">1st primary</option>
                <option value="2">2nd primary</option>
                <option value="3">3rd primary</option>
                <option value="4">4th primary</option>
                <option value="5">5th primary</option>
                <option value="6">6th primary</option>
                <option value="7">1st preparatory</option>
                <option value="8">2nd preparatory</option>
                <option value="9">3rd preparatory</option>
                <option value="10">1st secondary</option>
                <option value="11">2nd secondary</option>
                <option value="12">3rd secondary</option>
               </select>
    </div>
    <div class="input-container">
        <label for="national_id">National ID</label>
        <input type="text" id="national_id" name="national_id" required>
    </div>
    <div class="input-container">
        <label for="phone">Phone:</label>
    <input type="tel" id="phone" name="phone" required pattern="[0-9]{11}" placeholder="Enter an 11-digit phone number">
    </div>

    <input type="button" name="next" class="action-button" value="Next" />
</fieldset>

            <!-- Hobbies -->
            <fieldset>
                <h2 class="fs-title">Write Your Hobbies</h2>
                <div class="input-container">
                    <textarea name="hobbies"></textarea>
                 </div>
                          <div class="input-container">
                     <label for="certificates">Upload Certificates</label>
                     <input type="file" id="certificates" name="certificates[]" accept="image/*,.pdf" multiple>
                 </div>
                 <div class="input-container" id="certificate-preview">
                     <!-- Certificate previews will be displayed here -->
                 </div>
                <div class="input-container" id="certificate-preview">
                    <!-- Certificate previews will be displayed here -->
                </div>
                <!-- Add more hobby selections as needed -->
                <input type="button" name="previous" class="action-button-previous" value="Previous" />
                <input type="button" name="next" class="action-button" value="Next" />
            </fieldset>

            <!-- Photo -->
            <fieldset>
                <h2 class="fs-title">Upload Your Photo</h2>
                <h3 class="fs-subtitle">Upload a recent photo of yourself</h3>
                <div class="input-container">
                    <label for="photo">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                </div>
                <div class="input-container" id="multiple-photo-preview">
                    <!-- Photo previews will be displayed here -->
                </div>
                <input type="button" name="previous" class="action-button-previous" value="Previous" />
                <input type="submit" name="submit" class="action-button" value="Submit" />
            </fieldset>
        </form>
        
    </div>

    <div class="modal-container" id="photoModal">
        <span class="modal-close" id="modalClose">&times;</span>
        <div class="modal-content">
            <!-- Photo preview content -->
        </div>
    </div>

    <script>
        
    // Form navigation
    const nextButtons = document.querySelectorAll('input[name="next"]');
        const prevButtons = document.querySelectorAll('input[name="previous"]');
        const fieldsets = document.querySelectorAll('#msform fieldset');
        let current = 0;

        nextButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (validateCurrentFieldset()) {
                    if (current < fieldsets.length - 1) {
                        fieldsets[current].style.display = 'none';
                        fieldsets[++current].style.display = 'block';
                        updateProgressBar();
                    }
                } else {
                    alert("Please complete all required fields before proceeding.");
                }
            });
        });

        prevButtons.forEach(button => {
            button.addEventListener('click', () => {
                if (current > 0) {
                    fieldsets[current].style.display = 'none';
                    fieldsets[--current].style.display = 'block';
                    updateProgressBar();
                }
            });
        });

        function updateProgressBar() {
            const progressbarItems = document.querySelectorAll('#progressbar li');
            progressbarItems.forEach((item, index) => {
                item.classList.toggle('active', index <= current);
            });
        }

        function validateCurrentFieldset() {
            const inputs = fieldsets[current].querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.style.borderColor = 'red'; // Highlight invalid fields
                } else {
                    input.style.borderColor = '#d1d5db'; // Reset border color if valid
                }
            });

            return isValid;
        }

        // Photo preview
        const photoInput = document.getElementById('photo');
        const previewContainer = document.getElementById('multiple-photo-preview');

        photoInput.addEventListener('change', (e) => {
            previewContainer.innerHTML = ''; // Clear previous previews
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // Certificate preview
        const certificateInput = document.getElementById('certificates');
        const certificatePreviewContainer = document.getElementById('certificate-preview');

        certificateInput.addEventListener('change', (e) => {
            certificatePreviewContainer.innerHTML = ''; // Clear previous previews
            Array.from(e.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    certificatePreviewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
    
</body>
</html>
