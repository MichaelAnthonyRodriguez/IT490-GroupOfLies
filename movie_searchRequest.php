<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc'; // RabbitMQ library

// Retrieve and sanitize the movie title from the form.
$movie_title = filter_input(INPUT_POST, 'movie_title', FILTER_SANITIZE_STRING);
if (!$movie_title) {
    die("Invalid movie title input.");
}

// Build the request array for searching movies.
$request = [
    "type" => "search",
    "movie_title" => $movie_title
];

// Create a RabbitMQ client using your configuration.
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Send the request and capture the response.
$response = $client->send_request($request);
?>
<html>
    <head>
        <title>Cinemaniac - Search Results</title>
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
                echo "<br>";
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
                        // Movie title as a hyperlink to movie_details.php with tmdb_id as a GET parameter.
                        echo "<a href='movie_details.php?tmdb_id=" . urlencode($movie['tmdb_id']) . "' style='vertical-align: middle;'>" . htmlspecialchars($movie["title"]) . " (" . htmlspecialchars($movie["release_date"]) . ")" . "</a>";
                        echo "</li>";
                        echo "<br>";
                    }
                    echo "</ul>";
                    echo "<br>";
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
