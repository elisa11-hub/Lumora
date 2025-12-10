<?php
session_start();

// Wenn nicht eingeloggt → zurück zum Login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../html/auth/login.html');
    exit;
}

require_once __DIR__ . '/db.php';

$userId    = (int)$_SESSION['user_id'];
$username  = 'Traveler';
$lightpoints = 0;

try {
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

    // Wenn das Problem *genau* an der Spalte lightpoints liegt → Fallback ohne JOIN
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
  <title>Lumora – Inner Islands</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="overlay"></div>

<!-- TOPBAR -->
<header class="topbar">

    <!-- Emergency Button -->
    <div class="topbar-left">
       <a href="../html/emergency.html" class="btn topbar-emergency">Emergency</a>
    </div>

    <!-- Logo -->
    <div class="topbar-center">
        <img src="../images/lumora-logo-new.png" alt="Lumora Logo" class="topbar-logo">
    </div>

    <!-- Username, Lightpoints, Logout -->
    <div class="topbar-right">

        <span class="topbar-username">Hi, <?php echo htmlspecialchars($username); ?></span>

        <div class="topbar-lightpoints">
            <span class="topbar-lightpoints-label">Lightpoints</span>
            <span class="topbar-lightpoints-value"><?php echo $lightpoints; ?></span>
        </div>

        
        <a href="auth/logout.php" class="btn topbar-button">Logout</a>
    </div>
</header>



<!-- HAUPT-INHALT -->
<main class="world-screen">

    <!-- Title Area -->
    <section class="world-header">
      <h1 class="world-title">Choose your next island</h1>
      <p class="world-subline">Every island holds a part of your inner light.</p>
    </section>

    <!-- Island Grid -->
    <section class="island-grid enhanced-islands">

      <!-- 1. SELF-LOVE -->
      <a href="../html/islands/selflove.html" class="island-card">
        <img src="../images/island-selflove.png" alt="Island of Self-Love">
        <h3 style="color:#ff9ac0;">Island of Self-Love</h3>
        <p>A warm, gentle place where you learn to soften your heart toward yourself.</p>
      </a>

      <!-- 2. TRUST -->
      <a href="../html/islands/trust.html" class="island-card">
        <img src="../images/island-trust.png" alt="Island of Trust">
        <h3 style="color:#9ed8ff;">Island of Trust</h3>
        <p>A quiet island where courage grows naturally, step by step.</p>
      </a>

      <!-- 3. SELF-EMBRACE -->
      <a href="../html/islands/selfembrace.html" class="island-card">
        <img src="../images/island-selfembrace.png" alt="Island of Self-Embrace">
        <h3 style="color:#78c2ff;">Island of Self-Embrace</h3>
        <p>A safe place inviting you to hold all parts of yourself with compassion.</p>
      </a>

      <!-- 4. PEACE -->
      <a href="../html/islands/peace.html" class="island-card">
        <img src="../images/island-peace.png" alt="Island of Peace">
        <h3 style="color:#bfa6ff;">Island of Peace</h3>
        <p>A quiet sanctuary of stillness, breathing, grounding and gentle presence.</p>
      </a>

      <!-- 5. HEALING -->
      <a href="../html/islands/healing.html" class="island-card">
        <img src="../images/island-healing.png" alt="Island of Healing">
        <h3 style="color:#98f0b8;">Island of Healing</h3>
        <p>A green place where wounds soften and renewal begins step by step.</p>
      </a>

    </section>

</main>

</body>
</html>
