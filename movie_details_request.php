<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'vendor/autoload.php';
require_once 'rabbitMQLib.inc';

// Ensure tmdb_id is provided via POST.
if (!isset($_POST['tmdb_id'])) {
    die("Error: No movie specified.");
}
$tmdb_id = intval($_POST['tmdb_id']);

// Ensure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Create a RabbitMQ client.
$client = new rabbitMQClient("testRabbitMQ.ini", "testServer");

// Determine which form was submitted.
$action = $_POST['action'] ?? "";

if ($action === "update_rating") {
    // Process rating update.
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $ratingRequest = [
        "type"    => "update_rating",
        "user_id" => $user_id,
        "tmdb_id" => $tmdb_id,
        "rating"  => $rating
    ];
    $res = $client->send_request($ratingRequest);
    // Optionally, log or store $res feedback.
} elseif ($action === "update_review") {
    // Process review update.
    $review = trim($_POST['review']);
    if (!empty($review)) {
        $reviewRequest = [
            "type"    => "update_review",
            "user_id" => $user_id,
            "tmdb_id" => $tmdb_id,
            "review"  => $review
        ];
        $res = $client->send_request($reviewRequest);
        // Optionally, log or store $res feedback.
    }
    // If review is empty, you might add error handling here.
}

// After processing, redirect back to the movie details page.
header("Location: movie_details.php?tmdb_id=" . $tmdb_id);
exit();
?>
