<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc'; // RabbitMQ library

// Get current year
$currentYear = date("Y");

// Build the request for top movies
$request = [
    "type" => "top_movies",
    "year" => $currentYear
];

// Create a RabbitMQ client using your configuration
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Send the request and capture the response
$response = $client->send_request($request);
?>
<html>
  <head>
    <title>Cinemaniac - Homepage</title>
    <link rel="stylesheet" href="app/static/style.css"/>
  </head>
  <body>
    <!-- header -->
    <header>
      <img id="logo" src="images/logo.png" alt="Cinemaniac Logo">
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
      <h2>Top 10 Movies of <?php echo htmlspecialchars($currentYear); ?></h2>
      <?php
      if (isset($response["status"]) && $response["status"] === "success") {
          if (isset($response["movies"]) && count($response["movies"]) > 0) {
              echo "<ul style='list-style-type: none; padding: 0;'>";
              foreach ($response["movies"] as $movie) {
                  // Construct the poster URL using w92 size.
                  $posterUrl = "";
                  if (!empty($movie['poster_path'])) {
                      $posterUrl = "https://image.tmdb.org/t/p/w92" . $movie['poster_path'];
                  }
                  echo "<li style='margin-bottom: 15px;'>";
                  // Display the poster thumbnail if available.
                  if ($posterUrl) {
                      echo "<img src='" . htmlspecialchars($posterUrl) . "' alt='Poster for " . htmlspecialchars($movie["title"]) . "' style='vertical-align: left; margin-right: 10px;'>";
                  }
                  echo "<br>";
                  // Movie title as a hyperlink to movie_details.php with tmdb_id as GET parameter.
                  echo "<a href='movie_details.php?tmdb_id=" . urlencode($movie['tmdb_id']) . "'>" . htmlspecialchars($movie["title"]) . "</a>";
                  echo " (" . htmlspecialchars($movie["release_date"]) . ") - Popularity: " . htmlspecialchars($movie["popularity"]);
                  echo "</li><br>";
              }
              echo "</ul>";
          } else {
              echo "<p>No movies found for the current year.</p>";
          }
      } else {
          echo "<p>Error: " . htmlspecialchars($response["message"] ?? "Unknown error") . "</p>";
      }
      ?>
    </main>
    <footer></footer>
  </body>
</html>
