<?php
session_start();
include("database.php"); // Include connection to the database

// Redirect to login page if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get current user's ID
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE user='$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$current_user_id = $user['id'];

// Fetch friends with whom the user has had a conversation (assuming there's a messages table)
$friends_sql = "SELECT DISTINCT u.id, u.user, u.profile_picture 
                FROM users u
                JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id)
                WHERE (m.sender_id = $current_user_id OR m.receiver_id = $current_user_id) 
                AND u.id != $current_user_id";
$friends_result = mysqli_query($conn, $friends_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Conversations</title>
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

        .conversation-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px; /* Adaugă padding pentru a face elementele mai confortabile */
            border-bottom: 1px solid #ddd; /* Delimitare între utilizatori */
        }

        .conversation-item img {
            width: 50px; 
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .conversation-item span {
            flex-grow: 1; /* Face numele utilizatorului să ocupe tot spațiul disponibil */
        }

        .conversation-item a {
            text-decoration: none; /* Elimină sublinierea */
            color: #007bff; /* Culoare text pentru numele utilizatorului */
            font-size: 18px; /* Dimensiunea textului (ajustat) */
            font-weight: bold; /* Îngroșează textul */
        }
        
        .continue-chat-btn {
            background-color: white; /* Fundal alb */
            color: #5bc0de; /* Text albastru */
            border: 2px solid #5bc0de; /* Bordură albastră pentru buton */
            border-radius: 5px; /* Colțuri rotunjite */
            padding: 10px 20px; /* Padding */
            cursor: pointer; /* Cursor pointer la hover */
            text-decoration: none; /* Elimină sublinierea textului */
            display: inline-block; /* Asigură că butonul se comportă ca un bloc inline */
            font-weight: bold; /* Text îngroșat */
        }

        .continue-chat-btn:hover {
            background-color: #e7f1f5; /* Fundal deschis la hover */
        }

        .no-conversations {
            color: #888;
            font-size: 18px;
        }
        
        /* Container for the action buttons (Home and Logout) */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px; /* Space between buttons */
        }

        /* Style for the action buttons */
        .action-buttons button {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .action-buttons button.friends {
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .action-buttons button.friends:hover {
            background-color: #31b0d5;
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

    </style>
</head>
<div class="action-buttons">
    <button onclick="window.location.href='start_conversation.php'">Start New Conversation</button>
    <button class="friends" onclick="window.location.href='friends.php'">Friends</button>   
    <button onclick="window.location.href='home.php'">Home</button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>
<body>
<h1>Your Conversations</h1>
<div class="friends-container">
    <?php if (mysqli_num_rows($friends_result) > 0): ?>
        <ul>
            <?php while ($friend = mysqli_fetch_assoc($friends_result)): ?>
                <li class="conversation-item">
                    <?php
                    // Set default profile picture
                    $profile_picture = !empty($friend['profile_picture']) ? htmlspecialchars($friend['profile_picture']) : 'uploads/default-profile-picture.jpg';
                    ?>
                    <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                    <span>
                        <a href="profile.php?user_id=<?php echo $friend['id']; ?>">
                            <?php echo htmlspecialchars($friend['user']); ?>
                        </a>
                    </span>
                    <a href="chat.php?friend_id=<?php echo $friend['id']; ?>" class="continue-chat-btn">Continue Chat</a> <!-- Butonul rămâne -->
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p class="no-conversations">You have no conversations yet.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
mysqli_close($conn);
?>
