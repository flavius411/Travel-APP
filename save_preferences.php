<?php
session_start();
include("database.php");
include("header_buttons.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // ID-ul utilizatorului conectat

// Verificăm dacă utilizatorul are preferințe salvate
$existing_preferences = [];

// Interogăm baza de date pentru a vedea dacă există preferințe
$check_sql = "SELECT * FROM preferences WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $check_sql);

// Dacă găsim preferințe, le salvăm în $existing_preferences
if (mysqli_num_rows($result) > 0) {
    $existing_preferences = mysqli_fetch_assoc($result);
} else {
    // Dacă nu există preferințe, folosim valori implicite
    $existing_preferences = [
        'vacation_type' => '',
        'preferred_activities' => '',
        'water_activities' => '',
        'adventure_activities' => '',
        'cultural_activities' => '',
        'city_break_activities' => '',
        'active_activities' => '',
        'relaxation_activities' => '',
        'budget' => '',
        'vacation_duration' => '',
        'preferred_destinations' => '',
        'preferred_transport' => '',
        'preferred_accommodation' => '',
        'transport_class' => '',
        'culinary_experience' => '',
        'group_travel' => '',
        'travel_period' => '',
        'dietary_restrictions' => '',
        'visited_destinations' => ''
    ];
}

// Dacă formularul este trimis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preluăm datele trimise și folosim implode dacă este un array
    $vacation_type = isset($_POST['vacation_type']) ? implode(", ", $_POST['vacation_type']) : '';

    // Preluăm și unim activitățile
    $water_activities = isset($_POST['water_activities']) ? implode(", ", $_POST['water_activities']) : '';
    $adventure_activities = isset($_POST['adventure_activities']) ? implode(", ", $_POST['adventure_activities']) : '';
    $cultural_activities = isset($_POST['cultural_activities']) ? implode(", ", $_POST['cultural_activities']) : '';
    $city_break_activities = isset($_POST['city_break_activities']) ? implode(", ", $_POST['city_break_activities']) : '';
    $active_activities = isset($_POST['active_activities']) ? implode(", ", $_POST['active_activities']) : '';
    $relaxation_activities = isset($_POST['relaxation_activities']) ? implode(", ", $_POST['relaxation_activities']) : '';

    

    // Preluăm toate activitățile preferate într-un singur șir
    $preferred_activities = '';
    if (!empty($water_activities) || !empty($adventure_activities) || !empty($cultural_activities) || !empty($city_break_activities) || !empty($active_activities) || !empty($relaxation_activities)) {
        $all_activities = array_merge(
            !empty($water_activities) ? explode(", ", $water_activities) : [],
            !empty($adventure_activities) ? explode(", ", $adventure_activities) : [],
            !empty($cultural_activities) ? explode(", ", $cultural_activities) : [],
            !empty($city_break_activities) ? explode(", ", $city_break_activities) : [],
            !empty($active_activities) ? explode(", ", $active_activities) : [],
            !empty($relaxation_activities) ? explode(", ", $relaxation_activities) : []
        );
        $preferred_activities = implode(", ", $all_activities);
    }

    // Validăm restul datelor din formular
     // Preluăm datele trimise
    $budget = isset($_POST['budget']) ? mysqli_real_escape_string($conn, $_POST['budget']) : '';
    $custom_budget = isset($_POST['custom_budget']) ? mysqli_real_escape_string($conn, $_POST['custom_budget']) : '';

    $vacation_duration = isset($_POST['vacation_duration']) ? mysqli_real_escape_string($conn, $_POST['vacation_duration']) : '';
    $preferred_transport = isset($_POST['preferred_transport']) ? mysqli_real_escape_string($conn, $_POST['preferred_transport']) : '';
    $preferred_accommodation = isset($_POST['preferred_accommodation']) ? mysqli_real_escape_string($conn, $_POST['preferred_accommodation']) : '';
    $transport_class = isset($_POST['transport_class']) ? mysqli_real_escape_string($conn, $_POST['transport_class']) : '';
    $group_travel = isset($_POST['group_travel']) ? mysqli_real_escape_string($conn, $_POST['group_travel']) : '';
    $travel_period = isset($_POST['travel_period']) ? mysqli_real_escape_string($conn, $_POST['travel_period']) : '';
    $dietary_restrictions = isset($_POST['dietary_restrictions']) ? mysqli_real_escape_string($conn, $_POST['dietary_restrictions']) : '';

    // Validăm preferințele culinare
    $culinary_experience = isset($_POST['culinary_experience']) ? implode(", ", $_POST['culinary_experience']) : '';

    // Validăm destinațiile preferate
    $preferred_destinations = isset($_POST['preferred_destinations']) ? implode(", ", $_POST['preferred_destinations']) : '';

    // Validăm destinațiile vizitate
    $visited_destinations = isset($_POST['visited_destinations']) ? implode(", ", $_POST['visited_destinations']) : '';


    // Dacă utilizatorul a ales "Custom", setăm bugetul la valoarea din inputul personalizat
    if ($budget === 'Custom' && !empty($custom_budget)) {
        $budget = $custom_budget; // Setăm bugetul la valoarea din inputul personalizat
    }


    // Verificăm dacă există deja preferințe pentru utilizatorul conectat
    if (mysqli_num_rows($result) > 0) {
        // Actualizăm preferințele utilizatorului dacă deja există
        $sql = "UPDATE preferences SET
                    vacation_type = '$vacation_type',
                    preferred_activities = '$preferred_activities',
                    water_activities = '$water_activities',
                    adventure_activities = '$adventure_activities',
                    cultural_activities = '$cultural_activities',
                    city_break_activities = '$city_break_activities',
                    active_activities = '$active_activities',
                    relaxation_activities = '$relaxation_activities',
                    budget = '$budget',
                    vacation_duration = '$vacation_duration',
                    preferred_destinations = '$preferred_destinations',
                    preferred_transport = '$preferred_transport',
                    preferred_accommodation = '$preferred_accommodation',
                    transport_class = '$transport_class',
                    culinary_experience = '$culinary_experience',
                    group_travel = '$group_travel',
                    travel_period = '$travel_period',
                    dietary_restrictions = '$dietary_restrictions',
                    visited_destinations = '$visited_destinations'
                WHERE user_id = '$user_id'";
    } else {
        // Introducem preferințele pentru prima dată
        $sql = "INSERT INTO preferences (user_id, vacation_type, preferred_activities, water_activities, adventure_activities, cultural_activities, city_break_activities, active_activities, relaxation_activities, budget, vacation_duration, preferred_destinations, preferred_transport, preferred_accommodation, transport_class, culinary_experience, group_travel, travel_period, dietary_restrictions, visited_destinations)
                VALUES ('$user_id', '$vacation_type', '$preferred_activities', '$water_activities', '$adventure_activities', '$cultural_activities', '$city_break_activities', '$active_activities', '$relaxation_activities', '$budget', '$vacation_duration', '$preferred_destinations', '$preferred_transport', '$preferred_accommodation', '$transport_class', '$culinary_experience', '$group_travel', '$travel_period', '$dietary_restrictions', '$visited_destinations')";
    }

    // Verificăm dacă actualizarea/inserarea a fost realizată cu succes
    if (mysqli_query($conn, $sql)) {
        // Marcam ca preferințele au fost completate în users
        $update_user_sql = "UPDATE users SET preferences_completed = 1 WHERE id = '$user_id'";
        mysqli_query($conn, $update_user_sql);

        // Redirecționăm utilizatorul la pagina principală după salvare
        header("Location: home.php");
        exit();  // Oprirea scriptului după redirecționare
    } else {
        echo "Eroare la actualizarea preferințelor: " . mysqli_error($conn);
    }
}

// Închidem conexiunea cu baza de date
mysqli_close($conn);
?>



    <style>
        /* Stiluri generale pentru pagină */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Stiluri pentru formular */
        form {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        select, input[type="checkbox"], input[type="submit"] {
            margin-bottom: 15px;
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        h3 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        input[type="submit"], .cancel-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease;
            padding: 10px; /* Adaugă padding identic */
            width: 100%; /* Setează aceeași lățime */
            border-radius: 5px; /* Rotunjire colțuri */
            margin-bottom: 15px; /* Asigură-te că au același spațiu între ele */
            text-align: center; /* Centrăm textul în ambele */
        }

        input[type="submit"]:hover, .cancel-button:hover {
            background-color: #45a049;
        }


        /* Stiluri pentru checkbox-uri */
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .checkbox-group label {
            flex: 1 0 45%; /* Fiecare checkbox ocupă aproximativ 45% din lățime */
            margin-bottom: 10px;
        }
        .back-button {
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

        .back-button:hover {
            background-color: #31b0d5; /* Culoare la hover */
        }
    </style>
<button class="back-button" onclick="window.location.href='home.php'">Back</button>
<form action="save_preferences.php" method="post">
    <h3>Select your travel preferences:</h3>

    <label for="vacation_type">Preferred type of vacation:</label><br>
    <div class="checkbox-group">
        <label><input type="checkbox" name="vacation_type[]" value="Relaxation" <?php echo (strpos($existing_preferences['vacation_type'], 'Relaxation') !== false) ? 'checked' : ''; ?>> Relaxation</label><br>
        <label><input type="checkbox" name="vacation_type[]" value="Adventure" <?php echo (strpos($existing_preferences['vacation_type'], 'Adventure') !== false) ? 'checked' : ''; ?>> Adventure</label><br>
        <label><input type="checkbox" name="vacation_type[]" value="Cultural Exploration" <?php echo (strpos($existing_preferences['vacation_type'], 'Cultural Exploration') !== false) ? 'checked' : ''; ?>> Cultural Exploration</label><br>
        <label><input type="checkbox" name="vacation_type[]" value="City Break" <?php echo (strpos($existing_preferences['vacation_type'], 'City Break') !== false) ? 'checked' : ''; ?>> City Break</label><br>
        <label><input type="checkbox" name="vacation_type[]" value="Active Vacation" <?php echo (strpos($existing_preferences['vacation_type'], 'Active Vacation') !== false) ? 'checked' : ''; ?>> Active Vacation</label><br>
        <label><input type="checkbox" name="vacation_type[]" value="Beach" <?php echo (strpos($existing_preferences['vacation_type'], 'Beach') !== false) ? 'checked' : ''; ?>> Beach</label>
    </div>

    <!-- Activități de apă -->
    <div id="water_activities" style="display: none;">
        <label for="water_activities">Preferred water activities (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="water_activities[]" value="Snorkeling" <?php echo (strpos($existing_preferences['water_activities'], 'Snorkeling') !== false) ? 'checked' : ''; ?>> Snorkeling</label><br>
            <label><input type="checkbox" name="water_activities[]" value="Scuba Diving" <?php echo (strpos($existing_preferences['water_activities'], 'Scuba Diving') !== false) ? 'checked' : ''; ?>> Scuba Diving</label><br>
            <label><input type="checkbox" name="water_activities[]" value="Surfing" <?php echo (strpos($existing_preferences['water_activities'], 'Surfing') !== false) ? 'checked' : ''; ?>> Surfing</label><br>
            <label><input type="checkbox" name="water_activities[]" value="Jet Skiing" <?php echo (strpos($existing_preferences['water_activities'], 'Jet Skiing') !== false) ? 'checked' : ''; ?>> Jet Skiing</label><br>
            <label><input type="checkbox" name="water_activities[]" value="Kayaking" <?php echo (strpos($existing_preferences['water_activities'], 'Kayaking') !== false) ? 'checked' : ''; ?>> Kayaking</label>
        </div>
    </div>

    <!-- Activități de aventură -->
    <div id="adventure_activities_div" style="display: none;">
        <label for="adventure_activities">Preferred adventure activities (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="adventure_activities[]" value="Hiking" <?php echo (strpos($existing_preferences['adventure_activities'], 'Hiking') !== false) ? 'checked' : ''; ?>> Hiking</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="Mountain Biking" <?php echo (strpos($existing_preferences['adventure_activities'], 'Mountain Biking') !== false) ? 'checked' : ''; ?>> Mountain Biking</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="Rock Climbing" <?php echo (strpos($existing_preferences['adventure_activities'], 'Rock Climbing') !== false) ? 'checked' : ''; ?>> Rock Climbing</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="ATV Riding" <?php echo (strpos($existing_preferences['adventure_activities'], 'ATV Riding') !== false) ? 'checked' : ''; ?>> ATV Riding</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="Zip Lining" <?php echo (strpos($existing_preferences['adventure_activities'], 'Zip Lining') !== false) ? 'checked' : ''; ?>> Zip Lining</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="Skydiving" <?php echo (strpos($existing_preferences['adventure_activities'], 'Skydiving') !== false) ? 'checked' : ''; ?>> Skydiving</label><br>
            <label><input type="checkbox" name="adventure_activities[]" value="Amusement Parks" <?php echo (strpos($existing_preferences['adventure_activities'], 'Amusement Parks') !== false) ? 'checked' : ''; ?>> Amusement Parks</label>
        </div>
    </div>

    <!-- Activități culturale -->
    <div id="cultural_activities_div" style="display: none;">
        <label for="cultural_activities">Preferred cultural activities (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="cultural_activities[]" value="Museum Visits" <?php echo (strpos($existing_preferences['cultural_activities'], 'Museum Visits') !== false) ? 'checked' : ''; ?>> Museum Visits</label><br>
            <label><input type="checkbox" name="cultural_activities[]" value="Cultural Festivals" <?php echo (strpos($existing_preferences['cultural_activities'], 'Cultural Festivals') !== false) ? 'checked' : ''; ?>> Cultural Festivals</label><br>
            <label><input type="checkbox" name="cultural_activities[]" value="Local Cuisine Tasting" <?php echo (strpos($existing_preferences['cultural_activities'], 'Local Cuisine Tasting') !== false) ? 'checked' : ''; ?>> Local Cuisine Tasting</label><br>
            <label><input type="checkbox" name="cultural_activities[]" value="Historical Tours" <?php echo (strpos($existing_preferences['cultural_activities'], 'Historical Tours') !== false) ? 'checked' : ''; ?>> Historical Tours</label><br>
            <label><input type="checkbox" name="cultural_activities[]" value="Art Exhibitions" <?php echo (strpos($existing_preferences['cultural_activities'], 'Art Exhibitions') !== false) ? 'checked' : ''; ?>> Art Exhibitions</label>
        </div>
    </div>

    <!-- Activități pentru City Break -->
    <div id="city_break_activities_div" style="display: none;">
        <label for="city_break_activities">Preferred activities for city breaks (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="city_break_activities[]" value="Sightseeing" <?php echo (strpos($existing_preferences['city_break_activities'], 'Sightseeing') !== false) ? 'checked' : ''; ?>> Sightseeing</label><br>
            <label><input type="checkbox" name="city_break_activities[]" value="Shopping" <?php echo (strpos($existing_preferences['city_break_activities'], 'Shopping') !== false) ? 'checked' : ''; ?>> Shopping</label><br>
            <label><input type="checkbox" name="city_break_activities[]" value="Nightlife" <?php echo (strpos($existing_preferences['city_break_activities'], 'Nightlife') !== false) ? 'checked' : ''; ?>> Nightlife</label><br>
            <label><input type="checkbox" name="city_break_activities[]" value="Food Tours" <?php echo (strpos($existing_preferences['city_break_activities'], 'Food Tours') !== false) ? 'checked' : ''; ?>> Food Tours</label><br>
            <label><input type="checkbox" name="city_break_activities[]" value="Local Markets" <?php echo (strpos($existing_preferences['city_break_activities'], 'Local Markets') !== false) ? 'checked' : ''; ?>> Local Markets</label>
        </div>
    </div>

    <!-- Activități pentru Vacanța Activă -->
    <div id="active_activities_div" style="display: none;">
        <label for="active_activities">Preferred activities for an active vacation (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="active_activities[]" value="Hiking" <?php echo (strpos($existing_preferences['active_activities'], 'Hiking') !== false) ? 'checked' : ''; ?>> Hiking</label><br>
            <label><input type="checkbox" name="active_activities[]" value="Cycling" <?php echo (strpos($existing_preferences['active_activities'], 'Cycling') !== false) ? 'checked' : ''; ?>> Cycling</label><br>
            <label><input type="checkbox" name="active_activities[]" value="Team Sports" <?php echo (strpos($existing_preferences['active_activities'], 'Team Sports') !== false) ? 'checked' : ''; ?>> Team Sports</label><br>
            <label><input type="checkbox" name="active_activities[]" value="Running" <?php echo (strpos($existing_preferences['active_activities'], 'Running') !== false) ? 'checked' : ''; ?>> Running</label><br>
            <label><input type="checkbox" name="active_activities[]" value="Swimming" <?php echo (strpos($existing_preferences['active_activities'], 'Swimming') !== false) ? 'checked' : ''; ?>> Swimming</label>
        </div>
    </div>

    <!-- Activități de Relaxare -->
    <div id="relaxation_activities_div" style="display: none;">
        <label for="relaxation_activities">Preferred relaxation activities (select multiple):</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="relaxation_activities[]" value="Spa" <?php echo (strpos($existing_preferences['relaxation_activities'], 'Spa') !== false) ? 'checked' : ''; ?>> Spa</label><br>
            <label><input type="checkbox" name="relaxation_activities[]" value="Dining at a Restaurant" <?php echo (strpos($existing_preferences['relaxation_activities'], 'Dining at a Restaurant') !== false) ? 'checked' : ''; ?>> Dining at a Restaurant</label><br>
            <label><input type="checkbox" name="relaxation_activities[]" value="Enjoying the Scenery" <?php echo (strpos($existing_preferences['relaxation_activities'], 'Enjoying the Scenery') !== false) ? 'checked' : ''; ?>> Enjoying the Scenery</label><br>
            <label><input type="checkbox" name="relaxation_activities[]" value="Reading" <?php echo (strpos($existing_preferences['relaxation_activities'], 'Reading') !== false) ? 'checked' : ''; ?>> Reading</label><br>
            <label><input type="checkbox" name="relaxation_activities[]" value="Nature Walks" <?php echo (strpos($existing_preferences['relaxation_activities'], 'Nature Walks') !== false) ? 'checked' : ''; ?>> Nature Walks</label>
        </div>
    </div>

    <label for="budget">Preferred budget:</label>
    <select name="budget" id="budget" required onchange="toggleBudgetInput()">
        <option value="">Select a budget</option>
        <option value="Low" <?php echo ($existing_preferences['budget'] == 'Low') ? 'selected' : ''; ?>>Low (300 - 800 EUR/week)</option>
        <option value="Medium" <?php echo ($existing_preferences['budget'] == 'Medium') ? 'selected' : ''; ?>>Medium (800 - 2000 EUR/week)</option>
        <option value="High" <?php echo ($existing_preferences['budget'] == 'High') ? 'selected' : ''; ?>>High (2000 EUR+/week)</option>
        <!-- Selectăm "Custom" dacă bugetul este numeric -->
        <option value="Custom" <?php echo (is_numeric($existing_preferences['budget'])) ? 'selected' : ''; ?>>Custom</option>
    </select>

    <!-- Afișăm input-ul pentru bugetul personalizat dacă bugetul este numeric -->
    <div id="customBudgetDiv" style="display: <?php echo (is_numeric($existing_preferences['budget'])) ? 'block' : 'none'; ?>;">
        <label for="custom_budget">Enter your custom budget:</label>
        <input type="number" id="custom_budget" name="custom_budget" min="0" placeholder="Enter your budget" 
            value="<?php echo is_numeric($existing_preferences['budget']) ? htmlspecialchars($existing_preferences['budget']) : ''; ?>"><br><br>
    </div>

    <label for="vacation_duration">Preferred vacation duration:</label>
    <select name="vacation_duration" id="vacation_duration" required>
        <option value="">Select a duration</option>
        <option value="Weekend" <?php echo ($existing_preferences['vacation_duration'] == 'Weekend') ? 'selected' : ''; ?>>Weekend</option>
        <option value="1 Week" <?php echo ($existing_preferences['vacation_duration'] == '1 Week') ? 'selected' : ''; ?>>1 Week</option>
        <option value="2 Weeks" <?php echo ($existing_preferences['vacation_duration'] == '2 Weeks') ? 'selected' : ''; ?>>2 Weeks</option>
        <option value="More" <?php echo ($existing_preferences['vacation_duration'] == 'More') ? 'selected' : ''; ?>>More</option>
    </select>

    <!-- Preferred Destinations -->
    <label for="preferred_destinations">Preferred destinations (select multiple):</label>
    <div class="checkbox-group">
        <label><input type="checkbox" name="preferred_destinations[]" value="Beach" <?php echo (strpos($existing_preferences['preferred_destinations'], 'Beach') !== false) ? 'checked' : ''; ?>> Beach</label>
        <label><input type="checkbox" name="preferred_destinations[]" value="Mountains" <?php echo (strpos($existing_preferences['preferred_destinations'], 'Mountains') !== false) ? 'checked' : ''; ?>> Mountains</label>
        <label><input type="checkbox" name="preferred_destinations[]" value="Big Cities" <?php echo (strpos($existing_preferences['preferred_destinations'], 'Big Cities') !== false) ? 'checked' : ''; ?>> Big Cities</label>
        <label><input type="checkbox" name="preferred_destinations[]" value="Small Villages" <?php echo (strpos($existing_preferences['preferred_destinations'], 'Small Villages') !== false) ? 'checked' : ''; ?>> Small Villages</label>
        <label><input type="checkbox" name="preferred_destinations[]" value="Exotic Places" <?php echo (strpos($existing_preferences['preferred_destinations'], 'Exotic Places') !== false) ? 'checked' : ''; ?>> Exotic Places</label>
    </div>

    <label for="preferred_transport">Preferred transport:</label>
    <select name="preferred_transport" id="preferred_transport" required>
        <option value="">Select an option</option>
        <option value="Plane" <?php echo ($existing_preferences['preferred_transport'] == 'Plane') ? 'selected' : ''; ?>>Plane</option>
        <option value="Train" <?php echo ($existing_preferences['preferred_transport'] == 'Train') ? 'selected' : ''; ?>>Train</option>
        <option value="Car" <?php echo ($existing_preferences['preferred_transport'] == 'Car') ? 'selected' : ''; ?>>Car</option>
        <option value="Ship" <?php echo ($existing_preferences['preferred_transport'] == 'Ship') ? 'selected' : ''; ?>>Ship</option>
    </select>

    <label for="preferred_accommodation">Preferred accommodation:</label>
    <select name="preferred_accommodation" id="preferred_accommodation">
        <option value="">Select an option</option>
        <option value="Hotel" <?php echo ($existing_preferences['preferred_accommodation'] == 'Hotel') ? 'selected' : ''; ?>>Hotel</option>
        <option value="Hostel" <?php echo ($existing_preferences['preferred_accommodation'] == 'Hostel') ? 'selected' : ''; ?>>Hostel</option>
        <option value="Camping" <?php echo ($existing_preferences['preferred_accommodation'] == 'Camping') ? 'selected' : ''; ?>>Camping</option>
        <option value="Homestay" <?php echo ($existing_preferences['preferred_accommodation'] == 'Homestay') ? 'selected' : ''; ?>>Homestay (AirBnB, Booking, etc)</option>
    </select>

    <label for="transport_class">Preferred transport class:</label>
    <select name="transport_class" id="transport_class">
        <option value="">Select an option</option>
        <option value="Economy" <?php echo ($existing_preferences['transport_class'] == 'Economy') ? 'selected' : ''; ?>>Economy</option>
        <option value="Business" <?php echo ($existing_preferences['transport_class'] == 'Business') ? 'selected' : ''; ?>>Business</option>
        <option value="First Class" <?php echo ($existing_preferences['transport_class'] == 'First Class') ? 'selected' : ''; ?>>First Class</option>
    </select>

    <label for="group_travel">Group Travel:</label>
    <select name="group_travel" id="group_travel" required>
        <option value="">Select an option</option>
        <option value="Solo" <?php echo ($existing_preferences['group_travel'] == 'Solo') ? 'selected' : ''; ?>>Solo</option>
        <option value="Couple" <?php echo ($existing_preferences['group_travel'] == 'Couple') ? 'selected' : ''; ?>>Couple</option>
        <option value="Family" <?php echo ($existing_preferences['group_travel'] == 'Family') ? 'selected' : ''; ?>>Family</option>
        <option value="Friends" <?php echo ($existing_preferences['group_travel'] == 'Friends') ? 'selected' : ''; ?>>Friends</option>
        <option value="Organized Group" <?php echo ($existing_preferences['group_travel'] == 'Organized Group') ? 'selected' : ''; ?>>Organized Group</option>
    </select>

    <!-- Preferred Travel Period -->
    <label for="travel_period">Preferred Travel Period:</label>
    <select name="travel_period" id="travel_period" required>
        <option value="">Select an option</option>
        <option value="Spring" <?php echo ($existing_preferences['travel_period'] == 'Spring') ? 'selected' : ''; ?>>Spring</option>
        <option value="Summer" <?php echo ($existing_preferences['travel_period'] == 'Summer') ? 'selected' : ''; ?>>Summer</option>
        <option value="Autumn" <?php echo ($existing_preferences['travel_period'] == 'Autumn') ? 'selected' : ''; ?>>Autumn</option>
        <option value="Winter" <?php echo ($existing_preferences['travel_period'] == 'Winter') ? 'selected' : ''; ?>>Winter</option>
    </select>


    <label for="dietary_restrictions">Dietary Restrictions:</label>
    <select name="dietary_restrictions" id="dietary_restrictions" required>
        <option value="">Select an option</option>
        <option value="None" <?php echo ($existing_preferences['dietary_restrictions'] == 'None') ? 'selected' : ''; ?>>None</option>
        <option value="Vegetarian" <?php echo ($existing_preferences['dietary_restrictions'] == 'Vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
        <option value="Vegan" <?php echo ($existing_preferences['dietary_restrictions'] == 'Vegan') ? 'selected' : ''; ?>>Vegan</option>
        <option value="Gluten-Free" <?php echo ($existing_preferences['dietary_restrictions'] == 'Gluten-Free') ? 'selected' : ''; ?>>Gluten-Free</option>
        <option value="Lactose-Free" <?php echo ($existing_preferences['dietary_restrictions'] == 'Lactose-Free') ? 'selected' : ''; ?>>Lactose-Free</option>
        <option value="Others" <?php echo ($existing_preferences['dietary_restrictions'] == 'Others') ? 'selected' : ''; ?>>Others</option>
    </select>

    <!-- Preferred Culinary Experience -->
    <label for="culinary_experience">Preferred Culinary Experience:</label>
    <div class="checkbox-group">
        <label><input type="checkbox" name="culinary_experience[]" value="Local Cuisine" <?php echo (strpos($existing_preferences['culinary_experience'], 'Local Cuisine') !== false) ? 'checked' : ''; ?>> Local Cuisine</label>
        <label><input type="checkbox" name="culinary_experience[]" value="Fine Dining" <?php echo (strpos($existing_preferences['culinary_experience'], 'Fine Dining') !== false) ? 'checked' : ''; ?>> Fine Dining</label>
        <label><input type="checkbox" name="culinary_experience[]" value="Street Food" <?php echo (strpos($existing_preferences['culinary_experience'], 'Street Food') !== false) ? 'checked' : ''; ?>> Street Food</label>
        <label><input type="checkbox" name="culinary_experience[]" value="Bar" <?php echo (strpos($existing_preferences['culinary_experience'], 'Bar') !== false) ? 'checked' : ''; ?>> Bar</label>
        <label><input type="checkbox" name="culinary_experience[]" value="Wine Tasting" <?php echo (strpos($existing_preferences['culinary_experience'], 'Wine Tasting') !== false) ? 'checked' : ''; ?>> Wine Tasting</label>
        <label><input type="checkbox" name="culinary_experience[]" value="Sweet Treats" <?php echo (strpos($existing_preferences['culinary_experience'], 'Sweet Treats') !== false) ? 'checked' : ''; ?>> Sweet Treats</label>
    </div>

    <input type="submit" value="Save Preferences">
    <button type="button" class="cancel-button" onclick="window.location.href='home.php';">Cancel</button>
</form>

<script>
   document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('form').onsubmit = function(event) {
            let vacationType = document.querySelectorAll('input[name="vacation_type[]"]:checked');
            let preferredDestinations = document.querySelectorAll('input[name="preferred_destinations[]"]:checked');
            let culinaryExperience = document.querySelectorAll('input[name="culinary_experience[]"]:checked');

            // Verificare checkbox-uri vacation_type
            if (vacationType.length === 0) {
                alert("Please select at least one preferred vacation.");
                event.preventDefault();
                return false;
            }

            // Verificare checkbox-uri preferredDestinations
            if (preferredDestinations.length === 0) {
                alert("Please select at least one preferred destination.");
                event.preventDefault();
                return false;
            }

            // Verificare checkbox-uri culinaryExperience
            if (culinaryExperience.length === 0) {
                alert("Please select at least one culinary preference.");
                event.preventDefault();
                return false;
            }

            return true; // Permite trimiterea formularului dacă toate verificările trec
        };
    });
</script>
<script>
// Funcție pentru a afișa/ascunde câmpul de introducere a bugetului personalizat
function toggleBudgetInput() {
    var budgetSelect = document.getElementById("budget");
    var customBudgetDiv = document.getElementById("customBudgetDiv");
    var customBudgetInput = document.getElementById("custom_budget_input");

    if (budgetSelect.value === "Custom") {
        customBudgetDiv.style.display = "block";  // Afișează câmpul pentru bugetul personalizat
    } else {
        customBudgetDiv.style.display = "none";  // Ascunde câmpul de buget personalizat
        customBudgetInput.value = "";  // Golește câmpul de buget personalizat când se ascunde
        document.getElementById("budgetError").style.display = "none"; // Ascunde eroarea
    }
}

// Validare la trimiterea formularului
document.querySelector('form').onsubmit = function(event) {
    var budgetSelect = document.getElementById("budget");
    var customBudgetInput = document.getElementById("custom_budget_input");
    var budgetError = document.getElementById("budgetError");

    // Verifică dacă opțiunea "Custom" este selectată și bugetul personalizat este valid
    if (budgetSelect.value === "Custom" && (customBudgetInput.value === "" || customBudgetInput.value <= 0)) {
        budgetError.style.display = "block";  // Afișează mesajul de eroare
        budgetError.innerText = "Please enter a valid custom budget greater than 0.";  // Mesaj clar de eroare
        event.preventDefault();  // Oprește trimiterea formularului
        return false;
    }

    // Dacă totul este în regulă, ascundem eroarea și permitem trimiterea
    budgetError.style.display = "none";
    return true;  // Permite trimiterea formularului dacă nu există erori
};
</script>
<script>
    // Function to show/hide activity sections based on selected vacation type
    function toggleActivitySections() {
        var waterActivitiesDiv = document.getElementById('water_activities');
        var adventureActivitiesDiv = document.getElementById('adventure_activities_div');
        var culturalActivitiesDiv = document.getElementById('cultural_activities_div');
        var cityBreakActivitiesDiv = document.getElementById('city_break_activities_div');
        var activeActivitiesDiv = document.getElementById('active_activities_div');
        var relaxationActivitiesDiv = document.getElementById('relaxation_activities_div');

        var isBeachChecked = document.querySelector('input[value="Beach"]').checked;
        var isAdventureChecked = document.querySelector('input[value="Adventure"]').checked;
        var isCulturalChecked = document.querySelector('input[value="Cultural Exploration"]').checked;
        var isCityBreakChecked = document.querySelector('input[value="City Break"]').checked;
        var isActiveChecked = document.querySelector('input[value="Active Vacation"]').checked;
        var isRelaxationChecked = document.querySelector('input[value="Relaxation"]').checked;

        // Display the relevant sections based on vacation type
        waterActivitiesDiv.style.display = isBeachChecked ? 'block' : 'none';
        adventureActivitiesDiv.style.display = isAdventureChecked ? 'block' : 'none';
        culturalActivitiesDiv.style.display = isCulturalChecked ? 'block' : 'none';
        cityBreakActivitiesDiv.style.display = isCityBreakChecked ? 'block' : 'none';
        activeActivitiesDiv.style.display = isActiveChecked ? 'block' : 'none';
        relaxationActivitiesDiv.style.display = isRelaxationChecked ? 'block' : 'none';
    }

    // Add event listeners to vacation type checkboxes
    document.querySelectorAll('input[name="vacation_type[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', toggleActivitySections);
    });

    // Trigger the function when the page loads to display the correct sections for pre-selected options
    window.onload = toggleActivitySections;
</script>
