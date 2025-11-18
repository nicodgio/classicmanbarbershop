<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
date_default_timezone_set('America/Mexico_City');

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = 'invitado_' . session_id();
}

require_once '../../conn/conexionBD.php';

$servicioId = $_POST['servicio_id']  ?? null;
$fecha      = $_POST['fecha']        ?? null;
$horaInicio = $_POST['hora_inicio']  ?? null;
$duracion   = isset($_POST['duracion']) && is_numeric($_POST['duracion'])
                ? intval($_POST['duracion'])
                : 60;
$nombre     = trim($_POST['nombre']  ?? '');
$notas      = trim($_POST['notas']   ?? '');
$telefono   = trim($_POST['telefono']?? '');
$estado     = $_POST['estado']       ?? 2;

if (!$servicioId || !$fecha || !$horaInicio || !$nombre || !$telefono) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

try {
    $dt = new DateTime("$fecha $horaInicio");
    $dt->modify("+{$duracion} minutes");
    $horaFin = $dt->format('H:i');
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al calcular fin: ' . $e->getMessage()
    ]);
    exit;
}

$sql = "
INSERT INTO Citas
    (servicio_id, fecha, hora_inicio, hora_fin, estado, notas, nombre, telefono, fecha_creacion)
VALUES
    (:servicio_id, :fecha, :hora_inicio, :hora_fin, :estado, :notas, :nombre, :telefono, NOW())
";
$stmt = $pdo->prepare($sql);
$params = [
    ':servicio_id' => $servicioId,
    ':fecha'       => $fecha,
    ':hora_inicio' => $horaInicio,
    ':hora_fin'    => $horaFin,
    ':estado'      => $estado,
    ':notas'       => $notas,
    ':nombre'      => $nombre,
    ':telefono'    => $telefono,
];
if (!$stmt->execute($params)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la cita.']);
    exit;
}

echo json_encode([
    'success' => true,
    'data'    => [
        'id'             => $pdo->lastInsertId(),
        'servicio_id'    => $servicioId,
        'fecha'          => $fecha,
        'hora_inicio'    => $horaInicio,
        'hora_fin'       => $horaFin,
        'estado'         => $estado,
        'notas'          => nl2br(htmlspecialchars($notas, ENT_QUOTES)),
        'nombre'         => htmlspecialchars($nombre, ENT_QUOTES),
        'telefono'       => htmlspecialchars($telefono, ENT_QUOTES),
        'fecha_creacion' => date('Y-m-d H:i:s'),
    ]
]);
exit;