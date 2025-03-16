<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

// Process form submissions BEFORE any output (for proper header redirection).
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Determine which form was submitted by the hidden 'action' field.
    $action = $_POST['action'] ?? "";
    
    if ($action == "update_watchlist") {
        // Process watchlist update then redirect.
        $watchlist = isset($_POST['watchlist']) && $_POST['watchlist'] == "1" ? 1 : 0;
        $watchlistRequest = [
            "type"    => "update_watchlist",
            "user_id" => $user_id,
            "tmdb_id" => $tmdb_id,
            "watchlist" => $watchlist
        ];
        $res = $client->send_request($watchlistRequest);
        // You might optionally set a session flash message with $res["message"]
        header("Location: movie_watchlist.php");
        exit();
    } elseif ($action == "update_rating") {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $ratingRequest = [
            "type"    => "update_rating",
            "user_id" => $user_id,
            "tmdb_id" => $tmdb_id,
            "rating"  => $rating
        ];
        $res = $client->send_request($ratingRequest);
        if (isset($res["status"]) && $res["status"] === "success") {
            $feedbackMessage .= "Rating submitted successfully. ";
        } else {
            $feedbackMessage .= "Rating error: " . htmlspecialchars($res["message"] ?? "Unknown error") . " ";
        }
    } elseif ($action == "update_review") {
        $review = trim($_POST['review']);
        if (empty($review)) {
            $feedbackMessage .= "Review field cannot be empty. ";
        } else {
            $reviewRequest = [
                "type"    => "update_review",
                "user_id" => $user_id,
                "tmdb_id" => $tmdb_id,
                "review"  => $review
            ];
            $res = $client->send_request($reviewRequest);
            if (isset($res["status"]) && $res["status"] === "success") {
                $feedbackMessage .= "Review submitted successfully. ";
            } else {
                $feedbackMessage .= "Review error: " . htmlspecialchars($res["message"] ?? "Unknown error") . " ";
            }
        }
    }
    // After processing rating or review, re-fetch full details.
    $response = $client->send_request($request);
} else {
    $response = $client->send_request($request);
}

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
        <?php if(isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true): ?>
          <a href="movie_watchlist.php">My Watchlist</a>
     
