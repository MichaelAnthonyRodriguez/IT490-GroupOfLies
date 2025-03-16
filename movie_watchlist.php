<?php
//This page shows the user's watchlist
?>
<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Make sure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Build the request array.
$request = [
    "type"    => "watchlist",
    "user_id" => $user_id
];

// Create a RabbitMQ client using your configuration.
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
      <h2>My Watchlist</h2>
      <?php if (isset($response["status"]) && $response["status"] === "success"): ?>
        <?php if (isset($response["movies"]) && count($response["movies"]) > 0): ?>
          <ul style="list-style-type: none; padding: 0;">
            <?php foreach ($response["movies"] as $movie): 
                    // Build full poster URL using size w92 (or adjust as needed)
                    $posterUrl = "";
                    if (!empty($movie["poster_path"])) {
                        $posterUrl = "https://image.tmdb.org/t/p/w92" . $movie["poster_path"];
                    }
            ?>
              <li style="margin-bottom: 15px;">
                <?php if ($posterUrl): ?>
                  <img src="<?php echo htmlspecialchars($posterUrl); ?>" alt="Poster for <?php echo htmlspecialchars($movie["title"]); ?>" style="max-width: 100px; vertical-align: middle; margin-right: 10px;">
                <?php endif; ?>
                <a href="movie_details.php?tmdb_id=<?php echo urlencode($movie["tmdb_id"]); ?>">
                  <?php echo htmlspecialchars($movie["title"]); ?>
                </a>
                <span style="vertical-align: middle;"> (Released: <?php echo htmlspecialchars($movie["release_date"]); ?>, Rating: <?php echo htmlspecialchars($movie["vote_average"]); ?>/10)</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Your watchlist is empty.</p>
        <?php endif; ?>
      <?php else: ?>
        <p>Error: <?php echo htmlspecialchars($response["message"] ?? "Unknown error."); ?></p>
      <?php endif; ?>
    </main>
    <footer></footer>
  </body>
</html>
