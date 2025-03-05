<?php
session_start();
require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if (!isset($_SESSION['session_token'])) {
    echo "no active session found. Redirecting to login...";
    header("refresh:2;url=login.php");
    exit();
}

try {
    // Build logout request
    $request = [
        "type"          => "logout",
        "session_token" => $_SESSION['session_token']
    ];

    // Send request via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] === "success") {
        echo "successfully logged out. Redirecting...</p>";
    } else {
        echo "logout failed: " . htmlspecialchars($response["message"]) . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>error logging out: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$_SESSION = []; // Unset all session variables
session_unset(); // Free session variables
session_destroy(); // Destroy session
setcookie(session_name(), '', time() - 42000, '/'); // Delete session cookie

// Redirect to login page after 2 seconds
header("refresh:2;url=login.php");
exit();
?>
