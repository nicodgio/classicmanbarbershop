<?php
session_start();
require_once '../../conn/conexionBD.php';
date_default_timezone_set('America/Mexico_City');

header('Content-Type: application/json');

$stmt = $pdo->prepare(
    "SELECT id 
     FROM dolar_hoy 
     WHERE DATE(fecha_creacion) = CURDATE() 
     LIMIT 1"
);
$stmt->execute();
$existe = (bool) $stmt->fetchColumn();

echo json_encode(['valid' => $existe]);
