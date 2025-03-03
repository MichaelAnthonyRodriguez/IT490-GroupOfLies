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
                <a href="register.php">Register</a>
                <?php 
                    session_start();
                    if (isset($_SESSION['is_valid_admin'])) { 
                ?>
                    <a href="highOrLow.php">Play</a>
                    <a href="logout.php">Logout</a>
                    <p><a>
                        <?php
                            // require_once('userData.php');
                            // userData();
                        ?>
                    </a></p>
                <?php } else { ?>
                    <a href="login.php">Login</a>
                <?php } ?>              
            </nav>
        </header>
        <!-- main elements -->
        <main>
            <p>Register</p>
            
            <!-- Form to send a custom message -->
            <form action="send_message.php" method="POST">
                <label for="message">Enter Message:</label>
                <input type="text" id="message" name="message" required>
                <button type="submit">Send</button>
            </form>
        </main>
        <hr>
        <hr>
        <footer></footer>
        <hr>
    </body>
</html>
