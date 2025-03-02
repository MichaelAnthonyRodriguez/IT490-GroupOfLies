<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connection details
$host = '100.105.162.20';
$port = 5672;
$user = 'webdev';
$password = 'password';
$vhost = '/'; // default vhost, change if necessary

// Establish a connection to RabbitMQ
$connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
$channel = $connection->channel();

// Declare a durable queue named 'hello'
$queue = 'hello';
$channel->queue_declare($queue, false, true, false, false);

// Create the message you want to send
$messageBody = 'Hello RabbitMQ from PHP!';
$msg = new AMQPMessage($messageBody, array('delivery_mode' => 2)); // delivery_mode 2 makes it persistent

// Publish the message to the queue
$channel->basic_publish($msg, '', $queue);

echo " [x] Sent '$messageBody'\n";

// Close the channel and connection
$channel->close();
$connection->close();
?>
