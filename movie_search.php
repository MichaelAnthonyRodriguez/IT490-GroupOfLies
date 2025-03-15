<?php
//This page shows a searchbar for the user to input
?>
<?php
session_start();
// require_once('checkSession.php');
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

        <!-- main elements -->
        <main>
            <form action="movie_searchRequest.php" method="POST">
                <label for="movie_title">Movie Title:</label>
                <input type="text" name="movie_title" id="movie_title" required>
                <input type="submit" value="Search">
            </form>
        </main>
        
        <hr>
        <footer></footer>
    </body>
</html>
