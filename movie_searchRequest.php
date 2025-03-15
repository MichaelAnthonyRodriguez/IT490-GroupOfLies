<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Make sure a tmdb_id is provided
if (!isset($_GET['tmdb_id'])) {
    die("Error: No movie specified.");
}
$tmdb_id = intval($_GET['tmdb_id']);

// Build the RabbitMQ request for movie details
$request = [
    "type" => "movie_details",
    "tmdb_id" => $tmdb_id
];

// Create a RabbitMQ client (using your testRabbitMQ.ini configuration, "testServer" queue)
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request($request);

// Check for errors in the response
if (!isset($response['status']) || $response['status'] !== 'success') {
    die("Error fetching movie details: " . htmlspecialchars($response['message'] ?? "Unknown error"));
}

// Retrieve movie details from the response
$movie = $response['movie'];
?>
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
    <main>
      <h2><?php echo htmlspecialchars($movie["title"]); ?></h2>
      <!-- Display poster if available -->
      <?php if (!empty($movie["poster_path"])): ?>
          <img src="<?php echo htmlspecialchars($movie["poster_path"]); ?>" alt="<?php echo htmlspecialchars($movie["title"]); ?> Poster" style="max-width:300px;">
      <?php else: ?>
          <p>No poster available.</p>
      <?php endif; ?>
      <p><strong>Overview:</strong> <?php echo nl2br(htmlspecialchars($movie["overview"])); ?></p>
      <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie["release_date"]); ?></p>
      <p><strong>Average Rating:</strong> <?php echo htmlspecialchars($movie["vote_average"]); ?>/10</p>
    </main>
    <footer></footer>
  </body>
</html>
