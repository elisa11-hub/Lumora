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

// 1) alle Inseln laden
$islands = [];
$stmt = $pdo->query("
    SELECT id_islands, name_islands, description, order_index, theme_color, theme_sound, unlock_cost
    FROM islands
    ORDER BY order_index ASC
");
$islands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) bereits freigeschaltete Inseln des Users laden
$unlockedIds = [];
$stmt = $pdo->prepare("
    SELECT islands_id_islands
    FROM user_has_islands
    WHERE user_id_user = :uid
");
$stmt->execute(['uid' => $userId]);
$unlockedIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

// 3) Optional: automatisch neue Inseln persistieren, sobald Schwelle erreicht ist
// (INSERT IGNORE klappt gut, wenn (user_id_user, islands_id_islands) UNIQUE/PK ist)
$insStmt = $pdo->prepare("
    INSERT IGNORE INTO user_has_islands (user_id_user, islands_id_islands)
    VALUES (:uid, :iid)
");



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
        <a href="/Lumora/html/emergency.html" class="btn topbar-emergency" onclick="window.open(this.href, 'emergency', 'width=480,height=700'); return false;">Emergency</a>
    </div>

    <!-- Logo -->
    <div class="topbar-center">
        <img src="../images/lumora-logo.png" alt="Lumora Logo" class="topbar-logo">
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
    <section class="island-grid">
<?php
// Helper: slug für Dateinamen (selflove, trust, ...)
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '', $text);
    return $text;
}

foreach ($islands as $island) {
    $id   = (int)$island['id_islands'];
    $name = $island['name_islands'];
    $cost = (int)$island['unlock_cost'];

    // Freigeschaltet wenn:
    // - in user_has_islands ODER
    // - lightpoints >= unlock_cost (Schwellenlogik)
    $isUnlocked = in_array($id, $unlockedIds, true) || ($lightpoints >= $cost);

    // Persistieren, wenn gerade (durch Punkte) freigeschaltet
    if ($isUnlocked && !in_array($id, $unlockedIds, true)) {
        $insStmt->execute(['uid' => $userId, 'iid' => $id]);
        $unlockedIds[] = $id;
    }

    // Dateinamen/Assets: passe das an deine echten Dateien an
    // Beispiel: ../html/islands/trust.html und ../images/island-trust.png
    $slug = slugify($name); // wenn name z.B. "Self-Love" -> "selflove"
    $soundKey = !empty($island['theme_sound']) ? $island['theme_sound'] : $slug; // Fallback auf Slug wenn kein theme_sound gesetzt
    $href = "../html/islands/{$slug}.html";
    $img  = "../images/island-{$slug}.png";

    $classes = "island" . ($isUnlocked ? " unlocked" : " locked");
    $ariaDisabled = $isUnlocked ? "false" : "true";
    $tabIndex = $isUnlocked ? "0" : "-1";

    // Wenn locked: href auf #, damit man nicht navigiert (zusätzlich CSS pointer-events)
    $finalHref = $isUnlocked ? $href : "#";
?>
  <a
    href="<?= htmlspecialchars($finalHref) ?>"
    class="<?= htmlspecialchars($classes) ?>"
    aria-disabled="<?= $ariaDisabled ?>"
    tabindex="<?= $tabIndex ?>"
    data-unlock-cost="<?= (int)$cost ?>"
    $soundKey = $island['theme_sound'] ?: $slug;
    data-island="<?= htmlspecialchars($soundKey) ?>"
  >
    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" class="island-image">

    <?php if (!$isUnlocked): ?>
      <div class="island-lock-overlay">
        <div class="island-lock-badge">🔒 <?= (int)$cost ?> LP</div>
      </div>
    <?php endif; ?>
  </a>
<?php } ?>
</section>



</main>

<!-- JavaScript für Insel-Auswahl und Audio -->
<script type="module">
  import { unlockAudio, setSelectedIsland } from "/Lumora/js/audioManager.js";

  document.querySelectorAll("a.island.unlocked[data-island]").forEach(card => {
    card.addEventListener("click", async () => {
      await unlockAudio();
      setSelectedIsland(card.dataset.island);
    });
  });
</script>

<script type="module" src="/Lumora/js/audioBootstrap.js"></script>


</body>
</html>
