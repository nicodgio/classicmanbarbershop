<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../conn/conexionBD.php';

// Obtener siguiente ticket
$stmt = $pdo->query("SELECT COALESCE(MAX(ticket), 0) + 1 AS next_ticket FROM Ventas");
$next = (int) $stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode(['ticket' => $next]);
