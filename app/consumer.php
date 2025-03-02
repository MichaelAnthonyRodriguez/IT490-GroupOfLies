<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue_name = 'task_queue';
$channel->queue_declare($queue_name, false, true, false, false);

echo " [*] Waiting for messages. To exit, press CTRL+C\n";

$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
    sleep(1); // Simulating processing time
    echo " [x] Done\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
?>
