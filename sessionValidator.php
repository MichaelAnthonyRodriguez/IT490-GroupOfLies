<?php
session_start();
require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if (!isset($_SESSION['session_token'])) {
    exit("not authenticated.");
}

try {
    // Build the validation request
    $request = [
        "type"        => "validate_session",
        "session_token" => $_SESSION['session_token']
    ];

    // Send request via RabbitMQ
    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] !== "success") {
        session_destroy();
        exit("session expired. Please log in again.");
    }

    echo "✅ Session is valid.";

} catch (Exception $e) {
    echo "error checking session: " . htmlspecialchars($e->getMessage());
}
?>
