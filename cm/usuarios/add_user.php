<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../conn/conexionBD.php';
    
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : '';
    $activo = 1;
    
    try {
        $sql = "INSERT INTO Empleados (nombre, telefono, fecha_inicio, activo) VALUES (:nombre, :telefono, :fecha_inicio, :activo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'       => $nombre,
            ':telefono'     => $telefono,
            ':fecha_inicio' => $fecha_inicio,
            ':activo'       => $activo
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
            die("Error al añadir el usuario: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../dashboard.php?section=usuarios");
    exit;
}
?>