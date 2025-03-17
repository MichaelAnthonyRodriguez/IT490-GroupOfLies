<?php

session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Ensure tmdb_id is provided via GET.
if (!isset($_GET['tmdb_id'])) {
    die("Error: No movie specified.");
}
$tmdb_id = intval($_GET['tmdb_id']);

// Create a RabbitMQ client.
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Build the request for full movie details (details + reviews).
$request = [
    "type"    => "full_movie_details",
    "tmdb_id" => $tmdb_id
];

$feedbackMessage = "";
// Process watchlist form here (since it redirects).
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? "";
    if ($action == "update_watchlist") {
        $watchlist = isset($_POST['watchlist']) && $_POST['watchlist'] == "1" ? 1 : 0;
        $watchlistRequest = [
            "type"    => "update_watchlist",
            "user_id" => $user_id,
            "tmdb_id" => $tmdb_id,
            "watchlist" => $watchlist
        ];
        $res = $client->send_request($watchlistRequest);
        header("Location: movie_watchlist.php");
        exit();
    }
}

// Fetch movie details (and reviews).
$response = $client->send_request($request);
if (!isset($response['status']) || $response['status'] !== "success") {
    die("Error retrieving movie details: " . htmlspecialchars($response['message'] ?? "Unknown error."));
}
$movie = $response['movie'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($movie["title"]); ?> - Details</title>
    <link rel="stylesheet" href="app/static/style.css"/>
  </head>
  <body>
    <header>
      <img id="logo" src="images/logo.png" alt="Cinemaniac Logo">
      <h3>Cinemaniac</h3>
      <nav class="menu">
        <a href="movie_homepage.php">Home</a>
        <a href="movie_search.php">Search</a>
        <?php if (isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true): ?>
          <a href="movie_watchlist.php">My Watchlist</a>
          <a href="movie_trivia.php">Trivia</a>
          <a href="logout.php">Logout</a>
          <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['first_name'] . " " . $_SESSION['last_name']); ?></strong>!</p>
        <?php else: ?>
          <a href="register.php">Register</a>
          <a href="login.php">Login</a>
        <?php endif; ?>
      </nav>
    </header>
    <main>
      <h2><?php echo htmlspecialchars($movie["title"]); ?></h2>
      <?php
          // Build full poster URL (using size w342).
          $posterUrl = "";
          if (!empty($movie["poster_path"])) {
              $posterUrl = "https://image.tmdb.org/t/p/w342" . $movie["poster_path"];
          }
      ?>
      <?php if (!empty($posterUrl)): ?>
          <img src="<?php echo htmlspecialchars($posterUrl); ?>" alt="Poster for <?php echo htmlspecialchars($movie["title"]); ?>" style="max-width:300px;">
      <?php else: ?>
          <p>No poster available.</p>
      <?php endif; ?>
      
      <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie["overview"])); ?></p>
      <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie["release_date"]); ?></p>
      <p><strong>Average Rating:</strong> <?php echo htmlspecialchars($movie["vote_average"]); ?>/10</p>
      
      <?php if (isset($_SESSION['user_id'])): ?>
      <h3>Update Your Preferences</h3>
      <?php if (!empty($feedbackMessage)) echo "<p>$feedbackMessage</p>"; ?>
      
      <!-- Watchlist Form: Redirects to watchlist page -->
      <form method="POST" action="">
          <input type="hidden" name="action" value="update_watchlist">
          <input type="hidden" name="tmdb_id" value="<?php echo $tmdb_id; ?>">
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <input type="checkbox" name="watchlist" value="1"> Add to Watchlist<br>
          <input type="submit" value="Update Watchlist">
      </form>
      
      <!-- Rating Form: Submits to movie_details_request.php -->
      <form method="POST" action="movie_details_request.php">
          <input type="hidden" name="action" value="update_rating">
          <input type="hidden" name="tmdb_id" value="<?php echo $tmdb_id; ?>">
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <label for="rating">Rate (1 to 10):</label>
          <select name="rating" id="rating" required>
              <?php for ($i = 1; $i <= 10; $i++): ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php endfor; ?>
          </select>
          <input type="submit" value="Submit Rating">
      </form>
      
      <!-- Review Form: Submits to movie_details_request.php -->
      <form method="POST" action="movie_details_request.php">
          <input type="hidden" name="action" value="update_review">
          <input type="hidden" name="tmdb_id" value="<?php echo $tmdb_id; ?>">
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <label for="review">Write a review:</label><br>
          <textarea id="review" name="review" rows="5" cols="50" placeholder="Enter your review here..." required></textarea><br>
          <input type="submit" value="Submit Review">
      </form>
      <?php else: ?>
          <p>Please <a href="login.php">login</a> to update your preferences.</p>
      <?php endif; ?>
      <br>
      <br/>
      <h3>User Reviews</h3>
        <?php if (isset($movie["reviews"]) && is_array($movie["reviews"]) && count($movie["reviews"]) > 0): ?>
            <ul class="user-reviews">
                <?php foreach ($movie["reviews"] as $rev): ?>
                    <li>
                        <p><strong><?php echo htmlspecialchars($rev["username"]); ?></strong> on <?php echo htmlspecialchars($rev["review_date"]); ?></p>
                        <p>Rating: <?php echo htmlspecialchars($rev["rating"]); ?>/10</p>
                        <p><?php echo nl2br(htmlspecialchars($rev["review"])); ?></p>
                        <br />
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </main>
    <footer></footer>
  </body>
</html>
