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

$stmt = $pdo->prepare("
  SELECT ui.id_user_items, ui.items_id_items, ui.pos_x, ui.pos_y, ui.acquired_at,
         i.name, i.asset_path, i.required_light_points
  FROM user_items ui
  JOIN items i ON i.id_items = ui.items_id_items
  WHERE ui.user_id_user = :uid
  ORDER BY ui.acquired_at ASC
");
$stmt->execute(['uid' => $userId]);

echo json_encode(['items' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
