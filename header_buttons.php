<?php
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page == 'home.php') {
    $homeButtonText = "Profile"; // Schimbă textul în "Profile"
    $homelink = "profile.php";
} else {
    $homeButtonText = "Home"; // Text implicit
    $homelink = "home.php";
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!-- header_buttons.php -->
<div class="action-buttons">
    <button class="message-button" onclick="window.location.href='messages.php'">✉</button>
    <button class="friends" onclick="window.location.href='friends.php'">Friends</button>   
    <button onclick="window.location.href='<?php echo $homelink; ?>'"><?php echo $homeButtonText; ?></button>
    <button class="logout" onclick="window.location.href='home.php?logout=true'">Logout</button>
</div>

<style>
    /* Container for the action buttons (Home, Friends, Logout) */
    .action-buttons {
        position: fixed;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 10px; /* Space between buttons */
        z-index: 999; /* Asigurăm că butoanele sunt deasupra oricărui alt element */
    }
    /* General styles for the action buttons */
    .action-buttons button {
        background-color: #5bc0de;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        cursor: pointer;
    }
    .action-buttons button:hover {
        background-color: #31b0d5;
    }
    /* Logout button specific style */
    .action-buttons button.logout {
        background-color: #d9534f;
    }
    .action-buttons button.logout:hover {
        background-color: #c9302c;
    }
    /* Style for the round message button */
    .message-button {
        background-color: #5bc0de;
        color: white;
        border: none;
        border-radius: 50%; /* Round button */
        width: 50px;
        height: 50px;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
