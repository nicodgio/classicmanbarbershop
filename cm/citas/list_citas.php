<?php
// citas/list_citas.php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../conn/conexionBD.php';

$fecha = $_GET['fecha'] ?? '';
$stmt  = $pdo->prepare("
  SELECT
    DATE_FORMAT(hora_inicio, '%H:%i') AS inicio,
    DATE_FORMAT(hora_fin,    '%H:%i') AS fin
  FROM Citas
  WHERE fecha = :fecha
    AND estado IN (1,2)
");
$stmt->execute([':fecha' => $fecha]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
