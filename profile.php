<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("database.php"); 
include("header_buttons.php");
include("api_keys.php");

// Obținem ID-ul utilizatorului curent
$current_user_id = $_SESSION['user_id'];

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Obține ID-ul utilizatorului din URL, dacă există
if (isset($_GET['id'])) {
    $view_user_id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id='$view_user_id'";
} else {
    // Dacă nu este specificat un ID, afișează datele utilizatorului conectat
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM users WHERE user='$username'";
}

// Obținem datele utilizatorului și preferințele sale
$user_id = $_SESSION['user_id']; // presupunem că ai deja user_id stocat în sesiune

// Interogare pentru a obține preferințele utilizatorului
$sql_prefs = "SELECT vacation_type, preferred_activities, preferred_destinations, group_travel, visited_destinations, travel_period FROM preferences WHERE user_id = ?";
$stmt_prefs = $conn->prepare($sql_prefs);
$stmt_prefs->bind_param("i", $user_id);
$stmt_prefs->execute();
$result_prefs = $stmt_prefs->get_result();
$preferences = $result_prefs->fetch_assoc();


// Fetch user details from the database
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "User not found!";
    exit();
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $age = intval($_POST['age']);
    $lifestyle = mysqli_real_escape_string($conn, $_POST['lifestyle']);
    $interests = mysqli_real_escape_string($conn, $_POST['interests']);
    $profile_picture = isset($_FILES['profile_picture']) ? $_FILES['profile_picture'] : null;

    // Initialize update query
    $update_sql = "UPDATE users SET email='$email', age=$age, lifestyle='$lifestyle', interests='$interests'";

    // Check if a file has been uploaded
    if ($profile_picture && $profile_picture['error'] == 0) {
        $target_dir = "uploads/"; // Ensure this directory exists and is writable
        $target_file = $target_dir . basename($profile_picture["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file extension
        if (in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                // Update file path in the database
                $update_sql .= ", profile_picture='$target_file'";
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    // Complete the SQL query
    $update_sql .= " WHERE user='$username'";

    // Execute the update query
    if (mysqli_query($conn, $update_sql)) {
        $message = "Profile updated successfully!";
        // Reload the user information
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "Error updating profile: " . mysqli_error($conn);
    }
}

// Query pentru a obține locațiile utilizatorului, inclusiv coordonatele
$locations_sql = "SELECT location_id, location_name, description, visit_date, latitude, longitude, image_path 
                  FROM locations 
                  WHERE user_id = $current_user_id 
                  ORDER BY visit_date DESC";
$locations_result = mysqli_query($conn, $locations_sql);


// Handle cancel update action
if (isset($_POST['cancel_update'])) {
    header("Location: profile.php");
    exit();
}

if (isset($_POST['update_profile'])) {
    header("Location: profile.php");
    exit();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        /* General styles for the body */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
            height: 100vh;
            padding: 20px;
            position: relative;
        }

        /* Container for the main content */
        .main-container {
            display: flex; /* Use flexbox to arrange items side by side */
            width: 100%;
        }

        /* Container for the profile form */
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            margin-right: 20px; /* Space to the right for the map and location cards */
        }

        /* Headings */
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: left;
        }

        /* Style the form inputs */
        input[type="text"], input[type="password"], input[type="email"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Style the buttons */
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .preferrences {
            width: 96%;
            padding: 5px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 0px;
            margin-bottom: 0px;
        }
        .preferrences:hover {
            background-color: #4cae4c;
        }

        .cancel-button {
            background-color: #d9534f;
        }

        .cancel-button:hover {
            background-color: #c9302c;
        }

        /* Location container styles */
        .location-container {
            margin-top: 20px;
            width: 100%;
            background-color: #fff; /* Background for the location container */
            padding: 20px; /* Padding inside location container */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Shadow effect */
            display: flex;
            flex-direction: column; /* Stack location cards vertically */
            align-items: stretch; /* Stretch cards to full width */
        }

        .location-item {
            background-color: #fff; /* Fundal pentru fiecare card de locație */
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex; /* Folosește flexbox pentru layout */
            align-items: flex-start; /* Aliniază elementele în partea de sus */
            transition: transform 0.3s; /* Animație pentru efectul hover */
        }

        .location-item:hover {
            transform: scale(1.02); /* Scalează puțin la hover */
        }


        .location-details {
            flex-grow: 1;
            padding-right: 20px;
        }

        .location-details h3 {
            margin: 0;
            color: #333;
        }

        .location-details p {
            margin: 5px 0;
            color: #666;
        }

        .location-details .visit-date {
            font-style: italic;
            color: #999;
        }

        .location-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
        }

        /* Google Maps */
        #map {
            height: 400px;
            width: 100%; /* Full width for the map */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px; /* Space above the map */
        }

        /* Space between forms */
        form {
            margin-bottom: 5px; /* Reduced space between forms */
        }

        form + form {
            margin-top: -10px; /* Reduced distance between "Edit Profile" and "Add New Location" */
        }
        .comment-button {
            display: inline-block;
            background-color: #5bc0de; /* Culoarea de fundal */
            color: white; /* Culoarea textului */
            padding: 10px 20px; /* Spațiere interioară */
            text-decoration: none; /* Elimină sublinierea textului */
            border-radius: 5px; /* Colțuri rotunjite */
            font-size: 16px; /* Dimensiunea textului */
            transition: background-color 0.3s ease; /* Trecere lină la hover */
        }
        
        .comment-button:hover {
            background-color: #31b0d5; /* Culoarea de fundal la hover */
        }

    </style>
    <!-- Include Google Maps JavaScript API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</head>
<body>
<div class="main-container">
    <div class="form-container">
        <h1>Welcome to your profile, <?php echo htmlspecialchars($user['user']); ?>!</h1>
        <?php if (!empty($user['profile_picture'])): ?>
            <p><img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="max-width: 100%; height: auto; border-radius: 5px;"></p>
        <?php endif; ?>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Age: <?php echo htmlspecialchars($user['age']); ?></p>
        <p>Lifestyle: <?php echo htmlspecialchars($user['lifestyle']); ?></p>
        <p>Interests: <?php echo htmlspecialchars($user['interests']); ?></p>
        <?php if ($preferences): ?>
            <h3>Additional Travel Preferences:</h3>
            <p><strong>Vacation Type:</strong> <?php echo htmlspecialchars($preferences['vacation_type'] ?? 'N/A'); ?></p>
            <p><strong>Preferred Destinations:</strong> <?php echo htmlspecialchars($preferences['preferred_destinations'] ?? 'N/A'); ?></p>
            <p><strong>Group Travel:</strong> <?php echo htmlspecialchars($preferences['group_travel'] ?? 'N/A'); ?></p>
            <p><strong>Preferred Travel Period:</strong> <?php echo htmlspecialchars($preferences['travel_period'] ?? 'N/A'); ?></p>
        <?php else: ?>
            <p><strong>Additional Travel Preferences:</strong> You have not set any preferences yet.</p>
        <?php endif; ?>

        <div class="preferrences">
            <button class="preferrences" onclick="window.location.href='save_preferences.php'">Edit Preferences</button>
        </div>

        <?php if (isset($_POST['edit_profile']) || isset($_POST['update_profile'])): ?>
            <h2>Update Profile</h2>
            <form action="profile.php" method="post" enctype="multipart/form-data">

                <label for="profile_picture">Profile Picture:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*"><br>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
                <label for="age">Age:</label>
                <input type="number" name="age" id="age" value="<?php echo htmlspecialchars($user['age']); ?>" required><br>
                <label for="lifestyle">Lifestyle:</label>
                <input type="text" name="lifestyle" id="lifestyle" value="<?php echo htmlspecialchars($user['lifestyle']); ?>" required><br>
                <label for="interests">Interests:</label>
                <textarea name="interests" id="interests" rows="4" required><?php echo htmlspecialchars($user['interests']); ?></textarea><br>
                <input type="submit" name="update_profile" value="Update Profile">
                <input type="submit" name="cancel_update" value="Cancel">
            </form>
            <?php if (isset($message)) echo '<div class="error-message">' . $message . '</div>'; ?>
        <?php else: ?>
            <form action="profile.php" method="post">
                <input type="submit" name="edit_profile" value="Edit Profile">
            </form>
            <form action="add_location.php" method="get">
                <input type="submit" value="Add New Location">
            </form>
        <?php endif; ?>
    </div>

    <div class="location-container">
        <h2>Your Visited Locations</h2>

        <?php if (mysqli_num_rows($locations_result) > 0): ?>
            <?php while ($location = mysqli_fetch_assoc($locations_result)): ?>
                <div class="location-item">
                    <div class="location-details">
                        <h3><?php echo htmlspecialchars($location['location_name']); ?></h3>
                        <p><?php echo htmlspecialchars($location['description']); ?></p>
                        <p class="visit-date">Visited on: <?php echo htmlspecialchars($location['visit_date']); ?></p>
                        <a href="location_comments.php?id=<?php echo $location['location_id']; ?>" class="comment-button">Vezi comentariile</a>

                    </div>
                    <?php if (!empty($location['image_path'])): ?>
                        <img class="location-image" src="<?php echo htmlspecialchars($location['image_path']); ?>" alt="Location Image">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have not visited any locations yet.</p>
        <?php endif; ?>
        
        <!-- Google Maps Script -->
        <div id="map"></div>
    </div>
</div>

<style>
    /* Additional styles for the table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px; /* Rounded corners for the table */
        overflow: hidden; /* Ensures rounded corners are visible */
    }

    th {
        background-color: #5cb85c; /* Header background color */
        color: white; /* Header text color */
    }

    th, td {
        padding: 15px; /* Padding for cells */
        text-align: left; /* Align text to the left */
    }

    tbody tr {
        background-color: #f9f9f9; /* Default row background color */
        transition: background-color 0.3s; /* Transition effect for hover */
    }

    tbody tr:hover {
        background-color: #e0e0e0; /* Hover background color */
    }

    img {
        max-width: 100px; /* Set max width for images */
        height: auto; /* Maintain aspect ratio */
        border-radius: 5px; /* Rounded corners for images */
    }
</style>

<script>
    // Codul JavaScript pentru Google Maps
    let map;
    let markers = [];

    function initMap() {
        const center = { lat: 0, lng: 0 }; // Default center
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 2,
            center: center,
        });

        // Define the locations for Google Maps
        const locations = [
            <?php 
            // Refacerea array-ului de locații pentru Google Maps
            mysqli_data_seek($locations_result, 0); // Resetarea pointerului la rezultatele din interogare
            while ($location = mysqli_fetch_assoc($locations_result)): 
            ?>
                {
                    name: "<?php echo htmlspecialchars($location['location_name']); ?>",
                    lat: <?php echo htmlspecialchars($location['latitude']); ?>,
                    lng: <?php echo htmlspecialchars($location['longitude']); ?>,
                },
            <?php endwhile; ?>
        ];

        // Adăugarea markerelor pe hartă
        locations.forEach(location => {
            const marker = new google.maps.Marker({
                position: { lat: location.lat, lng: location.lng },
                map: map,
                title: location.name,
            });
            markers.push(marker);
        });

        // Setarea centrului hărții pe baza locațiilor
        if (locations.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            locations.forEach(location => {
                bounds.extend(new google.maps.LatLng(location.lat, location.lng));
            });
            map.fitBounds(bounds);
        }
    }
</script>

</body>
</html>
