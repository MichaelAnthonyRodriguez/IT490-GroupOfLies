<?php
require_once('path.inc');
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
    
    // Build the message as a JSON object
    $request = [
        "type"     => "register",
        "first"    => $first,
        "last"     => $last,
        "user"     => $user,
        "email"    => $email,
        "password" => $hash
    ];

    // Send the request via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    echo "Response from server:\n";
    print_r($response);

} catch (Exception $e) {
    echo "Error sending message: " . $e->getMessage();
}
?>
