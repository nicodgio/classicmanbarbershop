<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../conn/conexionBD.php';
$categoria    = trim($_POST['categoria']    ?? '');
$producto     = trim($_POST['producto']     ?? '');
$tipo_pago    = trim($_POST['tipo_pago']    ?? '');
$precio       = trim($_POST['precio']       ?? '');
$descuento    = intval($_POST['descuento']    ?? 0);
$precio_final = floatval($_POST['precio_final'] ?? 0.0);
$ticket       = intval($_POST['ticket']       ?? 0);
$propina      = floatval($_POST['propina']    ?? 0.0);
$tipo_propina = trim($_POST['tipo_propina'] ?? '');
$empleado     = trim($_POST['empleado']     ?? '');
$cambio       = floatval($_POST['cambio'] ?? 0.0);
if (empty($categoria) || empty($producto) || empty($tipo_pago) || $precio === '' || $ticket <= 0 || empty($empleado)) {
    echo json_encode(['success'=>false,'message'=>'Faltan campos obligatorios.']);
    exit;
}
$sql = "INSERT INTO Ventas
    (concepto, categoria, tipo_pago, precio, descuento, precio_final, ticket, fecha_venta, empleado, propina, tipo_propina, cambio)
  VALUES
    (:concepto, :categoria, :tipo_pago, :precio, :descuento, :precio_final, :ticket, NOW(), :empleado, :propina, :tipo_propina, :cambio)";
$stmt = $pdo->prepare($sql);
$params = [
    ':concepto'     => $producto,
    ':categoria'    => $categoria,
    ':tipo_pago'    => $tipo_pago,
    ':precio'       => $precio,
    ':descuento'    => $descuento,
    ':precio_final' => $precio_final,
    ':ticket'       => $ticket,
    ':propina'      => $propina,
    ':tipo_propina' => $tipo_propina,
    ':empleado'     => $empleado,
    ':cambio'       => $cambio
];
if ($stmt->execute($params)) {
    echo json_encode(['success'=>true,'message'=>'Venta agregada correctamente.']);
} else {
    echo json_encode(['success'=>false,'message'=>'Error al agregar la venta.']);
}
