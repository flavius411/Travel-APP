<?php
function fetchTripAdvisorData($endpoint, $apiKey, $params = []) {
    // URL-ul de bază pentru API-ul TripAdvisor
    $baseUrl = "https://api.tripadvisor.com/v2/";

    // Combină URL-ul de bază cu endpoint-ul dorit
    $url = $baseUrl . $endpoint;

    // Adaugă parametrii la URL
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    // Inițializează sesiunea cURL
    $ch = curl_init();

    // Setează opțiunile cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "Authorization: Bearer $apiKey"
    ]);

    // Execută cererea
    $response = curl_exec($ch);
    
    // Verifică dacă a fost o eroare
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        return null;
    }

    // Închide sesiunea cURL
    curl_close($ch);

    // Decodifică răspunsul JSON
    return json_decode($response, true);
}

// Exemplu de utilizare
$apiKey = "YOUR_API_KEY";  // Înlocuiește cu cheia ta API
$endpoint = "locations/search";  // Endpoint pentru căutarea locațiilor
$params = [
    "query" => "Barcelona",  // Exemplu de interogare
    "limit" => 5  // Numărul maxim de rezultate dorite
];

$data = fetchTripAdvisorData($endpoint, $apiKey, $params);

if ($data) {
    print_r($data);  // Afișează rezultatele obținute
}
?>
