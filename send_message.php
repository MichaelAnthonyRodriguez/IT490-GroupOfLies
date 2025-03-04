<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Get the registration data
$first = filter_input(INPUT_POST, 'first');
$last = filter_input(INPUT_POST, 'last');
$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

// Validate inputs
function validateInput($first, $last, $email, $password) {
  
    if ($first == NULL)
    {
      $error = "Invalid first name. Check all fields and try again.";
      echo "$error <br>";
    }
    elseif ($last == NULL)
    {
      $error = "Invalid last name. Check all fields and try again.";
      echo "$error <br>";
    }
    elseif ($email == NULL)
    {
      $error = "Invalid last name. Check all fields and try again.";
      echo "$error <br>";
    }
    echo "<p>Added Successfully</p>";
  }
validateInput($first, $last, $email, $password);

// Connection details for RabbitMQ
$host = '100.105.162.20';
$port = 5672;
$user = 'webdev';
$password = 'password';
$vhost = '/';

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    // Build the message as a JSON string
    $messageText = json_encode([
        "first"    => $first,
        "last"     => $last,
        "email"    => $email,
        "password" => $hash  // In production, never send or store plain text passwords!
    ]);

    // Establish a connection to RabbitMQ
    $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    $channel = $connection->channel();

    // Declare (and create if needed) a durable queue named 'hello'
    $queue = 'register';
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
