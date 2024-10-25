<?php
session_start();
include("database.php");

// Verificăm dacă utilizatorul este conectat
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Obținem ID-ul utilizatorului curent
$username = $_SESSION['username'];
$sql = "SELECT id FROM users WHERE user='$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
$current_user_id = $user['id'];

// Verificăm dacă există un ID de prieten (destinatar)
if (isset($_GET['friend_id'])) {
    $friend_id = intval($_GET['friend_id']);
    
    // Obținem numele prietenului din baza de date
    $friend_sql = "SELECT user FROM users WHERE id = $friend_id";
    $friend_result = mysqli_query($conn, $friend_sql);

    // Verificăm dacă prietenul există
    if ($friend_result && mysqli_num_rows($friend_result) > 0) {
        $friend_data = mysqli_fetch_assoc($friend_result);
        $friend_name = htmlspecialchars($friend_data['user']); // Numele prietenului
    } else {
        echo "Prietenul nu a fost găsit.";
        exit();
    }

    // Procesăm trimiterea unui mesaj
    if (isset($_POST['send_message'])) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $send_message_sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($current_user_id, $friend_id, '$message')";
        if (mysqli_query($conn, $send_message_sql)) {
            echo "Mesaj trimis!";
        } else {
            echo "Eroare la trimiterea mesajului: " . mysqli_error($conn);
        }
    }

    // Obținem toate mesajele dintre utilizatorul curent și prietenul selectat
    $messages_sql = "SELECT * FROM messages 
                     WHERE (sender_id = $current_user_id AND receiver_id = $friend_id) 
                     OR (sender_id = $friend_id AND receiver_id = $current_user_id)
                     ORDER BY sent_at ASC";
    $messages_result = mysqli_query($conn, $messages_sql);

} else {
    echo "Nu a fost specificat niciun prieten.";
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?php echo htmlspecialchars($friend_id); ?></title>
    <style>
        /* Stiluri de bază pentru pagină */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .chat-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .message {
            margin-bottom: 15px;
        }
        .message .from {
            font-weight: bold;
        }
        .message .content {
            margin-left: 20px;
        }
        .send-message-form {
            margin-top: 20px;
        }
        .send-message-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: none;
        }
        .send-message-form button {
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 10px;
        }
        .send-message-form button:hover {
            background-color: #4cae4c;
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
                   /* Stiluri pentru butonul de mesaje rotund */
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
<div class="action-buttons">
    <button class="message-button" onclick="window.location.href='messages.php'">✉</button>
    <button class="friends" onclick="window.location.href='friends.php'">Friends</button>   
    <button onclick="window.location.href='home.php'">Home</button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>
<body>
    <div class="chat-container">
        <h1>Chat with <?php echo $friend_name; ?></h1>
        
        <!-- Afișăm mesajele -->
        <div class="messages">
            <?php if (mysqli_num_rows($messages_result) > 0): ?>
                <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                    <div class="message">
                        <span class="from"><?php echo ($message['sender_id'] == $current_user_id) ? 'You' : 'Friend'; ?>:</span>
                        <span class="content"><?php echo htmlspecialchars($message['message']); ?></span>
                        <div><small><?php echo $message['sent_at']; ?></small></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages yet.</p>
            <?php endif; ?>
        </div>

        <!-- Form pentru trimiterea unui mesaj -->
        <form action="" method="POST" class="send-message-form">
            <textarea name="message" rows="3" placeholder="Write your message here..." required></textarea>
            <button type="submit" name="send_message">Send Message</button>
        </form>
    </div>
</body>
</html>

<?php
mysqli_close($conn);
?>
