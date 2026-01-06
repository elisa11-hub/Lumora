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
$itemId  = isset($body['item_id']) ? (int)$body['item_id'] : 0;
$islandId= isset($body['island_id']) ? (int)$body['island_id'] : 1;
$posX    = isset($body['pos_x']) ? (float)$body['pos_x'] : null;
$posY    = isset($body['pos_y']) ? (float)$body['pos_y'] : null;

if ($itemId <= 0 || $posX === null || $posY === null) {
  http_response_code(400);
  echo json_encode(['error' => 'bad_request']);
  exit;
}

// pos_x/pos_y müssen 0..1 sein
if ($posX < 0 || $posX > 1 || $posY < 0 || $posY > 1) {
  http_response_code(400);
  echo json_encode(['error' => 'out_of_bounds']);
  exit;
}

try {
  $pdo->beginTransaction();

  // 1) Check: Item gehört zu dieser Insel
  $chk = $pdo->prepare("SELECT 1 FROM items WHERE id_items = :iid AND islands_id_islands = :isid");
  $chk->execute(['iid' => $itemId, 'isid' => $islandId]);
  if (!$chk->fetchColumn()) {
    $pdo->rollBack();
    http_response_code(403);
    echo json_encode(['error' => 'item_not_allowed_here']);
    exit;
  }

  // 2) Check: genug Lightpoints
  $sum = $pdo->prepare("SELECT COALESCE(SUM(lightpoints),0) FROM lightpoints WHERE user_id_user = :uid");
  $sum->execute(['uid' => $userId]);
  $total = (int)$sum->fetchColumn();

  if ($total < 1) {
    $pdo->rollBack();
    http_response_code(403);
    echo json_encode(['error' => 'not_enough_lightpoints', 'total_lightpoints' => $total]);
    exit;
  }
// Check: Item schon platziert?
$already = $pdo->prepare("
  SELECT 1 FROM user_items
  WHERE user_id_user = :uid AND items_id_items = :iid
  LIMIT 1
");
$already->execute(['uid'=>$userId, 'iid'=>$itemId]);

if ($already->fetchColumn()) {
  $pdo->rollBack();
  http_response_code(409);
  echo json_encode(['error' => 'item_already_placed']);
  exit;
}


  // 3) Insert in user_items
  $ins = $pdo->prepare("
    INSERT INTO user_items (user_id_user, items_id_items, acquired_at, pos_x, pos_y, rotation, scale)
    VALUES (:uid, :iid, NOW(), :x, :y, 0, 1)
  ");
  $ins->execute(['uid'=>$userId, 'iid'=>$itemId, 'x'=>$posX, 'y'=>$posY]);

  // 4) 1 Lightpoint ausgeben (als -1)
  $spend = $pdo->prepare("
    INSERT INTO lightpoints (user_id_user, islands_id_islands, quests_id_quests, lightpoints, created_at)
    VALUES (:uid, :isid, NULL, -1, NOW())
  ");
  $spend->execute(['uid' => $userId, 'isid' => $islandId]);

  // 5) neuen Stand zurückgeben
  $sum2 = $pdo->prepare("SELECT COALESCE(SUM(lightpoints),0) FROM lightpoints WHERE user_id_user = :uid");
  $sum2->execute(['uid' => $userId]);
  $newTotal = (int)$sum2->fetchColumn();

  $pdo->commit();

  echo json_encode(['ok'=>true, 'total_lightpoints'=>$newTotal]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['error'=>'server_error']);
}

