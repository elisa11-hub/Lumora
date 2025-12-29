<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /Lumora/php/world.php');
    exit;
}

header('Location: /Lumora/html/start.html');
exit;
