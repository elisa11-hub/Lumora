<?php
session_start();
require_once __DIR__ . '/db.php';   //DB-Verbindung

// Hilfsfunktion zur Passwortstärke
function is_strong_password($password) {
    return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password);
}

//Formularwerte holen
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

//Grundprüfung
if ($username === '' || $password === '' || $password2 === '') {
    die('Please complete all fields to let your journey begin');
}

if ($password !== $password2) {
    die('The passwords do not match');
}

if (!is_strong_password($password)) {
    die('Let your light shine strong—choose a password with 8+ characters, brightened by uppercase & lowercase letters, a number, and a special symbol');
}

//Passwort hashen
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    //neuen Benutzer speichern
    $stmt = $pdo->prepare("
        INSERT INTO user (name_user, passwort_user. last_login)
        VALUES (:name, :passwort, NOW())
    ");

    $stmt->execute([
        ':name'     => $username,
        ':passwort' => $hash
    ]);

    //Weiterleiten zum Login
    header("Location: ../html/login.html");
    exit;

} catch (PDOException $e) {
    // UNIQUE-Fehler: Username bereits vergeben
    if ($e->getCode() === "23000") {
        die("This name is already in use — choose another to let your light stand out");
    }

    // sonstiger Fehler
    die("Database error: " . $e->getMessage());
}
