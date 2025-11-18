<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

require_once '../../conn/conexionBD.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método no permitido']);
  exit;
}

$nombre     = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$categoria  = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
$precio_usd = isset($_POST['precio_usd']) ? trim($_POST['precio_usd']) : '';
$precio_mxn = isset($_POST['precio_mxn']) ? trim($_POST['precio_mxn']) : '';

if (empty($nombre) || empty($categoria) || empty($precio_usd) || empty($precio_mxn)) {
  echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos']);
  exit;
}

try {
  $sql = "INSERT INTO ProdSer (nombre, categoria, precio_usd, precio_mxn) VALUES (:nombre, :categoria, :precio_usd, :precio_mxn)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':nombre', $nombre);
  $stmt->bindParam(':categoria', $categoria);
  $stmt->bindParam(':precio_usd', $precio_usd);
  $stmt->bindParam(':precio_mxn', $precio_mxn);
  $stmt->execute();
  
  echo json_encode(['success' => true, 'message' => 'Producto/Servicio agregado con éxito']);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>