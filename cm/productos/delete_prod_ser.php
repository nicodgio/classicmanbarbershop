<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}
require_once '../../conn/conexionBD.php';

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['success' => false, 'error' => 'ID inválido']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM ProdSer WHERE id = ?");
  $stmt->execute([$id]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
