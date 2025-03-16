<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Initialize score if not already set.
if (!isset($_SESSION['trivia_score'])) {
    $_SESSION['trivia_score'] = 0;
}

// Create a RabbitMQ client.
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Process answer submission.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answer'])) {
    $selected = $_POST['answer'];
    $correct = $_SESSION['correct_title'] ?? '';
    if ($selected === $correct) {
        $_SESSION['trivia_score']++;
        $feedback = "Correct! Your score is now " . $_SESSION['trivia_score'] . ".";
    } else {
        $feedback = "Incorrect! The correct answer was: " . htmlspecialchars($correct) . ".<br>Your final score is: " . $_SESSION['trivia_score'] . ".";
        // Update high score if current score is higher.
        $updateRequest = [
            "type"    => "update_trivia_highscore",
            "user_id" => $user_id,
            "score"   => $_SESSION['trivia_score']
        ];
        $client->send_request($updateRequest);
        // Reset score for a new game.
        $_SESSION['trivia_score'] = 0;
        // End game.
        echo "<p>$feedback</p>";
        echo '<p><a href="movie_trivia.php">Play Again</a></p>';
        exit();
    }
}

// Request a random movie for trivia.
$triviaRequest = [
    "type" => "get_trivia_movie"
];

// Attempt to get a movie with a non-empty overview.
$maxAttempts = 10;
$attempt = 0;
do {
    $response = $client->send_request($triviaRequest);
    if (!isset($response['status']) || $response['status'] !== "success") {
        die("Error retrieving trivia movie: " . htmlspecialchars($response['message'] ?? "Unknown error."));
    }
    $movie = $response['movie'];
    $attempt++;
} while(empty(trim($movie['overview'])) && $attempt < $maxAttempts);

if(empty(trim($movie['overview']))) {
    die("No valid trivia movie found with an overview after several attempts.");
}

// Save correct title for checking answer.
$_SESSION['correct_title'] = $movie['title'];
// Options: an array containing one correct title and three incorrect titles.
$options = $movie['options'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cinemaniac Trivia</title>
        <link rel="stylesheet" href="app/static/style.css"/>
        <style>
            /* Example styling for trivia page */
            .container {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background-color: #333;
                border-radius: 8px;
                text-align: center;
            }
            .options {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }
            .option {
                background-color: #555;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                color: #E7E7E7;
                font-size: 16px;
            }
            .option:hover {
                background-color: #777;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Movie Trivia</h1>
            <h2>Score: <?php echo $_SESSION['trivia_score']; ?></h2>
            <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie['overview'])); ?></p>
            <form method="POST" action="movie_trivia.php">
                <div class="options">
                    <?php foreach ($options as $option): ?>
                        <button class="option" type="submit" name="answer" value="<?php echo htmlspecialchars($option); ?>">
                            <?php echo htmlspecialchars($option); ?>
                        </button>
                    <?
