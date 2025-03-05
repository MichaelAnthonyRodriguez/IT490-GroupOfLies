<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$username = filter_input(INPUT_POST, 'user');
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
        "username" => $username,
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
        
        echo "login successful! Redirecting...</p>";
        header("refresh:2;url=highOrLow.php");
    } else {
        echo "error " . htmlspecialchars($response["message"]) . "</p>";
    }

} catch (Exception $e) {
  echo "Error sending message: " . $e->getMessage();
}
?>
