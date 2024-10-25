<?php
session_start();
include("database.php"); // Include connection to the database

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch the current user's ID
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE user='$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$current_user_id = $user['id'];

// Fetch friends list, excluding the current user
$friends_sql = "SELECT users.id, users.user, users.profile_picture 
                FROM friends 
                JOIN users ON (friends.user1_id = users.id OR friends.user2_id = users.id)
                WHERE (friends.user1_id = $current_user_id OR friends.user2_id = $current_user_id)
                AND users.id != $current_user_id";  // Exclude current user from results

// Execute query and check for errors
$friends_result = mysqli_query($conn, $friends_sql);

if (!$friends_result) {
    // Display error if query failed
    echo "Error executing query: " . mysqli_error($conn);
    exit();
}

// Handle friend removal
if (isset($_POST['remove_friend'])) {
    $friend_id = intval($_POST['friend_id']);
    
    // Delete the friendship from the database
    $delete_sql = "DELETE FROM friends 
                   WHERE (user1_id = $current_user_id AND user2_id = $friend_id) 
                   OR (user1_id = $friend_id AND user2_id = $current_user_id)";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<p>Friend removed successfully!</p>";
        // Refresh the page to reflect changes
        header("Refresh:0");
    } else {
        echo "<p>Error removing friend: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends List</title>
    <style>
        /* Stiluri pentru pagină */
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
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .friend-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .friend-item a {
            text-decoration: none;
            color: #007bff;
            font-size: 16px;
            font-weight: bold;
        }

        /* Buton pentru trimiterea mesajelor */
        .message-btn {
            background-color: #2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            margin-left: auto; /* Muta butonul la dreapta */
            transition: background-color 0.3s ease;
        }

        .message-btn:hover {
            background-color: #218838;
            cursor: pointer;
        }

        /* Stil pentru butonul de ștergere */
        .remove-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin-left: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .remove-btn:hover {
            background-color: #c9302c;
            transform: scale(1.05);
        }

        .remove-btn:focus {
            outline: none;
        }

        /* Container for the action buttons (Home and Logout) */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px; /* Space between buttons */
        }
        .action-buttons button {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .action-buttons button.logout {
            background-color: #d9534f;
        }
        .action-buttons button.logout:hover {
            background-color: #c9302c;
        }
        .action-buttons button:hover {
            background-color: #31b0d5;
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
    <button onclick="window.location.href='search.php'">Search For New Friends</button>
    <button onclick="window.location.href='friend_requests.php'">View Friend Requests</button>
    <button onclick="window.location.href='home.php'">Home</button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>

<h1>Your Friends</h1>
<div class="friends-container">
    <?php if (mysqli_num_rows($friends_result) > 0): ?>
        <ul>
            <?php while ($friend = mysqli_fetch_assoc($friends_result)): ?>
                <li class="friend-item">
                    <?php
                    // Set default profile picture
                    $profile_picture = !empty($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'uploads/default-profile-picture.jpg';
                    ?>
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                    <a href="friend_profile.php?id=<?php echo $friend['id']; ?>">
                        <?php echo htmlspecialchars($friend['user']); ?>
                    </a>
                    <!-- Buton de mesaje -->
                    <a href="chat.php?friend_id=<?php echo $friend['id']; ?>" class="message-btn">Message</a>
                    <!-- Buton de ștergere prieten -->
                    <form action="friends.php" method="post" style="display:inline;">
                        <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($friend['id']); ?>">
                        <button type="submit" name="remove_friend" class="remove-btn">Remove Friend</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You have no friends yet.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
mysqli_close($conn);
?>
