<?php
session_start();
?>

<html>
    <head>
        <title>Higher or Lower</title>
        <link rel="stylesheet" href="app/static/style.css"/>
    </head>
    <body>
        <!-- header -->
        <header>
            <img id="logo" src="images/logo.png">
            <h3>Higher or Lower</h3>
            <nav class="menu">
                <a href="index.php">Home</a>

                <?php if (isset($_SESSION['is_valid_admin']) && $_SESSION['is_valid_admin'] === true) { ?>
                    <a href="highOrLow.php">Play</a>
                    <a href="logout.php">Logout</a>
                    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
                <?php } else { ?>
                    <a href="register.php">Register</a>
                    <a href="login.php">Login</a>
                <?php } ?>
            </nav>
        </header>

        <!-- main elements -->
        <main>
            <p>Welcome to Higher or Lower!</p>
                <p>You're logged in!</p>
        </main>
        
        <hr>
        <footer></footer>
    </body>
</html>
