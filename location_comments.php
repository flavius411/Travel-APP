<?php
session_start();
include("database.php"); // Include conexiunea la baza de date
include("header_buttons.php");

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obține ID-ul locației din URL
if (isset($_GET['id'])) {
    $location_id = intval($_GET['id']);
    $sql = "SELECT * FROM locations WHERE location_id='$location_id'";
    $location_result = mysqli_query($conn, $sql);

    if ($location_result && mysqli_num_rows($location_result) > 0) {
        $location = mysqli_fetch_assoc($location_result);
    } else {
        echo "Locația nu a fost găsită!";
        exit();
    }
} else {
    echo "Niciun ID de locație specificat.";
    exit();
}

// Procesare formular de comentarii
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']); // Escape input pentru prevenirea SQL injection

    // Inserare comentariu în baza de date
    $sql = "INSERT INTO comments (location_id, user_id, comment) VALUES ('$location_id', '$user_id', '$comment')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: location_comments.php?id=$location_id"); // Redirecționează utilizatorul înapoi la detaliile locației
        exit();
    } else {
        echo "Eroare: " . mysqli_error($conn);
    }
}

// Interogare pentru a obține comentariile și imaginea utilizatorului
$sql = "SELECT comments.comment, users.user, users.id as user_id, users.profile_picture 
        FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE comments.location_id = '$location_id' 
        ORDER BY comments.created_at DESC";
$comments_result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($location['location_name']); ?> - Comentarii</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .location-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .location-image {
            max-width: 100%; /* Se asigură că imaginea locației nu depășește lățimea containerului */
            height: auto;
            border-radius: 10px;
            margin-bottom: 15px; /* Spațiu sub imagine */
        }
        .comment-form {
            margin-top: 20px;
        }
        .comment-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: none;
            margin-bottom: 10px;
        }
        .comment-form input[type="submit"] {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .comment-form input[type="submit"]:hover {
            background-color: #31b0d5;
        }
        .comment-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }
        .comment-item:last-child {
            border-bottom: none;
        }
        .comment-user {
            font-weight: bold;
            margin-right: 10px;
        }
        .comment-item img {
            width: 40px; /* Dimensiune imagine utilizator */
            height: 40px; /* Dimensiune imagine utilizator */
            border-radius: 50%; /* Formă rotundă */
            margin-right: 10px; /* Spațiu între imagine și text */
        }
        .comment-button {
            display: inline-block;
            margin-top: 20px;
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .comment-button:hover {
            background-color: #31b0d5;
        }
    </style>
</head>
<body>

<div class="location-container">
    <h1><?php echo htmlspecialchars($location['location_name']); ?></h1>
    <img src="<?php echo htmlspecialchars($location['image_path']); ?>" alt="<?php echo htmlspecialchars($location['location_name']); ?>" class="location-image"> <!-- Imaginea locației -->
    <p><?php echo htmlspecialchars($location['description']); ?></p>
    
    <h2>Adaugă un comentariu:</h2>
    <form action="location_comments.php?id=<?php echo $location_id; ?>" method="post" class="comment-form">
        <textarea name="comment" rows="3" placeholder="Scrie un comentariu..." required></textarea>
        <input type="submit" value="Adaugă comentariul">
    </form>
    
    <h2>Comentarii:</h2>
    <?php
    if (mysqli_num_rows($comments_result) > 0) {
        while ($row = mysqli_fetch_assoc($comments_result)) {
            // Link către profilul utilizatorului
            $profile_picture = !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'uploads/default-profile-picture.jpg';
            echo "<div class='comment-item'>
                    <img src='$profile_picture' alt='Profil utilizator'>
                    <span class='comment-user'>
                        <a href='friend_profile.php?id=" . htmlspecialchars($row['user_id']) . "'>" . htmlspecialchars($row['user']) . "</a>:
                    </span> 
                    " . htmlspecialchars($row['comment']) . "
                  </div>";
        }
    } else {
        echo "<p>Nu există comentarii pentru această locație.</p>";
    }
    ?>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
