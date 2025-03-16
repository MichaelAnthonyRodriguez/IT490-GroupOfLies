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

// Initialize current trivia score if not set.
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
        // Update highscore if the current score is higher.
        $updateRequest = [
            "type"    => "update_trivia_highscore",
            "user_id" => $user_id,
            "score"   => $_SESSION['trivia_score']
        ];
        $client->send_request($updateRequest);
        // Reset score for a new game.
        $_SESSION['trivia_score'] = 0;
        // Reload page to update the displayed highscore.
        header("Location: movie_trivia.php");
        exit();
    }
}

// Request a random trivia movie.
$triviaRequest = [
    "type" => "get_trivia_movie"
];
$response = $client->send_request($triviaRequest);
if (!isset($response['status']) || $response['status'] !== "success") {
    die("Error retrieving trivia movie: " . htmlspecialchars($response['message'] ?? "Unknown error."));
}
$movie = $response['movie'];

// Save correct title for later answer checking.
$_SESSION['correct_title'] = $movie['title'];
// $movie['options'] should be an array with one correct and three incorrect titles.
$options = $movie['options'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Movie Trivia</title>
    <style>
      body { background-color: #1d1d1d; color: #E7E7E7; font-family: sans-serif; }
      .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: #333; border-radius: 8px; }
      h1, h2, p { text-align: center; }
      .options { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
      .option { background-color: #555; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; color: #E7E7E7; font-size: 16px; }
      .option:hover { background-color: #777; }
      form { text-align: center; margin-top: 20px; }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Movie Trivia</h1>
      <h2>Your High Score: <?php echo htmlspecialchars($triviaHighscore); ?></h2>
      <h2>Current Score: <?php echo $_SESSION['trivia_score']; ?></h2>
      <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie['overview'])); ?></p>
      <form method="POST" action="movie_trivia.php">
        <div class="options">
          <?php foreach ($options as $option): ?>
            <button class="option" type="submit" name="answer" value="<?php echo htmlspecialchars($option); ?>">
              <?php echo htmlspecialchars($option); ?>
            </button>
          <?php endforeach; ?>
        </div>
      </form>
      <?php if (isset($feedback)): ?>
        <p><?php echo $feedback; ?></p>
      <?php endif; ?>
    </div>
  </body>
</html>
