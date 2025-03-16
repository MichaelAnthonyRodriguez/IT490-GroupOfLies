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

// Request the user's trivia highscore.
$highscoreRequest = [
    "type"    => "get_trivia_highscore",
    "user_id" => $user_id
];
$highscoreResponse = $client->send_request($highscoreRequest);
if (isset($highscoreResponse['status']) && $highscoreResponse['status'] === "success") {
    $triviaHighscore = $highscoreResponse['trivia_highscore'];
} else {
    $triviaHighscore = "N/A";
}

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
        // Reload the page to update the displayed highscore.
        header("Location: movie_trivia.php");
        exit();
    }
}

// Request a random movie for trivia.
$triviaRequest = [
    "type" => "get_trivia_movie"
];
$response = $client->send_request($triviaRequest);
if (!isset($response['status']) || $response['status'] !== "success") {
    die("Error retrieving trivia movie: " . htmlspecialchars($response['message'] ?? "Unknown error."));
}
$movie = $response['movie'];

// Save correct title for checking answer.
$_SESSION['correct_title'] = $movie['title'];
// Options: an array containing one correct title and three incorrect titles.
$options = $movie['options'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cinemaniac</title>
        <link rel="stylesheet" href="app/static/style.css"/>
    </head>
    <body>
        <!-- header -->
        <header>
            <img id="logo" src="images/logo.png">
            <h3>Cinemaniac</h3>
            <nav class="menu">
                <a href="movie_homepage.php">Home</a>
                <a href="movie_search.php">Search</a>
                <?php if (isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true) { ?>
                    <a href="movie_watchlist.php">My Watchlist</a>
                    <a href="movie_trivia.php">Trivia</a>
                    <a href="logout.php">Logout</a>
                    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['first_name'] . " " . $_SESSION['last_name']); ?></strong>!</p>
                    <?php } else { ?>
                    <a href="register.php">Register</a>
                    <a href="login.php">Login</a>
                <?php } ?>
            </nav>
        </header>
    <body>
        <div class="container">
            <h1>Movie Trivia</h1>
            <h2>High Score: <?php echo htmlspecialchars($triviaHighscore); ?></h2>
            <h2>Score: <?php echo $_SESSION['trivia_score']; ?></h2>
            <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie['overview'])); ?></p>
            <br>
            <form method="POST" action="movie_trivia.php">
                <div class="options">
                    <?php foreach ($options as $option): ?>
                        <button class="option" type="submit" name="answer" value="<?php echo htmlspecialchars($option); ?>">
                            <?php echo htmlspecialchars($option); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
            <?php if(isset($feedback)): ?>
                <p><?php echo $feedback; ?></p>
            <?php endif; ?>
        </div>
    </body>
</html>
