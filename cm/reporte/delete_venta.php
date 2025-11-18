<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['error'=>'No autorizado']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
  http_response_code(400);
  echo json_encode(['error'=>'Petición inválida']);
  exit;
}

$id = (int) $_POST['id'];

require_once '../../conn/conexionBD.php';

$stmt = $pdo->prepare("DELETE FROM Ventas WHERE id = :id");
if ($stmt->execute([':id'=>$id])) {
  echo json_encode(['success'=>true]);
} else {
  http_response_code(500);
  echo json_encode(['error'=>'Error al eliminar']);
}
