<?php
session_start();
require_once __DIR__ . '/../db.php';   // DB verbinden

//POST-Daten einsammeln
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

//Grundprüfung
if ($username === '' || $password === '') {
    die('Please enter your username and password');
}

try {
    //User aus DB holen
    $stmt = $pdo->prepare('SELECT * FROM user WHERE name_user = :name');
    $stmt->execute([':name' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        die('User does not exist');
    }

    //Passwort prüfen
    if (!password_verify($password, $user['passwort_user'])) {
        die('Password is incorrect');
    }

    //Login-Datum aktualisieren
    $pdo->prepare('UPDATE user SET last_login = NOW() WHERE id_user = :id')
        ->execute([':id' => $user['id_user']]);

    //Session speichern
    $_SESSION['user_id'] = $user['id_user'];

    //Weiterleiten
    header('Location: ../../html/startpage.html');
    exit;

} catch (PDOException $e) {
    die('database error: ' . $e->getMessage());
}
