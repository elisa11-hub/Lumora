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

$body = json_decode(file_get_contents('php://input'), true);
$questId = isset($body['quest_id']) ? (int)$body['quest_id'] : 0;

if ($questId <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'missing_quest_id']);
  exit;
}

// Sicherheitscheck: Nur die Quest abschließen, die auch “aktiv” gestartet wurde
if (!isset($_SESSION['active_quest_id']) || (int)$_SESSION['active_quest_id'] !== $questId) {
  http_response_code(403);
  echo json_encode(['error' => 'quest_not_active']);
  exit;
}

try {
  // Quest-Daten laden (für islands_id_islands)
  $stmt = $pdo->prepare("
    SELECT id_quests, islands_id_islands
    FROM quests
    WHERE id_quests = :qid
    LIMIT 1
  ");
  $stmt->execute([':qid' => $questId]);
  $q = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$q) {
    http_response_code(404);
    echo json_encode(['error' => 'quest_not_found']);
    exit;
  }

  $islandId = (int)$q['islands_id_islands'];

  // 1 Lightpoint eintragen
  $ins = $pdo->prepare("
    INSERT INTO lightpoints (user_id_user, islands_id_islands, quests_id_quests, lightpoints, created_at)
    VALUES (:uid, :iid, :qid, 1, NOW())
  ");
  $ins->execute([
    ':uid' => $userId,
    ':iid' => $islandId,
    ':qid' => $questId
  ]);

  // Session “active quest” löschen
  unset($_SESSION['active_quest_id']);

  // neuen Gesamtstand zurückgeben
  $sum = $pdo->prepare("
    SELECT COALESCE(SUM(lightpoints),0) AS total
    FROM lightpoints
    WHERE user_id_user = :uid
  ");
  $sum->execute([':uid' => $userId]);
  $total = (int)$sum->fetchColumn();

  echo json_encode(['ok' => true, 'total_lightpoints' => $total]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'server_error']);
}
