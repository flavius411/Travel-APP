<?php
session_start();
include("database.php"); // Include connection to the database

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Handle search query
$search_results = [];
$search_performed = false; // Flag to check if search was performed

if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT id, user, profile_picture FROM users WHERE user LIKE '%$search_query%'";

    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $search_results[] = $row; // Store each result
        }
    }
    $search_performed = true; // Mark that a search was performed
}

// Handle sending friend requests
if (isset($_POST['send_request'])) {
    $to_user_id = intval($_POST['to_user_id']);
    $from_user_id = $user_id; // Assume you have the user's ID in session

    $insert_sql = "INSERT INTO friend_requests (from_user_id, to_user_id, status) VALUES ('$from_user_id', '$to_user_id', 'pending')";
    if (mysqli_query($conn, $insert_sql)) {
        $message = "Friend request sent!";
    } else {
        $message = "Error sending request: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Friends</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px; /* Mutat în dreapta sus */
            display: flex;
            gap: 10px; /* Spațiu între butoane */
        }
        .action-buttons button {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px; /* Ajustat padding pentru butoane */
            cursor: pointer;
        }
        .action-buttons button.logout {
            background-color: #d9534f;
        }
        .action-buttons button:hover {
            background-color: #31b0d5;
        }
        .action-buttons button.logout:hover {
            background-color: #c9302c;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Lățimea redusă */
            margin: 80px auto 0; /* Centrat pe pagină */
        }
        h1, h2 {
            color: #333;
        }
        input[type="text"], button[type="submit"] {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
        .user-item {
            display: flex;
            align-items: center;
            margin: 5px 0; /* Margină redusă */
        }
        .user-item img {
            width: 50px; /* Ajustează dimensiunea imaginii după necesitate */
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .user-item button {
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .user-item button:hover {
            background-color: #4cae4c;
        }
        .message-button {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 50%; /* Creează un buton rotund */
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-button:hover {
            background-color: #31b0d5;
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="message-button" onclick="window.location.href='messages.php'">✉</button>
        <button onclick="window.location.href='friend_requests.php'">View Friend Requests</button>
        <button onclick="window.location.href='home.php'">Home</button>
        <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
    </div>
    <div class="form-container">
        <h1>Search for Friends</h1>
        <form action="search.php" method="GET">
            <input type="text" name="search" placeholder="Search for friends" required>
            <button type="submit">Search</button>
        </form>

        <?php if (isset($message)) echo "<p>$message</p>"; ?>

        <h2>Search Results</h2>
        <?php if ($search_performed): // Check if the search was performed ?>
            <?php if (!empty($search_results)): ?>
                <ul>
                    <?php foreach ($search_results as $user): ?>
                        <li class="user-item">
                            <?php
                            // Set default profile picture
                            $profile_picture = !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'uploads/default-profile-picture.jpg';
                            ?>
                            <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                            <span><?php echo htmlspecialchars($user['user']); ?></span>
                            <form action="search.php" method="post">
                                <input type="hidden" name="to_user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                <button type="submit" name="send_request">Send Friend Request</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Please enter a search term to see results.</p>
        <?php endif; ?>
    </div>
</body>
</html>
