<?php
require_once('loginRequest.php');
// This page adds a new manager to the database

// Get the login data from the form submission
$first = filter_input(INPUT_POST, 'first');
$last = filter_input(INPUT_POST, 'last');
$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

// Validate user input
function addManager($first, $last, $email, $password) {
    if ($first == NULL || $last == NULL || $email == NULL || $password == NULL) {
        $error = "Invalid input. Check all fields and try again.";
        echo "$error <br>";
        return;
    }
    
    // Prepare login request to verify user credentials
    $request = array();
    $request['type'] = "Login";
    $request['username'] = $email; // Assuming email is used as username
    $request['password'] = $password;
    
    // Send login request using RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);
    
    // Check login response
    if ($response['success']) {
        echo "User authenticated successfully. Proceeding to add manager...<br>";
        
        // Here, add logic to store the manager in the database
        echo "Manager added successfully.";
    } else {
        echo "Login failed: " . $response['message'] . "<br>";
    }
}

// Call the function to add the manager
addManager($first, $last, $email, $password);
?>
<html>
    <head>
        <title>Higher or Lower</title>
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <!-- header -->
        <header>
            <img id="logo" src="images/logo.png"><h3>Higher or Lower</h3>
            <nav class="menu">
                <a href="index.php">Home</a>
                <a href="register.php">Register</a>
                <?php 
                    if (isset($_SESSION['is_valid_admin'])==false){
                        session_start();
                    }
                    if (isset($_SESSION['is_valid_admin'])) { 
                ?>
                
                <a href="highOrLow.php">Play</a>
                <a href="logout.php">Logout</a>
                <p><a>
                    <?php
                        // require_once('userData.php');
                        // userData();
                    ?>
                </a></p>
                <?php } else { ?>
                <a href="login.php">Login</a>
                <?php } ?>              
            </nav>
        </header>
        <!-- main elements -->
        <main>
        </main>
        <hr>
        <hr>
        <footer></footer>
        <hr>
    </body>
</html>