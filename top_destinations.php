<?php

include("database.php");
include("destinations.php");
include("external_links.php");
include("api_keys.php");


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include autoload pentru Guzzle
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

// Funcția pentru a obține destinațiile din baza de date
function getDestinationsFromDB($conn) {
    $sql = "SELECT name, beach, city_break, activities, culture, adventure, relaxation, budget, location_id FROM vacation_destinations";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo "Eroare la interogarea bazei de date: " . mysqli_error($conn) . "<br>";
        return [];
    }

    $destinations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities = json_decode($row['activities'], true) ?? [];
        $culture = json_decode($row['culture'], true) ?? [];
        $adventure = json_decode($row['adventure'], true) ?? [];
        $relaxation = json_decode($row['relaxation'], true) ?? [];

        $destination = new VacationDestination(
            $row['name'],
            (bool)$row['beach'],
            (bool)$row['city_break'],
            $activities,
            $culture,
            $adventure,
            $relaxation,
            (int)$row['budget'],
            $row['location_id']
        );
        $destinations[] = $destination;
    }
    return $destinations;
}

// Funcția pentru a obține pozele folosind location_id de la TripAdvisor API
function getPhotosFromLocation($locationId) {
    $client = new Client();
    $apiKey = TRIPADVISOR_API_KEY;
    $url = "https://api.content.tripadvisor.com/api/v1/location/$locationId/photos";

    try {
        $response = $client->request('GET', $url, [
            'query' => [
                'language' => 'en',
                'key' => $apiKey,
            ],
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        $photos = json_decode($response->getBody(), true);

        // Verificăm dacă avem date valide și selectăm prima imagine din lista primită
        if (!empty($photos['data']) && isset($photos['data'][0]['images']['large']['url'])) {
            return $photos['data'][0]['images']['large']['url'];
        } else {
            return 'default-image-url.jpg'; // Imagine fallback în caz că nu există poze
        }
    } catch (Exception $e) {
        return 'default-image-url.jpg'; // Imagine fallback în caz de eroare
    }
}

// Funcția pentru afișarea destinațiilor top
function displayTopDestinations($destinations) {
    echo "<div class='destination-grid'>"; // Start of grid container
    foreach ($destinations as $destination) {
        $locationId = $destination->location_id;

        // Obținem poza pentru fiecare destinație folosind location_id
        $imageUrl = $locationId ? getPhotosFromLocation($locationId) : 'default-image-url.jpg';

        // Obține linkul extern
        $link = getExternalLink($destination->name); // Utilizează funcția pentru a obține linkul

        // Afișăm cardul cu detalii și imagine
        echo "<div class='location-card'>";
        echo "<a href='destination_detail.php?name=" . urlencode($destination->name) . "'>";
        echo "<div class='image-container'>";
        echo "<img src='$imageUrl' alt='" . htmlspecialchars($destination->name) . "'>";
        echo "</div>";
        echo "<div class='card-content'>";
        echo "<h3>" . htmlspecialchars($destination->name) . "</h3>";
        echo "<p>Budget: " . htmlspecialchars($destination->budget) . " EUR</p>";
        echo "<a href='$link' class='external-link'>Travel by plane</a>"; 
        echo "</div>";
        echo "</a>";
        echo "</div>";
    }
    echo "</div>"; // End of grid container
}

// Obținem preferințele utilizatorului
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT 
        vacation_type, 
        water_activities, 
        adventure_activities, 
        city_break_activities, 
        cultural_activities, 
        relaxation_activities, 
        budget 
    FROM preferences 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$preferences_filled = !empty($user['vacation_type']) || 
                      !empty($user['water_activities']) || 
                      !empty($user['adventure_activities']) || 
                      !empty($user['city_break_activities']) || 
                      !empty($user['cultural_activities']) || 
                      !empty($user['relaxation_activities']) || 
                      !empty($user['budget']);

$preferences = [
    'vacation_type' => !empty($user['vacation_type']) ? array_map('trim', explode(',', $user['vacation_type'])) : [],
    'water_activities' => !empty($user['water_activities']) ? array_map('trim', explode(',', $user['water_activities'])) : [],
    'adventure_activities' => !empty($user['adventure_activities']) ? array_map('trim', explode(',', $user['adventure_activities'])) : [],
    'city_break_activities' => !empty($user['city_break_activities']) ? array_map('trim', explode(',', $user['city_break_activities'])) : [],
    'cultural_activities' => !empty($user['cultural_activities']) ? array_map('trim', explode(',', $user['cultural_activities'])) : [],
    'relaxation_activities' => !empty($user['relaxation_activities']) ? array_map('trim', explode(',', $user['relaxation_activities'])) : [],
    'budget' => !empty($user['budget']) ? (int)$user['budget'] : 0
];

// Dacă preferințele sunt completate, calculăm scorurile și afișăm destinațiile top
if ($preferences_filled) {
    $destinations = getDestinationsFromDB($conn);

    $destination_scores = [];
    foreach ($destinations as $destination) {
        $score = $destination->calculateScore($preferences);
        $destination_scores[$destination->name] = $score;
    }

    arsort($destination_scores);
    $topDestinationNames = array_slice(array_keys($destination_scores), 0, 3); // Cele mai bune 3 destinații

    // Colectăm obiectele destinație corespunzătoare
    $topDestinations = [];
    foreach ($topDestinationNames as $name) {
        foreach ($destinations as $destination) {
            if ($destination->name === $name) {
                $topDestinations[] = $destination;
                break;
            }
        }
    }
    echo "<h2>Based on your saved preferences, we recommend:</h2> <br> <h3>Top 3 Destinations</h3>";
    displayTopDestinations($topDestinations);
} else {
    echo "<p></p>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }
    h3 {
        text-align: center;
        color: #333;
    }
    .destination-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .location-card {
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.2s ease;
    }
    .location-card:hover {
        transform: translateY(-10px);
    }
    .image-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .location-card:hover .image-container img {
        transform: scale(1.05);
    }
    .card-content {
        padding: 15px;
        text-align: center;
    }
    .card-content a {
    text-decoration: none; /* Elimină sublinierea */
    color: inherit; /* Asigură că culoarea textului rămâne aceeași */
    }

    .card-content h3 {
        font-size: 20px;
        color: #333;
        margin-bottom: 10px;
    }
    .card-content p {
        font-size: 14px;
        color: #777;
    }
</style>

</head>
<body>
    
</body>
</html>