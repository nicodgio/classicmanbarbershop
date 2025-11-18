<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(['success' => false, 'message' => 'No autorizado']);
  exit;
}

require_once '../../conn/conexionBD.php';

$data = json_decode(file_get_contents('php://input'), true);
$clienteId = $data['cliente_id'] ?? null;
$esGratuito = $data['es_gratuito'] ?? false;

if (!$clienteId) {
  echo json_encode(['success' => false, 'message' => 'ID de cliente no proporcionado']);
  exit;
}

try {
  $pdo->beginTransaction();

  $stmtCliente = $pdo->prepare("SELECT visitas, visitas_gratis FROM clientes_lealtad WHERE id = :id");
  $stmtCliente->execute([':id' => $clienteId]);
  $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

  if (!$cliente) {
    throw new Exception('Cliente no encontrado');
  }

  if ($esGratuito) {
    $visitasActuales = (int)$cliente['visitas'];
    if ($visitasActuales < 10) {
      throw new Exception('El cliente no tiene suficientes visitas para un corte gratuito');
    }

    $sqlUpdate = "UPDATE clientes_lealtad 
                  SET visitas = 0, 
                      visitas_gratis = visitas_gratis + 1 
                  WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([':id' => $clienteId]);
  } else {
    $sqlUpdate = "UPDATE clientes_lealtad 
                  SET visitas = visitas + 1 
                  WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([':id' => $clienteId]);
  }

  $sqlVisita = "INSERT INTO visitas_lealtad (cliente_id, fecha_visita, es_gratuito) 
                VALUES (:cliente_id, NOW(), :es_gratuito)";
  $stmtVisita = $pdo->prepare($sqlVisita);
  $stmtVisita->execute([
    ':cliente_id' => $clienteId,
    ':es_gratuito' => $esGratuito ? 1 : 0
  ]);

  $pdo->commit();

  $nuevasVisitas = $esGratuito ? 0 : ((int)$cliente['visitas'] + 1);
  $corteGratisDisponible = !$esGratuito && ($nuevasVisitas % 10 == 0);

  echo json_encode([
    'success' => true,
    'message' => 'Visita registrada exitosamente',
    'corte_gratis_disponible' => $corteGratisDisponible
  ]);

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}