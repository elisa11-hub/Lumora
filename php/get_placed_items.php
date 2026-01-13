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

$islandId = isset($_GET['island_id']) ? (int)$_GET['island_id'] : 1;

$stmt = $pdo->prepare("
  SELECT ui.items_id_items, ui.pos_x, ui.pos_y, i.asset_path, i.name
  FROM user_items ui
  JOIN items i ON i.id_items = ui.items_id_items
  WHERE ui.user_id_user = :uid
    AND (:isid = 0 OR i.islands_id_islands = :isid)
  ORDER BY ui.acquired_at ASC
");
$stmt->execute(['uid' => $userId, 'isid' => $islandId]);


echo json_encode(['items' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);

