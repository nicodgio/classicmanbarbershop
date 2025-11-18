<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'message' => 'No autorizado']);
  exit;
}

require_once '../../conn/conexionBD.php';

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

if (empty($nombre) || empty($telefono)) {
  echo json_encode(['success' => false, 'message' => 'Nombre y teléfono son obligatorios']);
  exit;
}

try {
  $sql = "INSERT INTO clientes_lealtad (nombre, telefono, visitas, visitas_gratis, fecha_registro) 
          VALUES (:nombre, :telefono, 0, 0, NOW())";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':nombre' => $nombre,
    ':telefono' => $telefono
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'Cliente registrado exitosamente',
    'id' => $pdo->lastInsertId()
  ]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al registrar cliente']);
}