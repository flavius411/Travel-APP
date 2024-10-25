<?php
session_start();
include("database.php"); // Include connection to the database
include("header_buttons.php");

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user's ID
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE user='$username'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $current_user_id = $user['id'];
} else {
    // Redirect if user ID is not found
    header("Location: login.php");
    exit();
}

// Fetch friends list
$friends_sql = "SELECT id, user, profile_picture FROM users WHERE id != $current_user_id";
$friends_result = mysqli_query($conn, $friends_sql);

if (!$friends_result) {
    // Handle error in query
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start New Conversation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        
        .friends-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
        }

        .friend-item {
            display: flex; /* Flexbox for alignment */
            align-items: center; /* Center align items */
            margin-bottom: 10px;
        }

        .friend-item img {
            width: 50px; /* Dimensiunea imaginii */
            height: 50px;
            border-radius: 50%; /* Colțuri rotunjite pentru imagine */
            margin-right: 10px; /* Spațiu între imagine și nume */
        }

        .friend-item a {
            text-decoration: none; /* Elimină sublinierea */
            color: #007bff; /* Culoare text */
            font-size: 18px; /* Dimensiunea textului */
            font-weight: bold; /* Îngroșează textul */
        }
    </style>
</head>
<body>

<h1>Start New Conversation</h1>
<div class="friends-container">
    <h2>Select a friend to chat with:</h2>
    <ul>
        <?php while ($friend = mysqli_fetch_assoc($friends_result)): ?>
            <li class="friend-item">
                <img src="<?php echo !empty($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'uploads/default-profile-picture.jpg'; ?>" alt="Profile Picture">
                <a href="chat.php?friend_id=<?php echo $friend['id']; ?>">
                    <?php echo htmlspecialchars($friend['user']); ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>

<?php
mysqli_close($conn);
?>
