<?php
session_start();
require_once __DIR__ . '/../db.php';   //DB-Verbindung

// Hilfsfunktion zur Passwortstärke
function is_strong_password($password) {
    return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password);
}

//Formularwerte holen
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

function fail_and_redirect($msg, $username = '') {
    $params = http_build_query([
        'error'    => $msg,
        'username' => $username
    ]);
    header("Location: ../../html/auth/createaccount.html?$params");
    exit;
}


//Grundprüfung
if ($username === '' || $password === '' || $password2 === '') {
    fail_and_redirect('Please complete all fields to let your journey begin', $username);
}

if ($password !== $password2) {
    fail_and_redirect('The passwords do not match', $username);
}

if (!is_strong_password($password)) {
    fail_and_redirect('Let your light shine strong — choose a password with 8+ characters, uppercase, lowercase, a number and a special symbol.', $username);
}

//Passwort hashen
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    //neuen Benutzer speichern
    $stmt = $pdo->prepare("
        INSERT INTO user (name_user, passwort_user, last_login)
        VALUES (:name, :passwort, NOW())
    ");

    $stmt->execute([
        ':name'     => $username,
        ':passwort' => $hash
    ]);

    //Weiterleiten zum Login
    header("Location: ../../html/auth/login.html");
    exit;

} catch (PDOException $e) {
    // UNIQUE-Fehler: Username bereits vergeben
    if ($e->getCode() === "23000") {
        fail_and_redirect("This name is already in use — choose another to let your light stand out", $username);
    }

    // sonstiger Fehler
    fail_and_redirect("Database error: " . $e->getMessage(), $username);
}
