<?php
session_start();
if (empty($_SESSION)) {
    echo "No session data found. You are logged out.";
} else {
    echo "Session still active:";
    print_r($_SESSION);
}
?>
