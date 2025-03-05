<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$user = filter_input(INPUT_POST, 'user');
$password = filter_input(INPUT_POST, 'password');

// Validate inputs
function validateInput($user, $password) {
  if ($user == NULL) {
      exit("Error: Invalid username.");
  }
  if ($password == NULL) {
      exit("Error: Invalid password.");
  }
  // echo "<p>Added Successfully</p>";
}
validateInput( $user, $password);


try {
    // Build the message as a JSON object
    $request = [
        "type"     => "login",
        "user" => $user,
        "password" => $password
    ];

    // Send request via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] === "success") {
      $_SESSION['is_valid_admin'] = true;
      $_SESSION['username'] = $username;
      $_SESSION['session_token'] = $response['session_token'];
      $_SESSION['user_id'] = $response['user_id'];
      $_SESSION['first_name'] = $response['first_name'];  // Store first name
      $_SESSION['last_name'] = $response['last_name'];    // Store last name
      $_SESSION['login_time'] = time(); // Store session start time

        echo "login successful! Redirecting...</p>";
        header("refresh:2;url=index.php");
    } else {
        echo "error " . htmlspecialchars($response["message"]) . "</p>";
    }

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

                <?php if (isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true) { ?>
                    <a href="highOrLow.php">Play</a>
                    <a href="logout.php">Logout</a>
                    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['first_name'] . " " . $_SESSION['last_name']); ?></strong>!</p>
                    <?php } else { ?>
                    <a href="register.php">Register</a>
                    <a href="login.php">Login</a>
                <?php } ?>
            </nav>
        </header>

        <!-- main elements -->
        <main>
        </main>
        
        <hr>
        <footer></footer>
    </body>
</html>
