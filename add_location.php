<?php
session_start();
include("database.php"); 
include("header_buttons.php");
include("api_keys.php");

// Verificăm dacă utilizatorul este autentificat
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

if (isset($_POST['cancel_update'])) {
    header("Location: profile.php");
    exit();
}

// Dacă formularul este trimis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preluăm datele trimise din formular și ne asigurăm că sunt sanitizate corect
    $location_name = mysqli_real_escape_string($conn, $_POST['location_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $visit_date = mysqli_real_escape_string($conn, $_POST['visit_date']);
    $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;

    // Verificăm dacă latitudinea și longitudinea sunt valide
    if (is_null($latitude) || is_null($longitude)) {
        echo "Latitude or longitude is missing!";
        exit;
    }

    // Tratarea fișierului încărcat (imaginea)
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];

        // Definim calea completă unde va fi salvată imaginea
        $targetDir = "uploads/locations/";
        $targetFile = $targetDir . basename($image); // Setăm calea completă a fișierului

        // Mutăm fișierul în directorul dorit
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Salvăm calea completă a imaginii în baza de date
            $imagePath = $targetFile;
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    // Construim query-ul de inserare cu sau fără imagine, în funcție de ce avem
    $insert_location_sql = "INSERT INTO locations (user_id, location_name, description, visit_date, latitude, longitude, image_path) 
                            VALUES ($current_user_id, '$location_name', '$description', '$visit_date', $latitude, $longitude, " . ($imagePath ? "'$imagePath'" : "NULL") . ")";

    // Executăm query-ul și verificăm dacă a reușit
    if (mysqli_query($conn, $insert_location_sql)) {
        echo "<p>Location added successfully!</p>"; 
        if (isset($_POST['add_location'])) {
            header("Location: profile.php");
            exit();
        }
    } else {
        echo "<p>Error adding location: " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Location</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places"></script>
    <style>
        .add-location-form {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .add-location-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .add-location-form input[type="text"], 
        .add-location-form input[type="date"], 
        .add-location-form textarea, 
        .add-location-form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"], button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover, button:hover {
            background-color: #4cae4c;
        }

        /* Stiluri pentru butonul de search */
        .search-button {
            width: auto; /* Lățime automată */
            padding: 5px 10px; /* Padding mai mic */
            background-color: #f0f0f0; /* Fundal deschis pentru un aspect basic */
            color: #333; /* Culoare text mai închisă */
            border: 1px solid #ccc; /* Bordură pentru a evidenția butonul */
            border-radius: 5px; /* Colțuri rotunjite */
            margin-top: 0; /* Fără margine de sus */
            cursor: pointer; /* Cursor pointer pentru interacțiune */
        }

        /* Stil pentru butonul de profil */
        .profile-button {
            position: fixed; /* Păstrează butonul pe loc */
            top: 20px; /* Margine de sus */
            left: 20px; /* Margine de stânga */
            background-color: #5bc0de; /* Culoarea de fundal a butonului */
            color: white; /* Culoarea textului */
            border: none; /* Fără bordură */
            border-radius: 5px; /* Colțuri rotunjite */
            padding: 15px 20px; /* Ajustăm padding-ul */
            cursor: pointer; /* Cursor pointer */
            z-index: 1000; /* Asigură-te că butonul este deasupra celorlalte elemente */
            width: auto; /* Lățime automată */
        }

        .profile-button:hover {
            background-color: #31b0d5; /* Culoare la hover */
        }

        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <button class="profile-button" onclick="window.location.href='profile.php'">Back to Profile</button> <!-- Butonul pentru profil -->
    
    <div class="add-location-form">
        <form action="add_location.php" method="POST" enctype="multipart/form-data">
            <label for="location_name">Location Name:</label>
            <input type="text" id="location_name" name="location_name" placeholder="Location name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" placeholder="Describe the location" required></textarea>

            <label for="visit_date">Date of Visit:</label>
            <input type="date" id="visit_date" name="visit_date" required>

            <label for="location">Select Location:</label>
            <input type="text" id="location-input" placeholder="Enter a location" required>
            <button type="button" id="search-button" class="search-button">Search</button> <!-- Aplicăm stilul -->

            <div id="map"></div>

            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <label for="image">Upload Image (optional):</label>
            <input type="file" id="image" name="image" accept="image/*"> <!-- Câmp pentru încărcarea imaginii -->

            <input type="submit" name="add_location" value="Add Location">
            <input type="submit" name="cancel_update" value="Cancel" id="cancel-button"> <!-- Păstrăm stilul -->
        </form>
    </div>
    <script>
        // Adaugă event listener pentru butonul de cancel
        document.getElementById('cancel-button').addEventListener('click', function(event) {
            event.preventDefault(); // Previne trimiterea formularului
            window.location.href = 'profile.php'; // Redirecționează către pagina de profil
        });

        document.getElementById('search-button').addEventListener('click', function() {
            const address = document.getElementById('location-input').value;
            const geocoder = new google.maps.Geocoder();

            geocoder.geocode({ 'address': address }, function(results, status) {
                if (status === 'OK') {
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();
                    
                    // Afișează coordonatele în input-urile ascunse
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    // Setează harta și marker-ul la locația căutată
                    map.setCenter(results[0].geometry.location);
                    marker.setPosition(results[0].geometry.location);
                } else {
                    alert('Geocode was not successful for the following reason: ' + status);
                }
            });
        });

        let map;
        let marker;
        let geocoder;

        function initMap() {
            const initialLocation = { lat: -34.397, lng: 150.644 }; // Default location

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 8,
                center: initialLocation
            });

            marker = new google.maps.Marker({
                position: initialLocation,
                map: map,
                draggable: true // Permite mutarea marker-ului
            });

            // Adaugă event listener pentru dragul marker-ului
            marker.addListener('dragend', function() {
                const lat = marker.getPosition().lat();
                const lng = marker.getPosition().lng();
                document.getElementById('latitude').value = lat; // Setează latitudinea
                document.getElementById('longitude').value = lng; // Setează longitudinea
            });
        }

        window.onload = initMap;
    </script>
</body>
</html>
