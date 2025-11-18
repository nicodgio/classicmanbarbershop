<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'message' => 'No autorizado']);
  exit;
}

require_once '../../conn/conexionBD.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
  exit;
}

try {
  $pdo->beginTransaction();

  $sqlVisitas = "DELETE FROM visitas_lealtad WHERE cliente_id = :id";
  $stmtVisitas = $pdo->prepare($sqlVisitas);
  $stmtVisitas->execute([':id' => $id]);

  $sqlCliente = "DELETE FROM clientes_lealtad WHERE id = :id";
  $stmtCliente = $pdo->prepare($sqlCliente);
  $stmtCliente->execute([':id' => $id]);

  $pdo->commit();

  echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);

} catch (PDOException $e) {
  $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => 'Error al eliminar cliente']);
}