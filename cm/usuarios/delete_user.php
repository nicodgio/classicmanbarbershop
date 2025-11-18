<?php
session_start();
if (!isset($_SESSION['user'])) {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../conn/conexionBD.php';
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            $sql = "DELETE FROM Empleados WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
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
                die("Error al eliminar el usuario: " . $e->getMessage());
            }
        }
    } else {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        } else {
            header("Location: ../dashboard.php?section=usuarios");
            exit;
        }
    }
} else {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    } else {
        header("Location: ../dashboard.php?section=usuarios");
        exit;
    }
}
?>