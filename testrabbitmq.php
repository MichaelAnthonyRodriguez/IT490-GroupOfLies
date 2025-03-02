<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PhpAmqpLib\Connection\AMQPStreamConnection;

require_once __DIR__ . '/vendor/autoload.php';

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    echo "✅ Connected to RabbitMQ!";
    $connection->close();
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
