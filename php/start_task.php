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

// Optional: Kategorie vom Frontend schicken (movement / mindfulness / journaling)
// oder leer lassen => komplett random
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
  if ($category !== '') {
    $stmt = $pdo->prepare("
      SELECT id_quests, islands_id_islands, titel, description, category
      FROM quests
      WHERE category = :cat
      ORDER BY RAND()
      LIMIT 1
    ");
    $stmt->execute([':cat' => $category]);
  } else {
    // komplett random aus allen Kategorien
    $stmt = $pdo->query("
      SELECT id_quests, islands_id_islands, titel, description, category
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

  // Quest-ID in Session merken, damit man nicht irgendeine ID “faken” kann
  $_SESSION['active_quest_id'] = (int)$quest['id_quests'];

  echo json_encode([
    'id' => (int)$quest['id_quests'],
    'islands_id_islands' => (int)$quest['islands_id_islands'],
    'title' => $quest['titel'],
    'description' => $quest['description'],
    'category' => $quest['category']
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'server_error']);
}
