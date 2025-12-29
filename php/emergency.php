<?php
session_start();

// User ist eingeloggt → zurück in die Welt
if (isset($_SESSION['user_id'])) {
    header('Location: world.php');
    exit;
}

// User ist NICHT eingeloggt → Landing
header('Location: ../html/start.html');
exit;
