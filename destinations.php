
<?php
class VacationDestination {
    public $name;
    public $beach; // boolean
    public $city_break; // boolean
    public $activities; // array
    public $culture; // array
    public $adventure; // array
    public $relaxation; // array
    public $budget; // integer
    public $location_id;

    // Constructor for VacationDestination
    public function __construct($name, $beach, $city_break, $activities, $culture, $adventure, $relaxation, $budget, $location_id) {
        $this->name = trim(htmlspecialchars($name)); 
        $this->beach = (bool)$beach; // Convert to boolean
        $this->city_break = (bool)$city_break; // Convert to boolean
        $this->activities = $activities;
        $this->culture = $culture;
        $this->adventure = $adventure;
        $this->relaxation = $relaxation;
        $this->budget = $budget;
        $this->location_id = $location_id; 
    }
    public function calculateScore($preferences) {
        $score = 0;
    
        // 1. Potrivirea cu tipul de vacanță (plajă sau city break)
        if (in_array('beach', $preferences['vacation_type']) && $this->beach) {
            $score += 20; // punctaj mare pentru potrivirea tipului de vacanță (plajă)
        }
        if (in_array('city_break', $preferences['vacation_type']) && $this->city_break) {
            $score += 20; // punctaj mare pentru potrivirea tipului de vacanță (city break)
        }
    
        // 2. Potrivirea activităților acvatice (activities)
        $score += $this->matchActivities($this->activities, $preferences['water_activities']);
    
        // 3. Potrivirea activităților de aventură (adventure)
        $score += $this->matchActivities($this->adventure, $preferences['adventure_activities']);
    
        // 4. Potrivirea activităților culturale (culture)
        $score += $this->matchActivities($this->culture, $preferences['cultural_activities']);
    
        // 5. Potrivirea activităților de relaxare (relaxation)
        $score += $this->matchActivities($this->relaxation, $preferences['relaxation_activities']);
    
        // Returnează scorul total calculat pentru această destinație
        return $score;
    }
    
    // Funcție auxiliară pentru a potrivi activitățile preferate cu cele disponibile la destinație
    private function matchActivities($destinationActivities, $userPreferences) {
        $totalScore = 0;
    
        foreach ($userPreferences as $activity) {
            if (isset($destinationActivities[$activity])) {
                $totalScore += $destinationActivities[$activity]; // Adaugă scorul activității dacă există
            }
        }
    
        return $totalScore;
    }
    

    // Function to insert data into MySQL database
    public function insertDestinationToDB() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "businessdb";

        // Create connection to MySQL
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare an SQL statement to insert data
        $stmt = $conn->prepare("INSERT INTO vacation_destinations (name, beach, city_break, activities, culture, adventure, relaxation, budget, location_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        // Convert arrays to JSON before inserting
        $activities_json = json_encode($this->activities);
        $culture_json = json_encode($this->culture);
        $adventure_json = json_encode($this->adventure);
        $relaxation_json = json_encode($this->relaxation);

        // Bind parameters to the SQL statement
        $stmt->bind_param(
            "siissssii", 
            $this->name,
            $this->beach,
            $this->city_break,
            $activities_json,
            $culture_json,
            $adventure_json,
            $relaxation_json,
            $this->budget,
            $this->location_id
        );

        // Execute the statement and check for errors
        if ($stmt->execute()) {
            echo "New destination inserted successfully: " . $this->name . "<br>";
        } else {
            echo "Error: " . $stmt->error . "<br>";
        }

        // Close connection
        $stmt->close();
        $conn->close();
    }
}

// Definirea destinațiilor
$destinations = array(
    new VacationDestination(
        "Hurghada, Egypt", 
        true,  // Beach
        false, // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 3
        ), 
        array(
            "Historical Tours" => 3,
            "Museum Visits" => 4
        ), 
        array(
            "Hiking" => 4, 
            "ATV Riding" => 3
        ), 
        array(
            "Spa" => 4, 
            "Enjoying the Scenery" => 3, 
            "Nature Walks" => 4
        ), 
        3,
        297549
    ),
    new VacationDestination(
        "Sharm El Sheikh, Egypt", 
        true,  // Beach
        false, // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Kayaking" => 3
        ), 
        array(
            "Museum Visits" => 4,
            "Cultural Festivals" => 3
        ), 
        array(
            "Hiking" => 4,
            "Mountain Biking" => 3
        ), 
        array(
            "Spa" => 4, 
            "Dining at a Restaurant" => 4,
            "Reading" => 3
        ), 
        2,
        297555 // Budget
    ),
    new VacationDestination(
        "Marsa Alam, Egypt", 
        true,  // Beach
        false, // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 4
        ), 
        array(
            "Historical Tours" => 3,
            "Local Cuisine Tasting" => 4
        ), 
        array(
            "Hiking" => 4,
            "ATV Riding" => 3,
            "Rock Climbing" => 4
        ), 
        array(
            "Spa" => 4, 
            "Nature Walks" => 3, 
            "Dining at a Restaurant" => 4
        ), 
        3,
        311425
    ),
    new VacationDestination(
        "Crete, Greece", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Kayaking" => 3
        ), 
        array(
            "Museum Visits" => 4,
            "Historical Tours" => 4,
            "Local Markets" => 3
        ), 
        array(
            "Hiking" => 4, 
            "Cycling" => 3
        ), 
        array(
            "Spa" => 4, 
            "Enjoying the Scenery" => 3, 
            "Food Tours" => 4,
            "Nightlife" => 4
        ), 
        4,
        189413
    ),
    new VacationDestination(
        "Rhodes, Greece", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 3
        ), 
        array(
            "Museum Visits" => 4,
            "Art Exhibitions" => 4
        ), 
        array(
            "Hiking" => 4, 
            "Rock Climbing" => 3
        ), 
        array(
            "Spa" => 4, 
            "Dining at a Restaurant" => 4,
            "Nightlife" => 4
        ), 
        3,
        189449
    ),
    new VacationDestination(
        "Zakynthos, Greece", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Surfing" => 3
        ), 
        array(
            "Historical Tours" => 4,
            "Cultural Festivals" => 3
        ), 
        array(
            "Hiking" => 4,
            "Cycling" => 3
        ), 
        array(
            "Spa" => 4, 
            "Enjoying the Scenery" => 3, 
            "Reading" => 4,
            "Nightlife" => 4
        ), 
        3, // Budget
        189462
    ),
    new VacationDestination(
        "Corfu, Greece", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 3
        ), 
        array(
            "Museum Visits" => 4,
            "Historical Tours" => 4
        ), 
        array(
            "Hiking" => 4,
            "Mountain Biking" => 3,
            "Zip Lining" => 3,
            "Amusement Parks" => 3
        ), 
        array(
            "Spa" => 4, 
            "Dining at a Restaurant" => 4,
            "Nightlife" => 4
        ), 
        4, // Budget
        189458
    ),
    new VacationDestination(
        "Antalya, Turkey", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 3
        ), 
        array(
            "Cultural Festivals" => 3,
            "Historical Tours" => 4
        ), 
        array(
            "Hiking" => 4, 
            "ATV Riding" => 3,
            "Amusement Parks" => 3
        ), 
        array(
            "Spa" => 4, 
            "Dining at a Restaurant" => 4,
            "Nightlife" => 3
        ), 
        4, // Budget
        297962
    ),
    new VacationDestination(
        "Bodrum, Turkey", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Kayaking" => 3
        ), 
        array(
            "Cultural Festivals" => 3,
            "Museum Visits" => 3
        ), 
        array(
            "Hiking" => 4, 
            "Cycling" => 3
        ), 
        array(
            "Spa" => 4, 
            "Enjoying the Scenery" => 3, 
            "Food Tours" => 4
        ), 
        3,
        298658
    ),
    new VacationDestination(
        "Kusadasi, Turkey", 
        true,  // Beach
        true,  // City Break
        array(
            "Snorkeling" => 4, 
            "Scuba Diving" => 5,
            "Jet Skiing" => 3
        ), 
        array(
            "Museum Visits" => 4,
            "Historical Tours" => 4,
            "Local Markets" => 3
        ), 
        array(
            "Hiking" => 4, 
            "Mountain Biking" => 3
        ), 
        array(
            "Spa" => 4, 
            "Dining at a Restaurant" => 4,
            "Sightseeing" => 3
        ), 
        3,
        297972
    )
);

/*
// Inserează fiecare destinație în baza de date
foreach ($destinations as $destination) {
    $destination->insertDestinationToDB();
}
*/

?>