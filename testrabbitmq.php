<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// RabbitMQ connection details
$host = 'localhost';   // Change if RabbitMQ is on a different server
$port = 5672;          // Default RabbitMQ port
$user = 'guest';       // Replace with your username
$password = 'guest';   // Replace with your password
$vhost = '/';          // Default vhost, change if needed

try {
    // Create a connection
    $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    $channel = $connection->channel();

    // Declare a queue named 'test_queue'
    $queueName = 'test_queue';
    $channel->queue_declare($queueName, false, false, false, false);

    // Create the message
    $messageBody = "Hello from RabbitMQ!";
    $msg = new AMQPMessage($messageBody);

    // Publish the message to the queue
    $channel->basic_publish($msg, '', $queueName);

    echo " [x] Sent: '$messageBody'\n";

    // Close the channel and connection
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "Error sending message: " . $e->getMessage();
}
?>
