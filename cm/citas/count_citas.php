<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../../conn/conexionBD.php';

$fecha = $_GET['fecha'] ?? null;
if (!$fecha) {
    echo json_encode([]);
    exit;
}

// Ahora solo estado 1 o 2
$stmt = $pdo->prepare("
    SELECT 
        TIME_FORMAT(hora_inicio, '%H:%i') AS slot,
        COUNT(*) AS cnt
    FROM Citas
    WHERE fecha = :fecha
      AND estado IN (1,2)
    GROUP BY slot
");
$stmt->execute([':fecha' => $fecha]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($rows as $r) {
    $result[$r['slot']] = (int)$r['cnt'];
}

echo json_encode($result);
