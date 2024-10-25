<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = '';
$db_name = 'businessdb';
$conn = "";

// Database connection
try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}

if (!$conn) {
    die("Failed to connect to the database: " . mysqli_connect_error());
}
?>
