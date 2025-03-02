<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Use POST data if available, otherwise check for GET parameter
if (isset($_POST['message'])) {
    $messageText = htmlspecialchars($_POST['message']);
} elseif (isset($_GET['message'])) {
    $messageText = htmlspecialchars($_GET['message']);
} else {
    die("No message provided.");
}

// Connection details for RabbitMQ
$host = '100.105.162.20';
$port = 5672;
$user = 'webdev';
$password = 'password';
$vhost = '/'; // Change if necessary

try {
    // Establish a connection to RabbitMQ
    $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    $channel = $connection->channel();

    // Declare (and create if needed) a durable queue named 'hello'
    $queue = 'hello';
    $channel->queue_declare($queue, false, true, false, false);

    // Create and publish the message (delivery_mode 2 makes it persistent)
    $msg = new AMQPMessage($messageText, array('delivery_mode' => 2));
    $channel->basic_publish($msg, '', $queue);

    echo " [x] Sent '$messageText'\n";

    // Close the channel and connection
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "Error sending message: " . $e->getMessage();
}
?>
