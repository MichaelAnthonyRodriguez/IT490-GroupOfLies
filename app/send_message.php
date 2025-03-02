<?php
require_once __DIR__ . '/vendor/autoload.php'; // Load Composer dependencies
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["message"]) && !empty($_POST["message"])) {
        $messageText = htmlspecialchars($_POST["message"]); // Sanitize input

        try {
            // Connect to RabbitMQ
            $connection = new AMQPStreamConnection('localhost', 5672, 'webdev', 'password');
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
<html>
    <head>
        <title>Higher or Lower</title>
        <link rel="stylesheet" href="static/style.css"/>
    </head>
    <body>
        <!-- header -->
        <header>
            <img id="logo" src="images/logo.png"><h3>Higher or Lower</h3>
            <nav class="menu">
                <a href="index.php">Home</a>
                <a href="register.php">Register</a>
                <?php 
                    if (isset($_SESSION['is_valid_admin'])==false){
                        session_start();
                    }
                    if (isset($_SESSION['is_valid_admin'])) { 
                ?>
                
                <a href="highOrLow.php">Play</a>
                <a href="logout.php">Logout</a>
                <p><a>
                    <?php
                        // require_once('userData.php');
                        // userData();
                    ?>
                </a></p>
                <?php } else { ?>
                <a href="login.php">Login</a>
                <?php } ?>              
            </nav>
        </header>
        <!-- main elements -->
        <main>
        </main>
        <hr>
        <hr>
        <footer></footer>
        <hr>
    </body>
</html>