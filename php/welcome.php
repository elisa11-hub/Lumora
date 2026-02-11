<?php
session_start();

// Wenn kein User eingeloggt ist → zurück zum Login
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
    // User-Daten + Lightpoints über JOIN holen
    $stmt = $pdo->prepare("
        SELECT 
            u.name_user,
            u.last_login,
            COALESCE(SUM(lp.lightpoints), 0) AS total_lightpoints
        FROM user u
        LEFT JOIN lightpoints lp
            ON lp.user_id_user = u.id_user
        WHERE u.id_user = :id
        GROUP BY u.id_user;
    ");

    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $username    = $row['name_user'];
        $lastLogin   = $row['last_login'];
        $lightpoints = (int)$row['total_lightpoints'];
    }

} catch (PDOException $e) {

    // Wenn das Problem *genau* an der Spalte lightpoints liegt - Fallback ohne JOIN
    if (strpos($e->getMessage(), 'lightpoints') !== false) {

        // Nur name_user + last_login holen, Lightpoints auf 0 setzen
        $stmt = $pdo->prepare("
            SELECT name_user, last_login
            FROM user
            WHERE id_user = :id
        ");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $username    = $row['name_user'];
            $lastLogin   = $row['last_login'];
            $lightpoints = null;   // oder 0, je nachdem was du anzeigen willst
        }

    } else {
        // anderer DB-Fehler → ausgeben
        die('Database error: ' . htmlspecialchars($e->getMessage()));
    }
}
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
      <a href="/Lumora/html/emergency.html" class="btn topbar-emergency" onclick="window.open(this.href, 'emergency', 'width=1400,height=900'); return false;">Emergency</a>
    </div>

    <div class="topbar-center">
      <img src="../images/lumora-logo.png" alt="Lumora Logo" class="topbar-logo">
    </div>

    <div class="topbar-right">
      <span class="topbar-username">
        Hi,&nbsp;<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
      </span>
<a href="../php/auth/logout.php" class="topbar-button">
  Logout
</a>

    </div>
  </header>

  <!-- HAUPT-INHALT -->
  <main class="welcome-screen">
    <section class="welcome-card">
      <!-- linke Seite: Text / Greeting -->
      <div class="welcome-main">
        <p class="welcome-tagline">Welcome back to Lumora</p>
        <h1 class="welcome-title">
          Good to see you, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
        </h1>

        <p class="welcome-subtitle">
          Your journey through the islands continues. <br> Take a breath, arrive here for a moment,<br>
          and then follow your inner light at your own pace.
        </p>


        <div class="welcome-actions">
          <a href="../php/world.php" class="btn-lumora btn-lumora-primary btn-lumora-lg welcome-play-btn">
          Continue your journey
          </a>
        </div>


      <!-- rechte Seite: Stats / Lightpoints -->
      <aside class="welcome-stats">
        <div class="stat-box reminder-highlight-soft">
          <div class="stat-label">Today’s gentle reminder</div>
          <p class="reminder-quote">
            You don’t have to be perfect to keep going. <br>
            Showing up is already an act of courage.
          </p>
        </div>
      </aside>
    </section>

    <div class="hud-meta">
  <?php if ($lastLogin !== null): ?>
    <div class="hud-item">
      <span class="hud-label">Last login</span>
      <span class="hud-value"><?php echo htmlspecialchars($lastLogin, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
  <?php endif; ?>

  <div class="hud-item">
    <span class="hud-label">Current world</span>
    <span class="hud-value">Islands of Selflove</span>
  </div>

  <div class="hud-item">
    <span class="hud-label">Lightpoints</span>
    <span class="hud-value">
      <?php echo $lightpoints !== null ? htmlspecialchars($lightpoints, ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
    </span>
  </div>
</div>

  </main>
</body>
</html>