<?php
//This page shows recommended shows based off of the last movie the user wishlist
?>
<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';  // RabbitMQ library

// Build a request for the top 10 movies for the current year.
$currentYear = date("Y");
$request = [
    "type" => "top_movies",
    "year" => $currentYear
];

// Create a RabbitMQ client (using your configuration file).
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");
$response = $client->send_request($request);
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
    <main>
      <h2>Top 10 Movies of <?php echo htmlspecialchars($currentYear); ?></h2>
      <?php if(isset($response["status"]) && $response["status"] === "success"): ?>
        <ul style="list-style-type: none; padding: 0;">
          <?php foreach($response["movies"] as $movie): 
                  // Build full poster URL using a chosen size (for example, w342)
                  $posterUrl = "";
                  if(!empty($movie['poster_path'])){
                      $posterUrl = "https://image.tmdb.org/t/p/w342" . $movie['poster_path'];
                  }
          ?>
            <li style="margin-bottom: 15px;">
              <?php if($posterUrl): ?>
                <img src="<?php echo htmlspecialchars($posterUrl); ?>" alt="Poster for <?php echo htmlspecialchars($movie['title']); ?>" style="max-width: 100px; vertical-align: middle; margin-right: 10px;">
              <?php endif; ?>
              <a href="movie_details.php?tmdb_id=<?php echo urlencode($movie['tmdb_id']); ?>" style="vertical-align: middle;">
                <?php echo htmlspecialchars($movie['title']); ?>
              </a>
              <span style="vertical-align: middle;"> - Popularity: <?php echo htmlspecialchars($movie['popularity']); ?>, Release Date: <?php echo htmlspecialchars($movie['release_date']); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p><?php echo htmlspecialchars($response["message"] ?? "Error retrieving top movies."); ?></p>
      <?php endif; ?>
    </main>
    <footer></footer>
  </body>
</html>
