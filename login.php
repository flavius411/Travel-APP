<?php
session_start();
include("database.php");

// Initialize an empty error message variable in session
if (!isset($_SESSION['error_message'])) {
    $_SESSION['error_message'] = '';
}

// Handle Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['login_username']);
    $password = $_POST['login_password'];

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE user = '$username'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['user'];
                unset($_SESSION['error_message']); // Clear any previous error messages
                $_SESSION['user_id'] = $user['id']; // După autentificare
                header("Location: home.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Incorrect password!";
            }
        } else {
            $_SESSION['error_message'] = "User does not exist!";
        }
    } else {
        $_SESSION['error_message'] = "Please enter a valid username and password.";
    }
}

// Handle Registration
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = mysqli_real_escape_string($conn, $_POST['reg_email']); // Preluăm email-ul

    // Verificăm dacă emailul este valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Please enter a valid email address."; // Setăm mesajul de eroare
    } else {
        // Continuăm procesul de înregistrare dacă emailul este valid
        if (!empty($username) && !empty($password) && !empty($confirm_password)) {
            if ($password === $confirm_password) {
                // Verificăm dacă utilizatorul sau emailul există deja
                $check_sql = "SELECT * FROM users WHERE user = '$username' OR email = '$email'";
                $check_result = mysqli_query($conn, $check_sql);

                if (mysqli_num_rows($check_result) == 0) {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user into the database, including email
                    $insert_sql = "INSERT INTO users (user, email, password) VALUES ('$username', '$email', '$hashed_password')";
                    if (mysqli_query($conn, $insert_sql)) {
                        $_SESSION['error_message'] = "Registration successful! You can now log in."; // Mesaj de succes
                    } else {
                        $_SESSION['error_message'] = "Error: Could not register user.";
                    }
                } else {
                    $_SESSION['error_message'] = "Username or email already taken!";
                }
            } else {
                $_SESSION['error_message'] = "Passwords do not match!";
            }
        } else {
            $_SESSION['error_message'] = "Please fill in all fields.";
        }
    }
}

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Register</title>
    <style>
        /* Stilul tău CSS aici */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin-top: 10px;
            text-align: left;
            font-size: 14px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
            display: block;
            width: 90%;
        }
        /* Image background */
        .background {
            position: absolute; /* Position it absolutely */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('./uploads/images/group.jpg'); /* Change to your image path */
            background-size: cover; /* Cover the entire area */
            background-position: center; /* Center the image */
            z-index: -1; /* Place it behind other elements */
        }
        /* General styles for the body */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        /* Container for the form */
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        /* Headings */
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        /* Style the form inputs */
        input[type="text"], input[type="password"], input[type="email"] {
            width: 92%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        /* Style the buttons */
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        /* Style for the links */
        p {
            margin-top: 20px;
        }
        a {
            color: #337ab7;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="background"></div> 
<div class="form-container">
    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
    ?>
        <h2>Register</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="text" name="reg_username" placeholder="Username" required><br>
            <input type="email" name="reg_email" placeholder="Email" required><br>
            <input type="password" name="reg_password" placeholder="Password" required><br>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
            <input type="submit" name="register" value="Register"><br>
        </form>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
            <?php // Clear the error message after displaying it
            unset($_SESSION['error_message']);
            ?>
        <?php endif; ?>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php
    } else {
    ?>
        <h2>Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="text" name="login_username" placeholder="Username" required><br>
            <input type="password" name="login_password" placeholder="Password" required><br>
            <input type="submit" name="login" value="Login"><br>
        </form>
        <?php  // Display error message if available
        if (!empty($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            // Clear the error message after displaying it
            unset($_SESSION['error_message']);
        }
        ?>
        <p>Don't have an account? <a href="login.php?action=register">Register here</a></p>
    <?php
    }
    ?>
</div>
</body>
</html>
