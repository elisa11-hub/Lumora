<?php
session_start();

// Wenn kein User eingeloggt ist → zurück zum Login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit;
}

// DB-Verbindung holen
require_once __DIR__ . '/../php/db.php';

$userId = (int) $_SESSION['user_id'];
$username = 'Traveler';
$lightpoints = null;
$lastLogin = null;

try {
    // Erst versuchen wir, auch lichtpunkte zu laden
    $stmt = $pdo->prepare('
        SELECT name_user, lightpoints, last_login
        FROM user
        WHERE id_user = :id
    ');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // Falls die Spalte "lichtpunkte" (noch) nicht existiert, fallback:
    if (strpos($e->getMessage(), 'lightpoints') !== false) {
        $stmt = $pdo->prepare('
            SELECT name_user, last_login
            FROM user
            WHERE id_user = :id
        ');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
    } else {
        // anderer DB-Fehler → hart abbrechen
        die('Database error: ' . htmlspecialchars($e->getMessage()));
    }
}

if (!$user) {
    // User nicht gefunden → Session aufräumen und zurück zum Login
    session_unset();
    session_destroy();
    header('Location: auth/login.html');
    exit;
}

$username   = $user['name_user'] ?? 'Traveler';
$lightpoints = isset($user['lightpoints']) ? (int)$user['lightpoints'] : null;
$lastLogin   = $user['last_login'] ?? null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Welcome to Lumora</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <div class="overlay"></div>

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <a href="emergency.html" class="btn topbar-emergency">Emergency</a>
    </div>

    <div class="topbar-center">
      <img src="../images/lumora-logo-new.png" alt="Lumora Logo" class="topbar-logo">
    </div>

    <div class="topbar-right">
      <span class="topbar-username">
        Hi, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
      </span>
      <a href="../php/auth/logout.php" class="btn topbar-button">Logout</a>
    </div>
  </header>

  <!-- HAUPT-INHALT -->
  <main class="welcome-layout">
    <section class="welcome-card">
      <h1 class="welcome-title">
        Welcome back, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>.
      </h1>

      <p class="welcome-subtitle">
        Your journey through the islands continues. Take a moment to arrive, then follow your inner light.
      </p>

      <div class="welcome-stats">
        <?php if ($lightpoints !== null): ?>
          <div class="stat-box">
            <div class="stat-label">Light points</div>
            <div class="stat-value"><?php echo $lightpoints; ?></div>
          </div>
        <?php endif; ?>

        <?php if ($lastLogin !== null): ?>
          <div class="stat-box">
            <div class="stat-label">Last login</div>
            <div class="stat-value stat-value-small">
              <?php echo htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="stat-box">
          <div class="stat-label">Current world</div>
          <div class="stat-value stat-value-small">
            Islands of inner strength
          </div>
        </div>
      </div>

      <div class="welcome-actions">
        <a href="startpage.html" class="btn btn-primary welcome-play-btn">
          Continue your journey
        </a>
      </div>
    </section>
  </main>
</body>
</html>


