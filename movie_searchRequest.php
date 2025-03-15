<?php
//This page shows a searchbar for the user to input
?>
<?php
session_start();
require_once('vendor/autoload.php');
require_once('rabbitMQLib.inc'); // Make sure this file is available

// Get and sanitize the movie title from POST data.
$movie_title = filter_input(INPUT_POST, 'movie_title', FILTER_SANITIZE_STRING);
if(!$movie_title){
    die("Invalid movie title.");
}

// Build the request array.
$request = [
    "type" => "search",
    "movie_title" => $movie_title
];

// Create a RabbitMQ client (using your configuration file and queue name).
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request($request);
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
      <h2>Results for "<?php echo htmlspecialchars($movie_title); ?>"</h2>
      <?php
      if (isset($response["status"]) && $response["status"] === "success") {
          if (isset($response["movies"]) && count($response["movies"]) > 0) {
              echo "<ul>";
              foreach ($response["movies"] as $movie) {
                  // Create a hyperlink that sends tmdb_id as a GET parameter to movie_details.php
                  echo "<li>";
                  echo "<a href='movie_details.php?tmdb_id=" . urlencode($movie['tmdb_id']) . "'>";
                  echo htmlspecialchars($movie["title"]);
                  echo "</a> (" . htmlspecialchars($movie["release_date"]) . ")";
                  echo "</li>";
              }
              echo "</ul>";
          } else {
              echo "<p>No movies found matching that title.</p>";
          }
      } else {
          echo "<p>Error: " . htmlspecialchars($response["message"] ?? "Unknown error") . "</p>";
      }
      ?>
    </main>
    <footer></footer>
  </body>
</html>
