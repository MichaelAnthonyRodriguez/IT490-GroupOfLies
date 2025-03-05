<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// Get the registration data
$first = filter_input(INPUT_POST, 'first');
$last = filter_input(INPUT_POST, 'last');
$user = filter_input(INPUT_POST, 'user');
$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

// Validate inputs
function validateInput($first, $last, $user, $email, $password) {
    if ($first == NULL) {
        die("Error: Invalid first name.");
    }
    if ($last == NULL) {
        die("Error: Invalid last name.");
    }
    if ($user == NULL) {
        die("Error: Invalid username.");
    }
    if ($email == NULL) {
        die("Error: Invalid email.");
    }
    if ($password == NULL) {
        die("Error: Invalid password.");
    }
    echo "<p>Added Successfully</p>";
}

validateInput($first, $last, $user, $email, $password);

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Builds the message as a JSON object
    $request = [
        "type"     => "register",
        "first"    => $first,
        "last"     => $last,
        "user"     => $user,
        "email"    => $email,
        "password" => $hash
    ];

    // Sends the request via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] === "error") {
        echo "<p style='color: red; font-weight: bold;'>" . htmlspecialchars($response["message"]) . "</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>" . htmlspecialchars($response["message"]) . "</p>";
    }

    echo "Response from server:\n";
    print_r($response);

} catch (Exception $e) {
    echo "Error sending message: " . $e->getMessage();
}
?>
<html>
    <head>
        <title>Higher or Lower</title>
        <link rel="stylesheet" href="app/static/style.css"/>
    </head>
    <body>
        <!-- header -->
        <header>
            <img id="logo" src="images/logo.png">
            <h3>Higher or Lower</h3>
            <nav class="menu">
                <a href="index.php">Home</a>
                <a href="register.php">Register</a>
                <?php 
                    session_start();
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
            <p>Welcome</p>
        </main>
        <hr>
        <hr>
        <footer></footer>
        <hr>
    </body>
</html>

