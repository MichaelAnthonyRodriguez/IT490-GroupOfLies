<?php
session_start();
require_once('rpc/path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if (!isset($_SESSION['session_token'])) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 42000, '/'); 
    header("Location: login.php");
    exit("Not authenticated. Redirecting to login...");
}

try {
    $request = [
        "type"          => "validate_session",
        "session_token" => $_SESSION['session_token']
    ];

    $client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
    $response = $client->send_request($request);

    if ($response["status"] !== "success") {
        $logoutRequest = [
            "type"          => "logout",
            "session_token" => $_SESSION['session_token']
        ];
        $client->send_request($logoutRequest); 

        $_SESSION = [];
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 42000, '/'); 

        header("Location: login.php");
        exit("Session expired. Redirecting to login...");
    }

    echo "Session is valid.";

} catch (Exception $e) {
    echo "Error checking session: " . htmlspecialchars($e->getMessage());
}
?>
