<?php
session_start();

// Wenn kein User eingeloggt ist → zurück zum Login (HTML liegt im html/auth/)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/auth/login.html');
    exit;
}

require_once __DIR__ . '/db.php';

$userId     = (int) $_SESSION['user_id'];
$username   = 'Traveler';
$lightpoints = null;
$lastLogin   = null;

try {
    $stmt = $pdo->prepare('
        SELECT name_user, lightpoints, last_login
        FROM user
        WHERE id_user = :id
    ');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    // Falls Spalte lightpoints noch nicht existiert
    if (strpos($e->getMessage(), 'lightpoints') !== false) {
        $stmt = $pdo->prepare('
            SELECT name_user, last_login
            FROM user
            WHERE id_user = :id
        ');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
    } else {
        die('Database error: ' . htmlspecialchars($e->getMessage()));
    }
}

if (!$user) {
    session_unset();
    session_destroy();
    header('Location: ../html/auth/login.html');
    exit;
}

$username    = $user['name_user'] ?? 'Traveler';
$lightpoints = isset($user['lightpoints']) ? (int)$user['lightpoints'] : null;
$lastLogin   = $user['last_login'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
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
      <a href="../html/emergency.html" class="btn topbar-emergency">Emergency</a>
    </div>

    <div class="topbar-center">
      <img src="../images/lumora-logo-new.png" alt="Lumora Logo" class="topbar-logo">
    </div>

    <div class="topbar-right">
      <span class="topbar-username">
        Hi,&nbsp;<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
      </span>
      <a href="../php/auth/logout.php" class="btn topbar-button">Logout</a>
    </div>
  </header>

  <!-- HAUPT-INHALT -->
  <main class="welcome-screen">
    <section class="welcome-card">
      <!-- linke Seite: Text / Greeting -->
      <div class="welcome-main">
        <p class="welcome-tagline">Welcome back to Lumora</p>
        <h1 class="welcome-title">
          Good to see you, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>.
        </h1>

        <p class="welcome-subtitle">
          Your journey through the islands continues. Take a breath, arrive here for a moment,
          and then follow your inner light at your own pace.
        </p>

        <div class="welcome-meta">
          <?php if ($lastLogin !== null): ?>
            <div class="meta-item">
              <span class="meta-label">Last login</span>
              <span class="meta-value">
                <?php echo htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </div>
          <?php endif; ?>

          <div class="meta-item">
            <span class="meta-label">Current world</span>
            <span class="meta-value">Islands of inner strength</span>
          </div>
        </div>

        <div class="welcome-actions">
          <a href="../world.php" class="btn btn-primary welcome-play-btn">
            Continue your journey
          </a>
        </div>
      </div>

      <!-- rechte Seite: Stats / Lightpoints -->
      <aside class="welcome-stats">
        <?php if ($lightpoints !== null): ?>
          <div class="stat-box big">
            <div class="stat-label">Light points</div>
            <div class="stat-value"><?php echo $lightpoints; ?></div>
            <p class="stat-hint">
              Each point is a moment you took for yourself.
            </p>
          </div>
        <?php endif; ?>

        <div class="stat-box reminder-highlight-soft">
          <div class="stat-label">Today’s gentle reminder</div>
          <p class="reminder-quote">
            You don’t have to be perfect to keep going.
            Showing up is already an act of courage.
          </p>
        </div>
      </aside>
    </section>
  </main>
</body>
</html>