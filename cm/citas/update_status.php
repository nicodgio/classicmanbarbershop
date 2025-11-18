<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'No autentificado']);
    exit;
}

require_once '../../conn/conexionBD.php';

$id     = $_POST['id']     ?? null;
$status = $_POST['estado'] ?? null;

if (!in_array($status, ['0', '1'], true) || !$id) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    if ($status === '0') {
        // Si el estado es "CANCELADA" (0), eliminamos la cita
        $stmt = $pdo->prepare("DELETE FROM Citas WHERE id = :id");
        $ok = $stmt->execute(['id' => $id]);
    } else {
        // Si el estado es "CONFIRMADA" (1), actualizamos el estado
        $stmt = $pdo->prepare("UPDATE Citas SET estado = :st WHERE id = :id");
        $ok = $stmt->execute([
            'st' => $status,
            'id' => $id
        ]);
    }

    if ($ok) {
        echo json_encode([
            'success' => true,
            'data'    => ['id' => $id, 'estado' => (int) $status]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo ejecutar la operación']);
    }
} catch (PDOException $e) {
    // Registro genérico de error; no exponer detalles en producción
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos.']);
}
