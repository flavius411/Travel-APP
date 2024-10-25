<?php
session_start();
include("database.php");
include("destinations.php");
include("header_buttons.php");
include("external_links.php");
include("api_keys.php");

require 'vendor/autoload.php';

use GuzzleHttp\Client;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


function getDestinationDetailsFromAPI($locationId) {
    $client = new Client();
    $apiKey = TRIPADVISOR_API_KEY;
    $url = "https://api.content.tripadvisor.com/api/v1/location/$locationId/details?language=en&currency=USD&key=$apiKey";

    try {
        $response = $client->request('GET', $url, [
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (Exception $e) {
        return null;
    }
}

function getDestinationDetail($conn, $destinationName) {
    $sql = "SELECT * FROM vacation_destinations WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $destinationName);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

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
        return !empty($photos['data']) ? $photos['data'] : [];
    } catch (Exception $e) {
        return [];
    }
}

function getAttractionsFromLocation($locationName) {
    $client = new Client();
    $apiKey = TRIPADVISOR_API_KEY;
    $url = "https://api.content.tripadvisor.com/api/v1/location/search?searchQuery=" . urlencode($locationName) . "&category=attractions&language=en&key=$apiKey";

    try {
        $response = $client->request('GET', $url, [
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (Exception $e) {
        return [];
    }
}

if (isset($_GET['name'])) {
    $destinationName = $_GET['name'];

    $destinationDetails = getDestinationDetail($conn, $destinationName);

    if (!$destinationDetails) {
        echo "<p>Destination not found.</p>";
        exit();
    }

    $locationId = $destinationDetails['location_id']; 
    $tripAdvisorDetails = getDestinationDetailsFromAPI($locationId);

    if (!$tripAdvisorDetails) {
        echo "<p>Unable to fetch details from TripAdvisor.</p>";
        exit();
    }

    $photos = getPhotosFromLocation($locationId);
    $attractions = getAttractionsFromLocation($destinationName);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destinationDetails['name']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Hero Section */
        .hero {
            height: 400px;
            background: url('<?php echo htmlspecialchars($photos[0]['images']['large']['url'] ?? 'default-hero.jpg'); ?>') no-repeat center center/cover;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero h1 {
            font-size: 3.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.7);
        }

        /* Main content */
        .content {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .description,
        .info {
            flex: 1;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .description h2, .info h2 {
            color: #008080;
            margin-bottom: 15px;
        }

        .description p {
            line-height: 1.6;
        }

        .info p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .info p strong {
            color: #333;
        }

        /* Photos Section */
        .photos-section {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .photos-section h2 {
            color: #008080;
            margin-bottom: 15px;
        }

        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .photos-grid img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .photos-grid img:hover {
            transform: scale(1.05);
        }

        .attractions-section{
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .attractions-section h2 {
            color: #008080;
            margin-bottom: 15px;
        }

        .attractions-section ul {
            list-style-type: none;
            padding: 0;
        }

        .attractions-section ul li {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .attractions-section ul li i {
            color: #008080;
            margin-right: 10px;
        }

        .external-link{
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .external-link h2 {
            color: #008080;
            margin-bottom: 15px;
        }

        .external-link ul {
            list-style-type: none;
            padding: 0;
        }

        .external-link ul li {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .external-link ul li i {
            color: #008080;
            margin-right: 10px;
        }


        /* Activities Section */
        .activities-section {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .activities-section h2 {
            color: #008080;
            margin-bottom: 15px;
        }

        .activities-section ul {
            list-style-type: none;
            padding: 0;
        }

        .activities-section ul li {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .activities-section ul li i {
            color: #008080;
            margin-right: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }

            .hero h1 {
                font-size: 2.5rem;
            }
        }
        .travel-button {
            display: inline-block;
            background-color: #5bc0de; /* Culoarea de fundal */
            color: white; /* Culoarea textului */
            padding: 10px 20px; /* Spațiere interioară */
            text-decoration: none; /* Elimină sublinierea textului */
            border-radius: 5px; /* Colțuri rotunjite */
            font-size: 16px; /* Dimensiunea textului */
            transition: background-color 0.3s ease; /* Trecere lină la hover */
        }
        
        .travel-button:hover {
            background-color: #31b0d5; /* Culoarea de fundal la hover */
        }
    </style>
</head>
<body>
    <div class="container">
    <!-- Hero Section -->
    <div class="hero">
        <h1><?php echo htmlspecialchars($destinationName); ?></h1>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Description Section -->
        <div class="description">
            <h2>Description</h2>
            <p><?php echo htmlspecialchars($tripAdvisorDetails['description'] ?? 'No description available.'); ?></p>
        </div>
    </div>

    <!-- Activities Section -->
    <div class="activities-section">
        <h2>Activities that you selected</h2>
        <ul>
            <?php
                $activitiesJson = $destinationDetails['activities'] ?? '{}';
                $activitiesArray = json_decode($activitiesJson, true);

                if (!empty($activitiesArray) && is_array($activitiesArray)) {
                    foreach ($activitiesArray as $activity => $rating) {
                        echo "<li>" . htmlspecialchars($activity) . "</li>"; // Doar activitatea, fără rating
                    }
                } else {
                    echo "<li>No activities available.</li>";
                }
            ?>
        </ul>
    </div>

    <!-- Attractions Section -->
    <div class="attractions-section">
        <h2>Main attractions</h2>
        <ul>
            <?php
            if (!empty($attractions['data'])) {
                foreach ($attractions['data'] as $attraction) {
                    echo "<li>" . htmlspecialchars($attraction['name']) . "</li>";
                }
            } else {
                echo "<li>No attractions available.</li>";
            }
            ?>
        </ul>
    </div>
      
        <div class="external-link">
            <h2>Travel Information</h2>
            <?php
                // Obține linkul extern pentru destinația curentă
                $externalLink = getExternalLink($destinationName);
                echo "<a href='$externalLink' class='travel-button'>Travel by plane from the nearest Airport</a>";
            ?>
        </div>

    <!-- Photos Section -->
    <div class="photos-section">
        <h2>Photos</h2>
        <div class="photos-grid">
            <?php
            if (!empty($photos)) {
                foreach ($photos as $photo) {
                    if (isset($photo['images']['large']['url'])) {
                        echo "<a href='" . htmlspecialchars($photo['images']['large']['url']) . "' target='_blank'>
                                  <img src='" . htmlspecialchars($photo['images']['large']['url']) . "' alt='Destination Photo'>
                              </a>";
                    }
                }
            } else {
                echo "<p>No photos available.</p>";
            }
            ?>
        </div>
    </div>

</div>


<?php
mysqli_close($conn);
?>
