<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'not_logged_in']);
  exit;
}

require_once __DIR__ . '/db.php';

$userId = (int)$_SESSION['user_id'];

// Tageslimit prüfen (max 3 pro Tag)
$limitStmt = $pdo->prepare("
  SELECT COALESCE(SUM(lightpoints),0) AS today_points
  FROM lightpoints
  WHERE user_id_user = :uid
    AND DATE(created_at) = CURDATE()
");
$limitStmt->execute(['uid' => $userId]);
$todayPoints = (int)$limitStmt->fetchColumn();

if ($todayPoints >= 3) {
  http_response_code(429);
  echo json_encode(['error' => 'limit_reached', 'remaining_today' => 0]);
  exit;
}


// Optional: Kategorie vom Frontend schicken (movement / mindfulness / journaling)
// oder leer lassen => komplett random
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
  if ($category !== '') {
    $stmt = $pdo->prepare("
      SELECT id_quests, islands_id_islands, titel, description, category, duration_seconds, design_variant
      FROM quests
      WHERE category = :cat
      ORDER BY RAND()
      LIMIT 1
    ");
    $stmt->execute([':cat' => $category]);
  } else {
    // komplett random aus allen Kategorien
    $stmt = $pdo->query("
      SELECT id_quests, islands_id_islands, titel, description, category, duration_seconds, design_variant
      FROM quests
      ORDER BY RAND()
      LIMIT 1
    ");
  }

  $quest = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$quest) {
    http_response_code(404);
    echo json_encode(['error' => 'no_quest_found']);
    exit;
  }

$duration = isset($quest['duration_seconds']) ? (int)$quest['duration_seconds'] : 60;
$variant  = isset($quest['design_variant']) ? $quest['design_variant'] : 'stopwatch';

  // Quest-ID in Session merken, damit man nicht irgendeine ID “faken” kann
  $_SESSION['active_quest_id'] = (int)$quest['id_quests'];
$remaining = 3 - $todayPoints;
echo json_encode([
  'id' => (int)$quest['id_quests'],
  'islands_id_islands' => (int)$quest['islands_id_islands'],
  'title' => $quest['titel'],
  'description' => $quest['description'],
  'category' => $quest['category'],
  'duration_seconds' => $duration,
  'design_variant' => $variant,
  'remaining_today' => $remaining
]);


} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'server_error']);
}
