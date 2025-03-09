<?php
session_start();
require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

// If session does not exist, redirect to login
if (!isset($_SESSION['session_token'])) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 42000, '/');
    header("Location: login.php");
    exit();
}

try {
    // Send logout request to remove session from the database
    $request = [
        "type"          => "logout",
        "session_token" => $_SESSION['session_token']
    ];

    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] === "success") {
        echo "Successfully logged out. Redirecting...";
    } else {
        echo "Logout failed: " . htmlspecialchars($response["message"]);
    }

} catch (Exception $e) {
    echo "Error logging out: " . htmlspecialchars($e->getMessage());
}

// Fully destroy the session
$_SESSION = [];
session_unset();
session_destroy();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Redirect to login page
header("Location: login.php");
exit();
?>
