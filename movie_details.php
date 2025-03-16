<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Check if tmdb_id is provided via GET
if (!isset($_GET['tmdb_id'])) {
    die("Error: No movie specified.");
}
$tmdb_id = intval($_GET['tmdb_id']);

// Create a RabbitMQ client using your configuration.
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Get movie details
$detailsRequest = [
    "type"    => "movie_details",
    "tmdb_id" => $tmdb_id
];
$detailsResponse = $client->send_request($detailsRequest);
if (!isset($detailsResponse['status']) || $detailsResponse['status'] !== "success") {
    die("Error retrieving movie details: " . htmlspecialchars($detailsResponse['message'] ?? "Unknown error."));
}
$movie = $detailsResponse['movie'];

// If the user submits the review form, process the review.
$feedbackMessage = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SESSION['user_id'])) {
    // Retrieve form data.
    $watchlist = isset($_POST['watchlist']) && $_POST['watchlist'] == "1" ? 1 : 0;
    $rating    = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review    = isset($_POST['review']) ? trim($_POST['review']) : "";
    $user_id   = $_SESSION['user_id'];
    
    $addReviewRequest = [
        "type"    => "add_review",
        "user_id" => $user_id,
        "tmdb_id" => $tmdb_id,
        "watchlist" => $watchlist,
        "rating"    => $rating,
        "review"    => $review
    ];
    $addReviewResponse = $client->send_request($addReviewRequest);
    if (isset($addReviewResponse["status"]) && $addReviewResponse["status"] === "success") {
        $feedbackMessage = "Review submitted successfully.";
    } else {
        $feedbackMessage = "Error: " . htmlspecialchars($addReviewResponse["message"] ?? "Unknown error");
    }
}

// Get all reviews for this movie.
$getReviewsRequest = [
    "type"    => "get_reviews",
    "tmdb_id" => $tmdb_id
];
$getReviewsResponse = $client->send_request($getReviewsRequest);
$reviews = [];
if (isset($getReviewsResponse["status"]) && $getReviewsResponse["status"] === "success") {
    $reviews = $getReviewsResponse["reviews"];
}
?>
<html>
  <head>
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
        <?php if(isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true): ?>
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
          // Build full poster URL (for example, using w342)
          $posterUrl = "";
          if (!empty($movie["poster_path"])) {
              $posterUrl = "https://image.tmdb.org/t/p/w342" . $movie["poster_path"];
          }
      ?>
      <?php if(!empty($posterUrl)): ?>
          <img src="<?php echo htmlspecialchars($posterUrl); ?>" alt="Poster for <?php echo htmlspecialchars($movie["title"]); ?>" style="max-width:300px;">
      <?php else: ?>
          <p>No poster available.</p>
      <?php endif; ?>
      
      <p><strong>Overview:</strong><br><?php echo nl2br(htmlspecialchars($movie["overview"])); ?></p>
      <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie["release_date"]); ?></p>
      <p><strong>Average Rating:</strong> <?php echo htmlspecialchars($movie["vote_average"]); ?>/10</p>
      
      <!-- If user is logged in, show the review form -->
      <?php if(isset($_SESSION['user_id'])): ?>
      <h3>Add to Watchlist / Rate / Review</h3>
      <?php if(!empty($feedbackMessage)) echo "<p>$feedbackMessage</p>"; ?>
      <form method="POST" action="">
          <label>
              <input type="checkbox" name="watchlist" value="1"> Add to Watchlist
          </label><br><br>
          <label for="rating">Rate (1 to 10):</label>
          <select name="rating" id="rating" required>
              <?php for($i=1; $i<=10; $i++): ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php endfor; ?>
          </select><br><br>
          <label for="review">Write a review:</label><br>
          <textarea id="review" name="review" rows="5" cols="50" placeholder="Enter your review here..."></textarea><br><br>
          <input type="submit" value="Submit Review">
      </form>
      <?php else: ?>
          <p>Please <a href="login.php">login</a> to add to your watchlist, rate, or review this movie.</p>
      <?php endif; ?>
      
      <!-- Display all reviews for this movie -->
      <h3>User Reviews</h3>
      <?php if (!empty($reviews) && is_array($reviews)): ?>
          <ul style="list-style-type: none; padding: 0;">
              <?php foreach ($reviews as $rev): ?>
                  <li style="margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                      <p><strong><?php echo htmlspecialchars($rev["username"]); ?></strong> on <?php echo htmlspecialchars($rev["review_date"]); ?></p>
                      <p>Rating: <?php echo htmlspecialchars($rev["rating"]); ?>/10</p>
                      <p><?php echo nl2br(htmlspecialchars($rev["review"])); ?></p>
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
