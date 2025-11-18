<?php
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../conn/conexionBD.php';
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
    
    try {
        $sql = "UPDATE Empleados SET nombre = :nombre, telefono = :telefono, fecha_inicio = :fecha_inicio WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'           => $id,
            ':nombre'       => $nombre,
            ':telefono'     => $telefono,
            ':fecha_inicio' => $fecha_inicio
        ]);
        
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
            echo json_encode(['success' => true]);
            exit;
        } else {
            header("Location: ../dashboard.php?section=usuarios");
            exit;
        }
    } catch (PDOException $e) {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        } else {
            die("Error al editar el usuario: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../dashboard.php?section=usuarios");
    exit;
}
?>