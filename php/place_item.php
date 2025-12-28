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
$itemId = isset($body['item_id']) ? (int)$body['item_id'] : 0;
$posX   = isset($body['pos_x']) ? (float)$body['pos_x'] : null;
$posY   = isset($body['pos_y']) ? (float)$body['pos_y'] : null;

if ($itemId <= 0 || $posX === null || $posY === null) {
  http_response_code(400);
  echo json_encode(['error' => 'bad_request']);
  exit;
}

/**
 * Security/Validation:
 * - pos_x / pos_y sind RELATIV zum Placement-Container (0..1)
 * - dadurch bist du unabhängig von Bildschirmgrößen
 */
if ($posX < 0 || $posX > 1 || $posY < 0 || $posY > 1) {
  http_response_code(400);
  echo json_encode(['error' => 'out_of_bounds']);
  exit;
}

try {
  $pdo->beginTransaction();

  // 1) Check: hat user >= 1 verfügbaren lightpoint?
  $sum = $pdo->prepare("
    SELECT COALESCE(SUM(lightpoints),0) AS total
    FROM lightpoints
    WHERE user_id_user = :uid
  ");
  $sum->execute(['uid' => $userId]);
  $total = (int)$sum->fetchColumn();

  if ($total < 1) {
    $pdo->rollBack();
    http_response_code(403);
    echo json_encode(['error' => 'not_enough_lightpoints', 'total_lightpoints' => $total]);
    exit;
  }

  // 2) Insert user_item (Position speichern)
  $ins = $pdo->prepare("
    INSERT INTO user_items (user_id_user, items_id_items, acquired_at, pos_x, pos_y, rotation, scale)
    VALUES (:uid, :iid, NOW(), :x, :y, 0, 1)
  ");
  $ins->execute([
    'uid' => $userId,
    'iid' => $itemId,
    'x'   => $posX,
    'y'   => $posY
  ]);

  $userItemId = (int)$pdo->lastInsertId();

  // 3) Lightpoint “ausgeben” als -1 Eintrag
  $spend = $pdo->prepare("
    INSERT INTO lightpoints (user_id_user, islands_id_islands, quests_id_quests, lightpoints, created_at)
    VALUES (:uid, NULL, NULL, -1, NOW())
  ");
  $spend->execute(['uid' => $userId]);

  // 4) neuen Stand zurückgeben
  $sum2 = $pdo->prepare("
    SELECT COALESCE(SUM(lightpoints),0) AS total
    FROM lightpoints
    WHERE user_id_user = :uid
  ");
  $sum2->execute(['uid' => $userId]);
  $newTotal = (int)$sum2->fetchColumn();

  $pdo->commit();

  echo json_encode([
    'ok' => true,
    'id_user_items' => $userItemId,
    'total_lightpoints' => $newTotal
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['error' => 'server_error']);
}
