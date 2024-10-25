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

// Fetch incoming friend requests
$friend_requests_sql = "SELECT users.id, users.user, users.profile_picture FROM friend_requests 
                        JOIN users ON friend_requests.from_user_id = users.id 
                        WHERE friend_requests.to_user_id = $current_user_id";
$friend_requests_result = mysqli_query($conn, $friend_requests_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h1 {
            color: #333;
        }
        .friend-request, .search-container {
            margin-top: 20px;
        }
        .friend-request button {
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
        .friend-request button:hover {
            background-color: #4cae4c;
        }
        input[type="text"], button[type="submit"] {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
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
        .friend-request img {
            width: 50px; /* Adjust the size of the profile picture */
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
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
    <button onclick="window.location.href='friends.php'">View Friends</button>
    <button onclick="window.location.href='home.php'">Home</button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>
<h1>Friend Requests</h1>

<div class="form-container">
    <?php if (mysqli_num_rows($friend_requests_result) > 0): ?>
        <h2>Pending Requests</h2>
        <?php while ($request = mysqli_fetch_assoc($friend_requests_result)): ?>
            <div class="friend-request">
                <?php
                // Set default profile picture
                $profile_picture = !empty($request['profile_picture']) ? htmlspecialchars($request['profile_picture']) : 'uploads/default-profile-picture.jpg';
                ?>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                <p><?php echo htmlspecialchars($request['user']); ?> wants to be your friend!</p>
                <form action="handle_request.php" method="POST">
                    <input type="hidden" name="from_user" value="<?php echo htmlspecialchars($request['user']); ?>">
                    <button type="submit" name="accept">Accept</button>
                    <button type="submit" name="reject">Reject</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <h2>No friend requests. Search for friends below:</h2>
        <div class="search-container">
            <form action="search.php" method="GET">
                <input type="text" name="search" placeholder="Search for friends" required>
                <button type="submit">Search</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
