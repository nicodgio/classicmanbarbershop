<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'error' => 'No autorizado']);
  exit;
}

require_once '../../conn/conexionBD.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Devolver fila como JSON
  $id = intval($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT id, nombre, nombre_en, descripcion, descripcion_en, categoria, precio_usd, precio_mxn, activo FROM ProdSer WHERE id = ?");
  $stmt->execute([$id]);
  $prod = $stmt->fetch(PDO::FETCH_ASSOC);
  echo json_encode($prod ?: []);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Recoger datos
  $id             = intval($_POST['id']);
  $nombre         = $_POST['nombre']         ?? '';
  $nombre_en      = $_POST['nombre_en']      ?? '';
  $descripcion    = $_POST['descripcion']    ?? '';
  $descripcion_en = $_POST['descripcion_en'] ?? '';
  $categoria      = $_POST['categoria']      ?? '';
  $precio_usd     = floatval($_POST['precio_usd'] ?? 0);
  $precio_mxn     = floatval($_POST['precio_mxn'] ?? 0);
  $activo         = ($_POST['activo'] == '1') ? 1 : 0;

  try {
    $sql = "UPDATE ProdSer
            SET nombre         = ?,
                nombre_en      = ?,
                descripcion    = ?,
                descripcion_en = ?,
                categoria      = ?,
                precio_usd     = ?,
                precio_mxn     = ?,
                activo         = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $nombre,
      $nombre_en,
      $descripcion,
      $descripcion_en,
      $categoria,
      $precio_usd,
      $precio_mxn,
      $activo,
      $id
    ]);
    echo json_encode(['success' => true]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }
  exit;
}

echo json_encode(['success' => false, 'error' => 'Método no soportado']);
