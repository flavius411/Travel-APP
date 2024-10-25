<?php
session_start();
include("header_buttons.php");
include("database.php");
include("top_destinations.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


// Verificăm dacă preferințele utilizatorului sunt completate
$user_id = $_SESSION['user_id']; 
$sql = "SELECT vacation_type FROM preferences WHERE user_id = '$user_id'"; 
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Verificăm dacă preferințele sunt completate
$preferences_filled = false;
if ($user && !empty($user['vacation_type'])) {
    $preferences_filled = true; // Preferințele sunt completate
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center the content horizontally */
        }

        .welcome-message {
            font-size: 20px;
            color: #333;
            margin-bottom: 30px;
            text-align: left;
            position: absolute;
            top: 20px;
            left: 50px;
            display: block; /* Întotdeauna arată mesajul de bun venit */
        }

        .intro-container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
            display: none; /* Ascuns în mod implicit */
        }

        .ready-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        .ready-button:hover {
            background-color: #45a049;
        }

        .pref-saved {
            display: none; /* Ascuns în mod implicit */
            margin-top: 20px; /* Margine pentru distanțare */
            color: #4CAF50; /* Culoare verde pentru mesajul de succes */
        }
    </style>
    <script>
        // Functie care decide ce se întâmplă când dai click pe "Ready"
        function showForm() {
            <?php if ($preferences_filled): ?>
                // Dacă preferințele sunt completate, afișăm mesajul de succes
                document.querySelector('.intro-container').style.display = 'none';
                document.querySelector('.pref-saved').style.display = 'block';
            <?php else: ?>
                // Dacă preferințele nu sunt completate, redirecționăm către pagina cu formularul
                window.location.href = 'save_preferences.php'; 
            <?php endif; ?>
        }

        // Automatizează afișarea intro-ului dacă preferințele nu sunt completate
        window.onload = function() {
            // Afișăm mesajul de bun venit
            document.querySelector('.welcome-message').style.display = 'block';
            <?php if ($preferences_filled): ?>
                // Dacă preferințele sunt completate, afișăm mesajul de salvare a preferințelor
                document.querySelector('.pref-saved').style.display = 'block'; 
            <?php else: ?>
                // Dacă preferințele nu sunt completate, afișăm formularul
                document.querySelector('.intro-container').style.display = 'block'; 
            <?php endif; ?>
        };
    </script>
</head>
<body>

<!-- Mesaj de bun venit în colțul din stânga sus -->
<div class="welcome-message">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
</div>

<!-- Container intro -->
<div class="intro-container">
    <h2>Are you ready to take the first step towards a unique vacation?</h2>
    <button class="ready-button" onclick="showForm()">Ready</button>
</div>

<!-- Mesaj de confirmare pentru salvarea preferințelor -->
<div class="pref-saved">
    <p>*Tip: You can always change your preferences in your profile!</p>
</div>

</body>
</html>
