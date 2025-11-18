<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}
require_once '../../conn/conexionBD.php';

$nombre         = $_POST['nombre']         ?? '';
$nombre_en      = $_POST['nombre_en']      ?? '';
$descripcion    = $_POST['descripcion']    ?? '';
$descripcion_en = $_POST['descripcion_en'] ?? '';
$categoria      = $_POST['categoria']      ?? '';
$precio_usd     = floatval($_POST['precio_usd'] ?? 0);
$precio_mxn     = floatval($_POST['precio_mxn'] ?? 0);
$activo         = ($_POST['activo'] == '1') ? 1 : 0;

try {
  $sql = "INSERT INTO ProdSer 
    (nombre,nombre_en,descripcion,descripcion_en,categoria,precio_usd,precio_mxn,activo)
    VALUES (?,?,?,?,?,?,?,?)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    $nombre,
    $nombre_en,
    $descripcion,
    $descripcion_en,
    $categoria,
    $precio_usd,
    $precio_mxn,
    $activo
  ]);
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
