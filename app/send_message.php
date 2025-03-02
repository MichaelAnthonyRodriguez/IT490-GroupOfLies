<?php
require_once __DIR__ . '/vendor/autoload.php'; // Load Composer dependencies
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["message"]) && !empty($_POST["message"])) {
        $messageText = htmlspecialchars($_POST["message"]); // Sanitize input

        try {
            // Connect to RabbitMQ
            $connection = new AMQPStreamConnection('100.105.162.20', 5672, 'webdev', 'password');
            $channel = $connection->channel();

            // Declare Queue
            $queue_name = 'task_queue';
            $channel->queue_declare($queue_name, false, true, false, false);

            // Create and send message
            $msg = new AMQPMessage($messageText, ['delivery_mode' => 2]); // Persistent message
            $channel->basic_publish($msg, '', $queue_name);

            // Close connection
            $channel->close();
            $connection->close();

            echo "Message sent successfully: " . $messageText;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error: Message field is empty.";
    }
} else {
    echo "Invalid request.";
}
?>
