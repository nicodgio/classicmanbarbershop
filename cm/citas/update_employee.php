<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success'=>false,'message'=>'No autentificado']);
    exit;
}
require_once '../../conn/conexionBD.php';

$data = json_decode(file_get_contents('php://input'), true);
$id  = $data['id'] ?? null;
$emp = $data['empleado_asignado'] ?? null;

if (!$id) {
    echo json_encode(['success'=>false,'message'=>'ID inválido']);
    exit;
}

try {
    $sql = "UPDATE Citas SET empleado_asignado = :emp WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['emp'=>$emp, 'id'=>$id]);
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Error en la base de datos']);
}
