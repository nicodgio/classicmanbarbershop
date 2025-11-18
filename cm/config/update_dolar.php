<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

require_once '../../conn/conexionBD.php';  // Conexión PDO en $pdo

header('Content-Type: application/json; charset=utf-8');

$newDolar = $_POST['dolar'] ?? null;
$id       = $_POST['id']    ?? null;

if ($newDolar === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta el valor del dólar']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("UPDATE dolar_hoy SET dolar = :d WHERE id = :i");
        $stmt->execute([':d' => $newDolar, ':i' => $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO dolar_hoy (dolar, fecha_creacion) VALUES (:d, NOW())");
        $stmt->execute([':d' => $newDolar]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'id'      => $id,
        'dolar'   => $newDolar
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
