<?php
session_start();
include("database.php"); 
include("api_keys.php");

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Obține ID-ul utilizatorului din URL
if (isset($_GET['id'])) {
    $view_user_id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id='$view_user_id'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $friend = mysqli_fetch_assoc($result);
    } else {
        echo "User not found!";
        exit();
    }
} else {
    echo "No user ID specified.";
    exit();
}
// Obține preferințele prietenului
$preferences_sql = "SELECT vacation_type, preferred_destinations FROM preferences WHERE user_id='$view_user_id'";
$preferences_result = mysqli_query($conn, $preferences_sql);
$preferences = mysqli_fetch_assoc($preferences_result);

// Obține locațiile prietenului
$locations_sql = "SELECT * FROM locations WHERE user_id='$view_user_id'";
$locations_result = mysqli_query($conn, $locations_sql);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($friend['user']); ?>'s Profile</title>
    <style>
        /* Adaugă stiluri pentru butonul de mesaje */
        .message-button {
            display: inline-block;
            background-color: #5bc0de; /* Culoarea de fundal */
            color: white; /* Culoarea textului */
            padding: 10px 20px; /* Spațiere interioară */
            text-decoration: none; /* Elimină sublinierea textului */
            border-radius: 5px; /* Colțuri rotunjite */
            font-size: 16px; /* Dimensiunea textului */
            transition: background-color 0.3s ease; /* Trecere lină la hover */
        }
        
        .message-button:hover {
            background-color: #31b0d5; /* Culoarea de fundal la hover */
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .profile-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align content */
        }
        h1 {
            color: #333;
        }
        .profile-picture {
            width: 150px; /* Size of the profile picture */
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .location-container {
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
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
        .location-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px; /* Space above the map */
        }
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px; /* Space between buttons */
            margin-left: auto; /* Move buttons to the right */
        }
        .action-buttons button {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .action-buttons button.logout {
            background-color: #d9534f;
        }
        .action-buttons button.logout:hover {
            background-color: #c9302c;
        }
        .action-buttons button:hover {
            background-color: #31b0d5;
        }
    </style>
    <!-- Include Google Maps JavaScript API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
</head>
<body>
<div class="action-buttons">
    <button class="message-button" onclick="window.location.href='messages.php'">✉</button>
    <button class="friends" onclick="window.location.href='friends.php'">Friends</button>   
    <button onclick="window.location.href='home.php'">Home</button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>

<div class="profile-container">
    <?php
    // Set default profile picture
    $profile_picture = !empty($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'uploads/default-profile-picture.jpg';
    ?>
    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-picture">
    <h1><?php echo htmlspecialchars($friend['user']); ?>'s Profile</h1>
    <p>Email: <?php echo htmlspecialchars($friend['email']); ?></p>
    <p>Age: <?php echo htmlspecialchars($friend['age']); ?></p>
    <p>Lifestyle: <?php echo htmlspecialchars($friend['lifestyle']); ?></p>
    <p>Interests: <?php echo htmlspecialchars($friend['interests']); ?></p>
    <p>Vacation Type: 
        <?php 
        echo htmlspecialchars($preferences['vacation_type'] ?? 'The user has not filled out the form yet.'); 
        ?>
        </p>
    <p>Preferred Destinations: 
        <?php 
        echo htmlspecialchars($preferences['preferred_destinations'] ?? 'The user has not filled out the form yet.'); 
        ?>
        </p>

    <a class="message-button" href="chat.php?friend_id=<?php echo $friend['id']; ?>">Send Message</a>
</div>

<div class="location-container">
    <h2><?php echo htmlspecialchars($friend['user']); ?>'s Visited Locations</h2>

    <?php if (mysqli_num_rows($locations_result) > 0): ?>
        <?php while ($location = mysqli_fetch_assoc($locations_result)): ?>
            <div class="location-item">
                <div class="location-details">
                    <h3><?php echo htmlspecialchars($location['location_name']); ?></h3>
                    <p><?php echo htmlspecialchars($location['description']); ?></p>
                    <p class="visit-date">Visited on: <?php echo htmlspecialchars($location['visit_date']); ?></p>
                    <a href="location_comments.php?id=<?php echo $location['location_id']; ?>" class="comment-button">View Comments</a>
                </div>
                <?php if (!empty($location['image_path'])): ?>
                    <img class="location-image" src="<?php echo htmlspecialchars($location['image_path']); ?>" alt="Location Image">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>This user has not visited any locations yet.</p>
    <?php endif; ?>
</div>

<!-- Google Maps Script -->
<div id="map"></div>

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
