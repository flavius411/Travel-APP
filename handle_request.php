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

// Handle accept or reject action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_user = mysqli_real_escape_string($conn, $_POST['from_user']);

    // Fetch the ID of the user who sent the request
    $from_user_sql = "SELECT id FROM users WHERE user='$from_user'";
    $from_user_result = mysqli_query($conn, $from_user_sql);
    $from_user_data = mysqli_fetch_assoc($from_user_result);
    
    // Verifică dacă utilizatorul există
    if (!$from_user_data) {
        echo "User not found!";
        exit();
    }

    $from_user_id = $from_user_data['id'];

    if (isset($_POST['accept'])) {
        // Adaugă în tabela friends
        $add_friend_sql = "INSERT INTO friends (user1_id, user2_id) VALUES ('$current_user_id', '$from_user_id')";
        mysqli_query($conn, $add_friend_sql);

        // Șterge cererea de prietenie
        $delete_request_sql = "DELETE FROM friend_requests WHERE from_user_id='$from_user_id' AND to_user_id='$current_user_id'";
        mysqli_query($conn, $delete_request_sql);

        $message = "You are now friends with $from_user!";
    } elseif (isset($_POST['reject'])) {
        // Șterge cererea de prietenie
        $delete_request_sql = "DELETE FROM friend_requests WHERE from_user_id='$from_user_id' AND to_user_id='$current_user_id'";
        mysqli_query($conn, $delete_request_sql);

        $message = "You have rejected the friend request from $from_user.";
    }

    // Redirect back to friends page with a message
    $_SESSION['message'] = $message;
    header("Location: friends.php");
    exit();
}

mysqli_close($conn);
?>
