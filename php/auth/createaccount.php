<?php
session_start();
require_once __DIR__ . '/../db.php';   // DB-Verbindung

//Formularwerte holen
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

function fail_and_redirect($msg, $username = '') {
    $params = http_build_query([
        'error'    => $msg,
        'username' => $username
    ]);
    header("Location: ../../html/auth/createaccount.html?$params");
    exit;
}

// Grundprüfung
if ($username === '' || $password === '') {
    fail_and_redirect('Please complete all fields to let your journey begin', $username);
}

// Passwort-Regel: mindestens 4 Zeichen
if (mb_strlen($password) < 4) {
    fail_and_redirect('Your password must have at least 4 characters.', $username);
}

// Passwort hashen
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Username schon vorab prüfen (Vermeidung Doppelanmeldung)
    $check = $pdo->prepare("SELECT 1 FROM user WHERE name_user = :name LIMIT 1");
    $check->execute([':name' => $username]);
    if ($check->fetchColumn()) {
        fail_and_redirect("This name is already in use — choose another to let your light stand out", $username);
    }

    // neuen Benutzer speichern
    $stmt = $pdo->prepare("
        INSERT INTO user (name_user, passwort_user, last_login)
        VALUES (:name, :passwort, NOW())
    ");

    $stmt->execute([
        ':name'     => $username,
        ':passwort' => $hash
    ]);

    // Weiterleiten zum Login
    header("Location: ../../html/auth/login.html");
    exit;

} catch (PDOException $e) {
    // falls du DB-seitig UNIQUE auf name_user hast, bleibt das als Sicherheit ok:
    if ($e->getCode() === "23000") {
        fail_and_redirect("This name is already in use — choose another to let your light stand out", $username);
    }
    fail_and_redirect("Database error: " . $e->getMessage(), $username);
}
