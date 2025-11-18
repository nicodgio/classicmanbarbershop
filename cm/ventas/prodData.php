<?php
require_once '../../conn/conexionBD.php';
header('Content-Type: application/json');
$sql = "SELECT id, nombre, categoria, precio_usd, precio_mxn FROM ProdSer";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias_array = [];
foreach ($productos as $prod) {
    $categorias_array[$prod['categoria']][] = $prod;
}
echo json_encode($categorias_array, JSON_HEX_TAG);
?>
