<?php
session_start();

// Wenn nicht eingeloggt → zurück zum Login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.html');
    exit;
}

require_once __DIR__ . '/../php/db.php';

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name_user, lightpoints FROM user WHERE id_user = :id");
$stmt->execute([":id" => $userId]);
$user = $stmt->fetch();

$username    = $user['name_user']    ?? "Traveler";
$lightpoints = $user['lightpoints']  ?? 0;
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

<!-- TOPBAR (GENAU wie in welcome.php) -->
<header class="topbar">

    <!-- Emergency Button -->
    <div class="topbar-left">
        <a href="emergency.html" class="btn topbar-emergency">Emergency</a>
    </div>

    <!-- Logo -->
    <div class="topbar-center">
        <img src="../images/lumora-logo-new.png" alt="Lumora Logo" class="topbar-logo">
    </div>

    <!-- Username, Lightpoints, Logout -->
    <div class="topbar-right">

        <div class="topbar-lightpoints">
            <img src="../images/lightpoint-icon.png" alt="" class="topbar-lightpoints-icon">
            <span class="topbar-lightpoints-label">Lightpoints</span>
            <span class="topbar-lightpoints-value"><?php echo $lightpoints; ?></span>
        </div>

        <span class="topbar-username">Hi, <?php echo htmlspecialchars($username); ?></span>

        <a href="../php/auth/logout.php" class="btn topbar-button">Logout</a>
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
