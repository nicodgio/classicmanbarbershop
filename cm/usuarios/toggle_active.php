<?php
session_start();
if (!isset($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

require_once '../../conn/conexionBD.php';

$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['id']) && isset($data['activo'])) {
  $id = (int)$data['id'];
  $activo = (int)$data['activo'];
  try {
    $sql = "UPDATE Empleados SET activo = :activo WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':activo' => $activo, ':id' => $id]);
    echo json_encode(['success' => true]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>