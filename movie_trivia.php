<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';
require_once 'mysqlconnect.php'; // Ensure we can query the users table

// Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Retrieve the user's current trivia highscore from the database.
$query = "SELECT trivia_highscore FROM users WHERE id = " . intval($user_id);
$result = $mydb->query($query);
$highscore = ($result && $row = $result->fetch_assoc()) ? (int)$row['trivia_highscore'] : 0;

// Initialize score if not set.
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
        $finalScore = $_SESSION['trivia_score'];
        // Update highscore if necessary by sending a RabbitMQ request.
        $updateRequest = [
            "type"    => "update_trivia_highscore",
            "user_id" => $user_id,
            "score"   => $finalScore
        ];
        $client->send_request($updateRequest);
        $_SESSION['trivia_score'] = 0;
        // Redirect to reload the page with a feedback message.
        header("Location: trivia.php?feedback=" . urlencode("Game Over! Final Score: $finalScore"));
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

// Save correct title for answer checking.
$_SESSION['correct_title'] = $movie['title'];
// The trivia movie includes an 'options' key with one correct and three incorrect titles.
$options = $movie['options'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Movie Trivia</title>
    <style>
      body { background-color: #1d1d1d; color: #E7E7E7; font-family: sans-serif; }
      .container {
          max-width: 800px;
          margin: 20px auto;
          padding: 20px;
          background-color: #333;
          border-radius: 8px;
      }
      h1, h2, p { text-align: center; }
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
      .option:hover { background-color: #777; }
      form { text-align: center; margin-top: 20px; }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Movie Trivia</h1>
      <h2>Your Highscore: <?php echo $highscore; ?></h2>
      <h2>Current Score: <?php echo $_SESSION['trivia_score']; ?></h2>
      <?php if(isset($_GET['feedback'])): ?>
          <p><?php echo htmlspecialchars($_GET['feedback']); ?></p>
      <?php endif; ?>
      <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie['overview'])); ?></p>
      <form method="POST" action="trivia.php">
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
